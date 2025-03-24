<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Queue;
use App\Http\Requests\QueueRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    public function joinQueue(QueueRequest $request)
    {
        $type_id = $request->type_id;
    
        // Fetch all tellers assigned to this type_id
        $tellers = DB::table('tellers')->where('type_id', $type_id)->pluck('id');
    
        if ($tellers->isEmpty()) {
            return response()->json(['message' => 'No tellers assigned to this window type'], 400);
        }
    
        // Determine the last assigned teller for this type_id
        $lastAssigned = Queue::where('type_id', $type_id)->orderBy('created_at', 'desc')->first();
    
        // Get the next teller in a round-robin manner
        $nextTellerIndex = $lastAssigned ? ($tellers->search($lastAssigned->teller_id) + 1) % $tellers->count() : 0;
        $assignedTellerId = $tellers[$nextTellerIndex];
    
        // Double-check assignedTellerId
        if (!$assignedTellerId) {
            return response()->json(['message' => 'Failed to assign teller'], 500);
        }
    
        // Get the next queue number
        $lastQueue = DB::table('queue_numbers')
                        ->where('type_id', $type_id)
                        ->where('teller_id',  $assignedTellerId)
                        ->where('status', '!=', 'finished')
                        ->orderBy('queue_number', 'desc')
                        ->first();
    
        $nextQueueNumber = $lastQueue ? $lastQueue->queue_number + 1 : 1;
    
        // Create a new queue entry with explicit teller_id
        $queue = Queue::create([
            'token' => $request->token,
            'name' => $request->name,
            'email' => $request->email,
            'type_id' => $type_id,
            'teller_id' => $assignedTellerId, // Assigned teller - Explicitly setting it here
            'email_status' => $request->email_status,
            'queue_number' => $nextQueueNumber,
            'status' => 'waiting',
            'waiting_customer' => null,  
        ]);
    
        // Log to ensure proper assignment
        logger()->info("Queue Created: ", $queue->toArray());
    
        DB::table('queue_numbers')->insert([
            'status' => 'waiting',
            'queue_number' => $nextQueueNumber,
            'type_id' => $type_id,
            'teller_id' =>  $assignedTellerId
        ]);
    
        return response()->json([
            'message' => 'Successfully joined queue',
            'id' => $queue->id,
            'queue_number' => $queue->queue_number
        ]);
    }
    
    
    
    

    // public function startWait(Request $request)
    // {
    //     Cache::put('waiting_customer', $request->queue_number, now()->addMinutes(1)); // Store for 1 minute
    //     return response()->json(['waiting_customer' => $request->queue_number]);
    // }

    public function startWait(Request $request)
    {
        // Set waiting_customer to the queue number
        DB::table('queues')
            ->update(['waiting_customer' => $request->queue_number]);

        return response()->json([
            'message' => 'Customer is now being waited on.', 'waiting_customer' => $request->queue_number
        ]);
    }

    public function getQueueList(Request $request)
    {
        $token = $request->input('token');

        $typeId = DB::table('queues')
            ->where('token', $token)
            ->value('type_id');

        $tellerId = DB::table('queues')
            ->where('token', $token)
            ->value('teller_id');

        // Get all queue numbers for the specified type_id
        $queueList = Queue::where('type_id', $typeId)
            ->where('teller_id', $tellerId)
            ->orderBy('queue_number')
            ->get();

        // Get the currently serving queue number for the specified type_id
        $currentServing = Queue::where('status', 'serving')
            ->where('type_id', $typeId)
            ->where('teller_id', $tellerId)
            ->first()?->queue_number ?? 'N/A';

        return response()->json([
            'queue' => $queueList,
            'current_serving' => $currentServing,
        ]);
    }
    


    public function leaveQueue(Request $request)
    {
        Queue::where('id', $request->id)
            ->update(['status' => 'cancelled']);
        return response()->json([
            'message' => 'Queue left successfully'
        ]);
    }
        // this get the admin queue list
        public function getCQueueList()
    {
        $queue = Queue::where('status', 'waiting')
            ->orderBy('created_at')
            ->get();

        $currentServing = Queue::where('status', 'serving')->first();

        return response()->json([
            'queue' => $queue,
            'current_serving' => $currentServing,
            'queue_numbers' => $queue->pluck('queue_number') // Extracts all queue numbers
        ]);
    }
    public function caterCustomer(Request $request)
    {

        Queue::where('status', 'serving')
                ->update(['status' => 'served']);

        Queue::where('id', $request->id)
                ->update(['status' => 'serving']);

        return response()->json(['message' => 'Customer is now being served']);
    }

    public function cancelCustomer(Request $request)
    {
        Queue::where('id', $request->id)
                ->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Customer removed from queue'
        ]);
    }

    public function finishCustomer(Request $request)
    {
        $queue = Queue::find($request->id);
        if ($queue) {
            $queue->update([
                'status' => 'finished',
                'updated_at' => now()
            ]);
        }

        return response()->json([
            'message' => 'Customer marked as finished.'
        ]);
    }

    // fetch the specific data on queuenumber
    public function fetchData(Request $request) {
        try {
            // Get queue number from request
            $id = $request->input('id');
    
            // Find the queue by queue_number (exact match)
            $queue = Queue::where('id', $id)->first();
    
            if (!$queue) {
                return response()->json(['message' => 'Queue not found'], 404);
            }
    
            // Return user data as JSON
            return response()->json([
                'Information' => $queue
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function queueLogs(Request $request)
    { 
        try {
            $res = DB::table('queues as qs')
                ->select(
                    "qs.name",
                    "qs.email",
                    "qs.queue_number",
                    "tp.name as type_id",
                    DB::raw("CONCAT(ts.teller_firstname, ' ', ts.teller_lastname) as teller_id"),
                    "qs.status",
                    "qs.updated_at"
                )
                ->leftJoin("types as tp", "tp.id", "qs.type_id")
                ->leftJoin("tellers as ts", "ts.id", "qs.teller_id")
                ->whereNotIn('qs.status', ['serving', 'waiting']) // Add 'qs.' to the status column
                ->orderBy('qs.updated_at', 'desc');
    
            // Check if a date filter is provided
            if ($request->has('date') && $request->input('date')) {
                $date = $request->input('date'); 
                $res->whereDate('qs.updated_at', $date); // Use the alias 'qs'
            }
    
            return response()->json([
                'rows' => $res->get()
            ]);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return response()->json([
                "message" => env('APP_DEBUG') ? $message : "Something went wrong!"
            ]);
        }
    }

    
    

    public function fetchReports(Request $request)
    { 
        try {
            $res = DB::table('queues as qs')
            ->select(
                "qs.name",
                "qs.email",
                "qs.queue_number",
                "tp.name as type_id",
                DB::raw("CONCAT(ts.teller_firstname, ' ', ts.teller_lastname) as teller_id"),
                "qs.status",
                "qs.updated_at"
            )
            ->leftJoin("types as tp", "tp.id", "qs.type_id")
            ->leftJoin("tellers as ts", "ts.id", "qs.teller_id")
            ->whereNotIn('qs.status', ['serving', 'waiting'])
            ->orderBy('qs.updated_at', 'asc');
    
            // Filter by "From" date
            if ($request->has('from_date') && !empty($request->input('from_date'))) {
                $res->whereDate('qs.updated_at', '>=', $request->input('from_date'));
            }
    
            // Filter by "To" date
            if ($request->has('to_date') && !empty($request->input('to_date'))) {
                $res->whereDate('qs.updated_at', '<=', $request->input('to_date'));
            }
    
            return response()->json([
                'rows' => $res->get()
            ]);
    
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return response()->json([
                "message" => env('APP_DEBUG') ? $message : "Something went wrong!"
            ]);
        }
    }

    public function customerData(Request $request)
    {
        $token = $request->input('token');
    
        // Get type_id and teller_id from the queues table
        $queue = DB::table('queues')
            ->where('token', $token)
            ->select('type_id', 'teller_id')
            ->first();
    
        // If the queue doesn't exist
        if (!$queue) {
            return response()->json(['row' => null], 404);
        }
    
        // Fetch teller details
        $newTeller = DB::table('tellers as t')
            ->select(
                "t.id",
                "t.teller_firstname",
                "t.teller_lastname",
                "tp.name",
                "tp.id as typeId"
            )
            ->leftJoin("types as tp", "tp.id", "=", "t.type_id")
            ->where("t.type_id", $queue->type_id) // Corrected to match the queue's type_id
            ->where("t.id", $queue->teller_id) // Corrected teller ID
            ->first();
    
        return response()->json([
            'row' => $newTeller
        ]);
    }
    

    public function resetTodayQueueNumbers()
    {
        DB::table('queue_numbers')->update(['status' => 'finished']);

        // Reset queue numbering for the next customer (MySQL ONLY)
        DB::statement('ALTER TABLE queues AUTO_INCREMENT = 1');

        return response()->json([
            'message' => 'Queue has been reset. The next customer will start at queue number 1.'
        ]);
    }

    public function WaitCustomer(Request $request)
    {
        $queue = Queue::find($request->id);
        if ($queue) {
            $queue->update([
                'waiting_customer' => 'yes',
                'updated_at' => now()
            ]);
        }
      

        return response()->json([
            'message' => 'Customer is being waited.'
            
        ]);
    }

    public function checkWaitingCustomer(Request $request)
    {
        $queue = DB::table('queues')
            ->where('token', $request->input('token'))
            ->first();

       

        return response()->json([
            'waiting_customer' => $queue->waiting_customer,
           
        ]);
    }

    
    public function WaitingCustomerReset(Request $request)
    {
        $queue = Queue::find($request->id);
        if ($queue) {
            $queue->update([
                'waiting_customer' => 'no',
                'updated_at' => now()
            ]);
        }

        return response()->json([
            'message' => 'Customer is being waited.'
        ]);
    }

    public function updateTellerId(Request $request){
        $token = $request->token;
        $tellerId = $request->teller_id;

        DB::table('queues')
        ->where('token', $token)
        ->update(['teller_id' => $tellerId]);
    }

}

