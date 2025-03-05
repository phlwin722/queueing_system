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
        // Get the next queue number
        $lastQueue = Queue::orderBy('queue_number', 'desc')->first();
        $nextQueueNumber = $lastQueue ? $lastQueue->queue_number + 1 : 1;

        // Create queue entry
        $queue = Queue::create([
            'name' => $request->name,
            'mobile' => $request->mobile,
            'queue_number' => $nextQueueNumber,
            'status' => 'waiting',
            'waiting_customer' => null
        ]);

        return response()->json([
            'message' => 'Successfully joined queue',
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
        return response()->json([
            'queue' => Queue::orderBy('queue_number')->get(),
            'current_serving' => Queue::where('status', 'serving')
                              ->first()?->queue_number ?? 'N/A',
            'waiting_customer' => $waitingCustomer, // Send waiting customer
        ]);
    }

    public function leaveQueue(Request $request)
    {
        Queue::where('queue_number', $request->queue_number)
            ->update(['status' => 'cancelled']);
        return response()->json([
            'message' => 'Queue left successfully'
        ]);
    }

    public function getCQueueList()
    {
        return response()->json([
            'queue' => Queue::where('status', 'waiting')
                    ->orderBy('created_at')->get(),
            'current_serving' => Queue::where('status', 'serving')->first()
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
}
