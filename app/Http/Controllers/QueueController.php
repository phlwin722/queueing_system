<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\CustomerDashBoardQueuelist;
use App\Events\TellerEvent;
use App\Models\Queue;
use App\Models\Teller;
use App\Http\Requests\QueueRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueueController extends Controller
{
    public function joinQueue(QueueRequest $request)
    {
        // Extracts the 'type_id' from the incoming request. This identifies the type of service (or window).
        $type_id = $request->type_id;
        $temp = $request->token;
        $temp1 = substr($temp, 12);
        $branch_id = $request->token ? (int)$temp1 : $request->branch_idd;
        
        // Log::info('Last character as int: ' . $branch_id);
        // return response()->json(['branch_id' => $branch_id]);
        // Fetch all tellers who are assigned to the specific window type and are currently "Online" (signed in).
        $tellers = DB::table('windows')
                       ->where('branch_id', $branch_id) // Filters by the branch ID.
                       ->where('type_id', $type_id)        // Filters by 'type_id' for the current window type.
                       ->where('status', 'Online')       // Filters to only include tellers who are online.
                       ->pluck('teller_id')              // Retrieves an array of teller IDs.
                       ->values(); // ensure it's indexed by 0, 1, 2...
    
        // If no tellers are found, return a 400 error with an appropriate message.
        if ($tellers->isEmpty()) {
            return response()->json([
                'message' => 'No tellers assigned to this window type'  // Error message for the user.
            ], 400);  // HTTP status code 400 for a bad request.
        }
    
        // Get last teller used for this branch/type combo
        $lastTellerData = DB::table('round_robin_trackers')
        ->where('branch_id', $branch_id)
        ->where('type_id', $type_id)
        ->first();

        // Initialize an empty array to track how many customers each teller has assigned.
        $nextTellerId = null;
    
        // Loop through each teller to count how many active customers (not finished) are assigned to them.
        if ($lastTellerData) {
            $lastIndex = $tellers->search($lastTellerData->last_teller_id);
            $nextIndex = ($lastIndex === false || $lastIndex === $tellers->count() - 1) ? 0 : $lastIndex + 1;
            $nextTellerId = $tellers[$nextIndex];
        
            // Update tracker
            DB::table('round_robin_trackers')
                ->where('branch_id', $branch_id)
                ->where('type_id', $type_id)
                ->update(['last_teller_id' => $nextTellerId]);
        } else {
            $nextTellerId = $tellers[0];
            // Create tracker entry
            DB::table('round_robin_trackers')->insert([
                'branch_id' => $branch_id,
                'type_id' => $type_id,
                'last_teller_id' => $nextTellerId
            ]);
        }
        
        $assignedTellerId = $nextTellerId;
    
        $counter = DB::table('queue_counters')
        ->where('branch_id', $branch_id)
        ->where('type_id', $type_id)
        ->where('teller_id', $assignedTellerId)
        ->where('status', '!=', 'finished')
        ->first();
    
    if (!$counter) {
        DB::table('queue_counters')->insert([
            'branch_id' => $branch_id,
            'type_id' => $type_id,
            'teller_id' => $assignedTellerId,
            'next_queue_number' => 2,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $nextQueueNumber = 1;
    } else {
        $nextQueueNumber = $counter->next_queue_number;
    
        DB::table('queue_counters')
            ->where('branch_id', $branch_id)
            ->where('type_id', $type_id)
            ->where('teller_id', $assignedTellerId)
            ->update([
                'next_queue_number' => $nextQueueNumber + 1,
                'updated_at' => now()
            ]);
    }

        // $lastQueue = DB::table('queue_numbers')
        // ->where('type_id', $type_id)
        // ->where('teller_id', $assignedTellerId)
        // ->where('branch_id', $branch_id)
        // ->where('status', '!=', 'finished')
        // ->orderBy('queue_number', 'desc')
        // ->first();

        // $nextQueueNumber = $lastQueue ? $lastQueue->queue_number + 1 : 1;
        $position = null;

        if ($request->priority_service !== null) {
            // Get customers ordered by position who have priority_service
            $lastPriorityCustomer = DB::table('queues')
                ->select('position')
                ->where('type_id', $type_id)
                ->where('teller_id', $assignedTellerId)
                ->where('branch_id', $branch_id)
                ->where('status', 'waiting')
                ->whereNotNull('priority_service') // Only customers with priority
                ->orderBy('position', 'desc') // Get the last one
                ->first();
        
            if ($lastPriorityCustomer) {
                // If there is a customer with priority_service
                $position = $lastPriorityCustomer->position + 1;
            } else {
                // If there are no customers with priority_service
                $position = 1;
            }
        
            // Now shift the position of others who are at or after this position
            DB::table('queues')
                ->where('type_id', $type_id)
                ->where('teller_id', $assignedTellerId)
                ->where('branch_id', $branch_id)
                ->where('status', 'waiting')
                ->where('position', '>=', $position)
                ->increment('position');
        } else {
            // Normal customers (without priority service)
            // Get max position and add 1 at the end
            $lastCustomer = DB::table('queues')
                ->select('position')
                ->where('type_id', $type_id)
                ->where('teller_id', $assignedTellerId)
                ->where('branch_id', $branch_id)
                ->where('status', 'waiting')
                ->orderBy('position', 'desc')
                ->first();
        
            $position = $lastCustomer ? ($lastCustomer->position + 1) : 1;
        }

        // on online appointment if joining will be update will be arrived
        if ($request->tokenn) {
            DB::table('appointments')
                ->where('referenceNumber', $request->tokenn)
                ->update(['status' => 'Arrived']);
        }
    
        // Create a new queue entry in the database with the given customer and teller information.
        $queue = Queue::create([
            'token' => $request->token ? $request->token :  $request->tokenn,                      // The unique token for the customer.
            'name' => $request->name,                        // The customer's name.
            'email' => $request->email,                      // The customer's email.
            'type_id' => $type_id,                           // The 'type_id' for the service.
            'teller_id' => $assignedTellerId,                // The selected teller's ID.
            'email_status' => $request->email_status,        // Email status (if applicable).
            'queue_number' => $nextQueueNumber,               // The next available queue number.
            'position' => $position,                          // The position in the queue (if applicable).
            'status' => 'waiting',                            // Initial status for the new queue: 'waiting'.
            'waiting_customer' => null,                      // Placeholder for customer waiting status.
            'currency_selected' => $request->currency,       // The currency selected by the customer.
            'priority_service' => $request->priority_service, // Priority service flag.
            'branch_id' => $branch_id,                     // The branch ID of the teller.
        ]);
    

        // Insert a new entry in the 'queue_numbers' table to associate the queue with the teller and the customer.
        // DB::table('queue_numbers')->insert([
        //     'status' => 'waiting',                            // Set the status as 'waiting' for the new queue number.
        //     'queue_number' => $nextQueueNumber,               // The next queue number for the customer.
        //     'type_id' => $type_id,                           // The 'type_id' for the service.
        //     'branch_id' => $branch_id,                     // The branch ID of the teller.
        //     'teller_id' => $assignedTellerId,                // The assigned teller's ID.
        //     'customer_id' => $queue->id                      // The customer ID associated with this queue.
        // ]);
    
        // Retrieve the window name associated with the assigned teller.
        $windowName = DB::table('windows')
            ->where('branch_id', $branch_id) // Filters by the branch ID.
            ->where('teller_id', $queue->teller_id)           // Filters by the 'teller_id' of the assigned teller.
            ->select('window_name')                           // Selects the 'window_name' column.
            ->first();                                        // Retrieves the first result (since 'teller_id' is unique).
    
        if ($request->referenceNumber) {
            DB::table('appointments')
                ->where('referenceNumber', $request->referenceNumber)
                ->update(['status' => 'Arrived']);
        }
        $newCustomer = DB::table('queues')->where('id', $queue)->first();
        broadcast(new CustomerDashBoardQueuelist($newCustomer));
        // Return a JSON response to the user with the details of the newly joined queue.
        return response()->json([
            'message' => 'Successfully joined queue',         // Success message.
            'id' => $queue->id,                               // The ID of the newly created queue.
            'queue_number' => $queue->queue_number,           // The queue number assigned to the customer.
            'window_name' => $windowName->window_name         // The name of the window where the customer was assigned.
        ]);
    }
    
    
    public function joinSwitchQueue (Request $request) {
        $customerID = $request->userId;
        $assignedTellerId = $request->teller_id;
        $type_id_teller = $request->type_id_teller;
        $branch_id = $request->branch_id;
        try {
            // Get the next queue number
            $counter = DB::table('queue_counters')
            ->where('branch_id', $branch_id)
            ->where('type_id', $type_id_teller)
            ->where('teller_id', $assignedTellerId)
            ->where('status', '!=', 'finished')
            ->first();
        
        if (!$counter) {
            DB::table('queue_counters')->insert([
                'branch_id' => $branch_id,
                'type_id' => $type_id_teller,
                'teller_id' => $assignedTellerId,
                'next_queue_number' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $nextQueueNumber = 1;
        } else {
            $nextQueueNumber = $counter->next_queue_number;
        
            DB::table('queue_counters')
                ->where('branch_id', $branch_id)
                ->where('type_id', $type_id_teller)
                ->where('teller_id', $assignedTellerId)
                ->update([
                    'next_queue_number' => $nextQueueNumber + 1,
                    'updated_at' => now()
                ]);
        }

        $position = null;

        if ($request->priority_service !== null) {
            // Get customers ordered by position who have priority_service
            $lastPriorityCustomer = DB::table('queues')
                ->select('position')
                ->where('type_id', $type_id_teller)
                ->where('teller_id', $assignedTellerId)
                ->where('branch_id', $branch_id)
                ->where('status', 'waiting')
                ->whereNotNull('priority_service') // Only customers with priority
                ->orderBy('position', 'desc') // Get the last one
                ->first();
        
            if ($lastPriorityCustomer) {
                // If there is a customer with priority_service
                $position = $lastPriorityCustomer->position + 1;
            } else {
                // If there are no customers with priority_service
                $position = 1;
            }
        
            // Now shift the position of others who are at or after this position
            DB::table('queues')
                ->where('type_id', $type_id_teller)
                ->where('teller_id', $assignedTellerId)
                ->where('branch_id', $branch_id)
                ->where('status', 'waiting')
                ->where('position', '>=', $position)
                ->increment('position');
        } else {
            // Normal customers (without priority service)
            // Get max position and add 1 at the end
            $lastCustomer = DB::table('queues')
                ->select('position')
                ->where('type_id', $type_id_teller)
                ->where('teller_id', $assignedTellerId)
                ->where('branch_id', $branch_id)
                ->where('status', 'waiting')
                ->orderBy('position', 'desc')
                ->first();
        
            $position = $lastCustomer ? ($lastCustomer->position + 1) : 1;
        }

            DB::table('queues')
                ->where('id',$customerID)
                ->update([
                    'queue_number' => $nextQueueNumber,
                    'email_status' => 'sending_customer',
                    'teller_id' => $assignedTellerId,
                    'position' => $position,
                ]);

            // DB::table('queue_numbers')
            //     ->where('customer_id', $customerID)
            //     ->update([
            //     'status' => 'waiting',
            //     'queue_number' => $nextQueueNumber,
            //     'type_id' => $type_id_teller,
            //     'teller_id' => $assignedTellerId
            // ]);
            
            $windowName = DB::table('windows')
                ->where('teller_id', $assignedTellerId)
                ->select('window_name')
                ->first();
            $newCustomer = DB::table('queues')->where('id', $customerID)->first();
            broadcast(new CustomerDashBoardQueuelist($newCustomer));
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

    // public function updateBranchId(Request $request)
    // {

    
    //     $updated = Queue::where('id', $request->id)
    //         ->update(['branch_id' => $request->branch_id]);
    
    //     return response()->json([
    //         'success' => $updated ? true : false,
    //         'message' => $updated ? 'Branch ID updated successfully.' : 'No matching id found.',
    //     ]);
    // }
    
    public function getQueueList(Request $request)
    {
        $token = $request->input('token');
        $lastUpdated = $request->input('last_updated'); // from frontend

        $typeId = DB::table('queues')
            ->where('token', $token)
            ->value('type_id');

        $branchId = DB::table('queues')
            ->where('token', $token)
            ->value('branch_id');

        $tellerId = DB::table('queues')
            ->where('token', $token)
            ->value('teller_id');

        // Check latest updated_at timestamp in queue for this type and teller
        $latestUpdate = Queue::where('type_id', $typeId)
            ->where('teller_id', $tellerId)
            ->max('updated_at');

        if ($lastUpdated && $latestUpdate && $latestUpdate <= $lastUpdated) {
            // No updates since the last request
            return response()->json([
                'updated' => false,
            ]);
        }

        // Otherwise, return the updated list
        $queueList = Queue::where('type_id', $typeId)
            ->where('teller_id', $tellerId)
            ->where('branch_id', $branchId)
            ->orderBy('position', 'asc')
            ->get();

        $currentServing = Queue::where('status', 'serving')
            ->where('type_id', $typeId)
            ->where('teller_id', $tellerId)
            ->where('branch_id', $branchId)
            ->first()?->queue_number ?? 'N/A';

        return response()->json([
            'updated' => true,
            'last_updated_at' => $latestUpdate,
            'queue' => $queueList,
            'current_serving' => $currentServing,
        ]);
    }




    public function leaveQueue(Request $request)
    {
        Queue::where('id', $request->id)
            ->update(['status' => 'cancelled']);

        $updatedCustomer = DB::table('queues')->where('id', $request->id)->first();
        broadcast(new CustomerDashBoardQueuelist($updatedCustomer));
        broadcast(new TellerEvent($updatedCustomer));
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
            $res = '';
            if ($request->branch_id != 0) {
                $res = DB::table('queues as qs')
                ->select(
                    "qs.name",
                    "qs.email",
                    "qs.queue_number",
                    'qs.branch_id',
                    "tp.name as type_id",
                    DB::raw("CONCAT(ts.teller_firstname, ' ', ts.teller_lastname) as teller_id"),
                    "qs.status",
                    "qs.updated_at"
                )
                ->leftJoin("types as tp", "tp.id", "qs.type_id")
                ->leftJoin("tellers as ts", "ts.id", "qs.teller_id")
                ->whereNotIn('qs.status', ['serving', 'waiting']) // Add 'qs.' to the status column
                ->orderBy('qs.updated_at', 'desc')
                ->where('qs.branch_id', $request->branch_id)
                ->whereDate('qs.updated_at', $request->date)
                ->get(); // Use the alias 'qs'
            }else {
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
                ->orderBy('qs.updated_at', 'desc')
                ->whereDate('qs.updated_at', $request->date)
                ->get(); // Use the alias 'qs'
            
            }
            
            
            return response()->json([
                'rows' => $res
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
            $res = '';
            if ($request->branch_id != 0) {
                $res = DB::table('queues as qs')
                ->select(
                    "qs.name",
                    "qs.email",
                    "qs.queue_number",
                    "qs.branch_id",
                    "b.branch_name",
                    "tp.name as type_id",
                    DB::raw("CONCAT(ts.teller_firstname, ' ', ts.teller_lastname) as teller_id"),
                    "qs.status",
                    "qs.updated_at"
                )
                ->leftJoin("types as tp", "tp.id", "qs.type_id")
                ->leftJoin("tellers as ts", "ts.id", "qs.teller_id")
                ->leftJoin('branchs as b' ,'b.id', '=','qs.branch_id')
                ->where('qs.branch_id',$request->branch_id)
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
            }else {
                $res = DB::table('queues as qs')
                ->select(
                    "qs.name",
                    "qs.email",
                    "qs.queue_number",
                    "qs.branch_id",
                    "b.branch_name",
                    "tp.name as type_id",
                    DB::raw("CONCAT(ts.teller_firstname, ' ', ts.teller_lastname) as teller_id"),
                    "qs.status",
                    "qs.updated_at"
                )
                ->leftJoin("types as tp", "tp.id", "qs.type_id")
                ->leftJoin("tellers as ts", "ts.id", "qs.teller_id")
                ->leftJoin('branchs as b' ,'b.id', '=','qs.branch_id')
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
        $branch_id = $request->input('branch_id');
        // Get type_id and teller_id from the queues table
        $queue = DB::table('queues')
            ->where('token', $token)
            ->select('type_id', 'teller_id', 'queue_number', 'email', 'name', 'email_status', 'token', 'id', 'branch_id', 'status', 'priority_service' )
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
                "t.branch_id",
                "t.Image",
                "tp.name",
                "tp.indicator",
                "tp.serving_time",
                "w.status"
            )
            ->leftJoin('windows as w', 'w.teller_id', "=", "t.id")
            ->whereIn('w.status', ['Online'])
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


    public function resetTodayQueueNumbers(Request $request)
    {
        $branchId = $request->branch_id;

        // Mark all queue_numbers as finished for the branch
        DB::table('queue_counters')
            ->where('branch_id', $branchId)
            ->update(['status' => 'finished']);
    
        // Reset the counter for that branch
        DB::table('queue_counters')
        ->where('branch_id', $branchId)
        ->update(['next_queue_number' => 1, 'updated_at' => now()]);
    
        return response()->json([
            'message' => 'Queue has been reset for this branch. The next customer will start at queue number 1.'
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

        $updatedCustomer = DB::table('queues')->where('id', $request->id)->first();
        broadcast(new CustomerDashBoardQueuelist($updatedCustomer));
        return response()->json([
            'message' => 'Customer is being waited.'
        ]);
    }

    public function checkWaitingCustomer(Request $request)
    {
        $token = $request->input('token');
        $lastUpdated = $request->input('last_updated');
        $branch_id = $request->input('branch_id');
        $queue = DB::table('queues')
            ->where('token', $token)
            ->where('branch_id', $branch_id)
            ->first();
    
        // Get updated_at value of the matched queue
        $latestUpdate = optional($queue)->updated_at;
    
        // If not updated since the last fetch, skip
        if ($lastUpdated && $latestUpdate && $latestUpdate <= $lastUpdated) {
            return response()->json([
                'updated' => false,
            ]);
        }
    
        return response()->json([
            'updated' => true,
            'last_updated_at' => $latestUpdate,
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

    public function checkingReferenceNumber (Request $request) {

        $validate = $request->validate([
            'referenceNumber' => 'required'
        ],[
            'referenceNumber.required' => 'The reference number field is required'
        ]);

        if (!$validate) {
            return response()->json([
                'errors' => $validate
            ],422);
        }

        $data = DB::table('appointments as ap')
            ->select(
                'ap.id',
                'ap.referenceNumber',
                'ap.branch_id',
                'ap.name as fullname',
                'ap.email',
                'ap.type_id',
                'ap.appointment_date',
                'ap.status',
                'b.branch_name',
                'tp.name'
            )
            ->where('referenceNumber', $request->referenceNumber)
            ->leftJoin('types as tp', 'tp.id', '=', 'ap.type_id')
            ->leftJoin('branchs as b', 'b.id', '=', 'ap.branch_id')
            ->first();

        if ($data) {
            if ($data->status == 'Booked') {
                $dateNow = Carbon::now()->toDateString();
                if ($data->appointment_date == $dateNow  && $data->branch_id == $request->branch_id) {
                    return response()->json([
                        'value' => $data
                    ]);
                } else if ($data->appointment_date > $dateNow && $data->branch_id == $request->branch_id) {
                    return response()->json([
                        'errors' => "The reference number appointment for Online Application is not yet valid your appointment date is {$data->appointment_date}."
                    ],400);
                } else {
                    return response()->json([
                        'errors' => "The reference number appointment for the Online Application is not valid for this branch. Your appointment branch {$data->branch_name}"
                    ],400);
                }
            } else if ($data->status == 'Expired') {
                return response()->json([
                    'errors' => "The reference number appointment for Online Application is no longer valid."
                ],400);
            } else {
                return response()->json([
                    'errors' => 'The customer has already been finished.'
                ],400);
            }
        } else {
            return response()->json([
                'errors' => 'The reference number could not be found.'
            ],400);
        }
    }

    public function checkingCustomer (Request $request) {
        try {
            $result = DB::table('queues')
                ->select('email_status')
                ->where('token', $request->token)
                ->first();

                return response()->json([
                    'email_status' => $result->email_status
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => env('APP_DEBUG') ? $e->getMessage() : 'Something went wrong'
            ]);
        }
    }
}