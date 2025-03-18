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

