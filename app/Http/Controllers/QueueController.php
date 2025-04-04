<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Queue;
use App\Models\Teller;
use App\Http\Requests\QueueRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    public function joinQueue(QueueRequest $request)
    {
        $type_id = $request->type_id;

        // Fetch all tellers assigned to this type_id
        $tellers = DB::table('windows')->where('type_id', $type_id)->pluck('teller_id');

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
            ->where('teller_id', $assignedTellerId)
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
            'currency_selected' => $request->currency,
            'priority_service' => $request->priority_service
        ]);

        // Log to ensure proper assignment
        logger()->info("Queue Created: ", $queue->toArray());

        DB::table('queue_numbers')->insert([
            'status' => 'waiting',
            'queue_number' => $nextQueueNumber,
            'type_id' => $type_id,
            'teller_id' => $assignedTellerId,
            'customer_id' => $queue->id
        ]);

        $windowName = DB::table('windows')
            ->where('teller_id', $queue->teller_id)
            ->select('window_name')
            ->first();

        return response()->json([
            'message' => 'Successfully joined queue',
            'id' => $queue->id,
            'queue_number' => $queue->queue_number,
            'window_name' => $windowName->window_name
        ]);
    }
    
    public function joinSwitchQueue (Request $request) {
        $customerID = $request->userId;
        $assignedTellerId = $request->teller_id;
        $type_id_teller = $request->type_id_teller;

        try {
            // Get the next queue number
            $lastQueue = DB::table('queue_numbers')
                ->where('type_id', $type_id_teller)
                ->where('teller_id', $assignedTellerId)
                ->where('status', '!=', 'finished')
                ->orderBy('queue_number', 'desc')
                ->first();

            $nextQueueNumber = $lastQueue ? $lastQueue->queue_number + 1 : 1;

            DB::table('queues')
                ->where('id',$customerID)
                ->update([
                    'queue_number' => $nextQueueNumber,
                    'email_status' => 'sending_customer',
                    'teller_id' => $assignedTellerId
                ]);

            DB::table('queue_numbers')
                ->where('customer_id', $customerID)
                ->update([
                'status' => 'waiting',
                'queue_number' => $nextQueueNumber,
                'type_id' => $type_id_teller,
                'teller_id' => $assignedTellerId
            ]);
            
            $windowName = DB::table('windows')
                ->where('teller_id', $assignedTellerId)
                ->select('window_name')
                ->first();

        return response()->json([
            'message' => 'Successfully joined queue',
            'id' => $customerID ,
            'queue_number' => $nextQueueNumber,
            'window_name' => $windowName->window_name
        ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : 'Something went wrong'
            ]);
        }
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
            'message' => 'Customer is now being waited on.',
            'waiting_customer' => $request->queue_number
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
            ->orderBy('position', 'asc') // Sort in ascending order
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
    public function fetchData(Request $request)
    {
        try {
            // Get queue number from request
            $id = $request->input('id');
            $token = $request->token;

            // Find the queue by queue_number (exact match)
            $queue = Queue::where('id', $id)->first();

            $tokenFetch = DB::table('queues')
                ->where('token', $token)
                ->first();

            /*             if (!$queue) {
                            return response()->json(['message' => 'Queue not found'], 404);
                        } */

            // Return user data as JSON
            return response()->json([
                'Information' => $queue,
                'InformationFromToken' => $tokenFetch
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function updatePositions(Request $request)
    {
        foreach ($request->positions as $positionData) {
            Queue::where('id', $positionData['id'])->update(['position' => $positionData['position']]);
        }
        return response()->json(['message' => 'Queue positions updated successfully']);
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
            ->select('type_id', 'teller_id', 'queue_number', 'email', 'name', 'email_status', 'token', 'id', )
            ->first();

        // If the queue doesn't exist
        if (!$queue) {
            return response()->json(['row' => null], 404);
        }

        // Fetch type_ids_selected from Teller table as an array (since it's a JSON field)
        $selectedTypeIds = Teller::pluck('type_ids_selected')->toArray();

        // Flatten the array of arrays and decode the JSON data to arrays
        $typeIdsArray = [];
        foreach ($selectedTypeIds as $typeIds) {
            $typeIdsArray = array_merge($typeIdsArray, json_decode($typeIds));
        }

        // Find tellers with matching type_id in type_ids_selected
        $matchingTypeIds = array_values(array_filter($typeIdsArray, function ($typeId) use ($queue) {
            return $typeId == $queue->type_id;
        }));

        if (empty($matchingTypeIds)) {
            return response()->json([
                'message' => 'Teller not found',
                'userInfo' => $queue,
                'window' => null
            ]);
        }

        // Fetch the specific teller details (the one assigned to the queue)
        $newTeller = DB::table('tellers as t')
            ->select(
                "t.id",
                "t.teller_firstname",
                "t.teller_lastname",
                "tp.name",
                "tp.id as typeId",
                "tp.indicator",
                "tp.serving_time",
            )
            ->leftJoin("types as tp", "tp.id", "=", "t.type_id")
            ->where("t.type_id", $queue->type_id) // Corrected to match the queue's type_id
            ->where("t.id", $queue->teller_id) // Corrected teller ID
            ->first();

        $windowName = DB::table('windows')
            ->where('teller_id', $newTeller->id)
            ->select('window_name')
            ->first();

        // Now fetch all tellers who have the matching type_id (from type_ids_selected)
        $matchingTellers = DB::table('tellers as t')
            ->select(
                "t.id",
                "t.teller_firstname",
                "t.teller_lastname",
                "t.type_id",
                "t.Image",
                "tp.name",
                "tp.indicator",
                "tp.serving_time"
            )
            ->leftJoin("types as tp", "tp.id", "=", "t.type_id")
            ->whereIn("t.type_id", $matchingTypeIds) // Match multiple type_ids
            ->get();

        // Add full name and window name for each matching teller
        foreach ($matchingTellers as $teller) {
            $teller->full_name = $teller->teller_firstname . ' ' . $teller->teller_lastname;
            $teller->teller_image =  $teller->Image ? asset($teller->Image) : asset('assets/no-image-user.png');
            // Fetch the window name for each teller
            $windowName = DB::table('windows')
                ->where('teller_id', $teller->id)
                ->select('window_name')
                ->first();

            $teller->window_name = $windowName ? $windowName->window_name : null;
        }

        return response()->json([
            'row' => $newTeller,
            'matchingTellers' => $matchingTellers, // Return the matching tellers
            'userInfo' => $queue,
            'window' => $windowName,
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

    public function updateTellerId(Request $request)
    {
        $token = $request->token;
        $tellerId = $request->teller_id;

        DB::table('queues')
            ->where('token', $token)
            ->update(['teller_id' => $tellerId]);
    }

}

