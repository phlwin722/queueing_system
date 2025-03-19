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

        // Check if there are active (not finished) queue entries
        $lastQueue = DB::table('queue_numbers')
                        ->where('type_id', $type_id)
                        ->where('status', '!=', 'finished')
                        ->orderBy('queue_number', 'desc')
                        ->first();

        // If there's no active queue, start from 1
        $nextQueueNumber = $lastQueue ? $lastQueue->queue_number + 1 : 1;
        
        // Create queue entry
        $queue = Queue::create([
            'token' => $request->token,
            'name' => $request->name,
            'email' => $request->email,
            'type_id' => $request->type_id,
            'email_status' => $request->email_status,
            'queue_number' => $nextQueueNumber,
            'status' => 'waiting',
            'waiting_customer' => null
        ]);
      
        DB::table('queue_numbers')->insert([
            'status' => 'waiting',
            'queue_number' => $nextQueueNumber,
            'type_id' => $type_id
        ]);
        
        // Return response with queue ID and queue number
        return response()->json([
            'message' => 'Successfully joined queue',
            'id' => $queue->id, // ✅ Include the queue ID
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

        // Get all queue numbers for the specified type_id
        $queueList = Queue::where('type_id', $typeId)
            ->orderBy('queue_number')
            ->get();

        // Get the currently serving queue number for the specified type_id
        $currentServing = Queue::where('status', 'serving')
            ->where('type_id', $typeId)
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
                "qs.created_at"
            )
            ->leftJoin("types as tp", "tp.id", "qs.type_id")
            ->leftJoin("tellers as ts", "ts.id", "qs.teller_id")
            ->whereNotIn('status', ['serving', 'waiting'])
            ->orderBy('qs.created_at', 'desc');
    
             // Check if a date filter is provided
             if ($request->has('date')) {
                $date = $request->input('date'); 
                $res->whereDate('created_at', $date); // Filter by date
            }

      
    

             // $query = DB::table('queues')
            //     ->whereNotIn('status', ['serving', 'waiting'])
            //     ->orderBy('created_at', 'desc'); // Sort by latest

       

            return response()->json([
                'rows' => $res->get()
                
            ]);

            // return response()->json([
            //     'rows' => $query->get()
            // ]);

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
                "qs.created_at"
            )
            ->leftJoin("types as tp", "tp.id", "qs.type_id")
            ->leftJoin("tellers as ts", "ts.id", "qs.teller_id")
            ->whereNotIn('status', ['serving', 'waiting'])
            ->orderBy('qs.created_at', 'asc');
    
            // Filter by "From" date
            if ($request->has('from_date') && !empty($request->input('from_date'))) {
                $res->whereDate('created_at', '>=', $request->input('from_date'));
            }
    
            // Filter by "To" date
            if ($request->has('to_date') && !empty($request->input('to_date'))) {
                $res->whereDate('created_at', '<=', $request->input('to_date'));
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

        // Get type_id from the queues table
        $typeId = DB::table('queues')
            ->where('token', $token)
            ->value('type_id');

        // If type_id is null, return an empty response
        if (!$typeId) {
            return response()->json(['row' => null], 404);
        }

        // Get teller details based on type_id
        $newTeller = DB::table('tellers as t')
            ->select(
                "t.id",
                "t.teller_firstname",
                "t.teller_lastname",
                "tp.name",
                "tp.id as typeId"
            )
            ->leftJoin("types as tp", "tp.id", "=", "t.type_id")
            ->where("t.type_id", $typeId) // Correct column
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

