<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Queue;
use App\Http\Requests\QueueRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TellercaterController extends Controller
{
    public function joinQueue(QueueRequest $request)

    {
        // Check if there are active (not finished) queue entries
        

        $lastQueue = DB::table('queue_numbers')
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
            'queue_number' => $nextQueueNumber
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

    public function getQueueList()
    {
        $waitingCustomer = DB::table('queues')->value('waiting_customer');
        $id = DB::table('queues')->where('queue_number', $waitingCustomer)->value('id');
        return response()->json([
            'id' => $id,
            'queue' => Queue::orderBy('queue_number')->get(),
            'current_serving' => Queue::where('status', 'serving')
                              ->first()?->queue_number ?? 'N/A',
            'waiting_customer' => $waitingCustomer,
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

    public function getTellerQueueList(Request $request)
    {
        $request->validate([
            'type_id' => 'required',
            'teller_id' => 'required'  // Ensure we receive teller_id
        ]);
    
        $type_id = $request->type_id;
        $teller_id = $request->teller_id;
        $lastUpdated = $request->input('last_updated'); // from frontend

        $queue = DB::table('queues as qs')
                ->select(
                    "qs.id",
                    "qs.token",
                    "qs.name",
                    "qs.email",
                    "qs.email_status",
                    "qs.type_id",
                    "qs.teller_id",
                    "qs.queue_number",
                    "qs.position",
                    "qs.status",
                    "qs.waiting_customer",
                    "qs.priority_service",
                    "qs.created_at",
                    "qs.updated_at",
                    "cr.currency_name",
                    "cr.flag",
                    "cr.currency_symbol",
                )
                ->leftJoin("currencies as cr", "cr.id", "qs.currency_selected")
                ->where('type_id', $type_id)
                ->where('teller_id', $teller_id) // Fetch queue specific to teller
                ->where('status', 'waiting')
                ->orderBy('position', 'asc')
                ->get();

                // Check latest updated_at timestamp in queue for this type and teller
                $latestUpdate = Queue::where('type_id', $type_id)
                ->where('teller_id', $teller_id)
                ->max('updated_at');
    
            if ($lastUpdated && $latestUpdate && $latestUpdate <= $lastUpdated) {
                // No updates since the last request
                return response()->json([
                    'updated' => false,
                ]);
            }
    
        $currentServing = Queue::where('type_id', $type_id)
                            ->where('teller_id', $teller_id)
                            ->where('status', 'serving')
                            ->first();
    
        $servingQueue = DB::table('queues')
                        ->select('name', 'queue_number', 'status')
                        ->where('type_id', $type_id)
                        ->where('teller_id', $teller_id)
                        ->where('status', 'serving')
                        ->first();
    
        $name = DB::table('tellers')
                ->select('teller_firstname', 'teller_lastname')
                ->where('id', $teller_id)
                ->first();
    
        $fullname = $name ? $name->teller_firstname . " " . $name->teller_lastname : null;
    
        return response()->json([
            'queue' => $queue,
            'current_serving' => $currentServing,
            'queue_numbers' => $queue->pluck('queue_number'),
            'name' => $servingQueue->name ?? null,
            'queue_number' => $servingQueue->queue_number ?? null,
            'status' => $servingQueue->status ?? null,
            'fullname' => $fullname,
            'updated' => true,
            'last_updated_at' => $latestUpdate,
        ]);
    }
    

    public function caterTellerCustomer(Request $request)
    {
        // First mark any currently serving customers as served
        // Queue::where('type_id', $request->service_id)
        //      ->where('teller_id', $request->teller_id)
        //      ->where('status', 'serving')
        //      ->update(['status' => 'served']);
    
        // Then update the new customer with both status and position in one query
        $updated = Queue::where('type_id', $request->service_id)
                    ->where('teller_id', $request->teller_id)
                    ->where('id', $request->id)
                    ->update([
                        'status' => 'serving',
                        'position' => 0
                    ]);
    
        if (!$updated) {
            return response()->json(['error' => 'Failed to update customer'], 500);
        }
    
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
            $query = DB::table('queues')
                ->whereNotIn('status', ['serving', 'waiting'])
                ->orderBy('created_at', 'desc'); // Sort by latest

            // Check if a date filter is provided
            if ($request->has('date')) {
                $date = $request->input('date'); 
                $query->whereDate('created_at', $date); // Filter by date
            }

            return response()->json([
                'rows' => $query->get()
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
            $query = DB::table('queues')
                ->whereNotIn('status', ['serving', 'waiting'])
                ->orderBy('created_at', 'asc'); // Sort by latest
    
            // Filter by "From" date
            if ($request->has('from_date') && !empty($request->input('from_date'))) {
                $query->whereDate('created_at', '>=', $request->input('from_date'));
            }
    
            // Filter by "To" date
            if ($request->has('to_date') && !empty($request->input('to_date'))) {
                $query->whereDate('created_at', '<=', $request->input('to_date'));
            }
    
            return response()->json([
                'rows' => $query->get()
            ]);
    
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return response()->json([
                "message" => env('APP_DEBUG') ? $message : "Something went wrong!"
            ]);
        }
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

}

