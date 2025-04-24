<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
                       ->where('type_id', $type_id)        // Filters by 'type_id' for the current window type.
                       ->where('status', 'Online')       // Filters to only include tellers who are online.
                       ->where('branch_id', $branch_id) // Filters by the branch ID.
                       ->pluck('teller_id');              // Retrieves an array of teller IDs.
    
        // If no tellers are found, return a 400 error with an appropriate message.
        if ($tellers->isEmpty()) {
            return response()->json([
                'message' => 'No tellers assigned to this window type'  // Error message for the user.
            ], 400);  // HTTP status code 400 for a bad request.
        }
    
        // Initialize an empty array to track how many customers each teller has assigned.
        $tellerCount = [];
    
        // Loop through each teller to count how many active customers (not finished) are assigned to them.
        foreach ($tellers as $tellerID) {
            // Count customers for each teller (those whose status is not 'finished').
            $assignedCount = DB::table('queues')
                                ->where('type_id', $type_id)        // Filters by the 'type_id' of the service.
                                ->where('teller_id', $tellerID)     // Filters by each 'teller_id'.
                                ->where('branch_id', $branch_id) // Filters by the branch ID.
                                ->where('status', '!=', 'finished') // Excludes customers with a 'finished' status.
                                ->count();                         // Counts how many customers meet the criteria.
            $tellerCount[$tellerID] = $assignedCount;  // Stores the count of assigned customers for each teller.
        }
    
        // Filters out tellers who have less than 1 customer assigned to them.
        $availableTellers = array_filter($tellerCount, function($count) {
            return $count < 1;  // Returns tellers with less than 1 active customer.
        });
    
        // If all tellers are at the limit (i.e., have 1 or more assigned customers), choose a random teller.
        if (count($availableTellers) === 0) {
            $assignedTellerId = $tellers->random(); // Randomly selects a teller from the list.
        } else {
            // If there are tellers available with less than 1 customer, assign the one with the least load.
            $assignedTellerId = array_search(min($availableTellers), $availableTellers); // Finds the teller with the minimum assigned customers.
        }
    
        // Retrieve the last queue number for the specific 'type_id' and 'assignedTellerId', ordered by the most recent.
        $lastQueue = DB::table('queue_numbers')
            ->where('type_id', $type_id)                  // Filters by 'type_id' for the current service.
            ->where('teller_id', $assignedTellerId)       // Filters by the selected teller.
            ->where('branch_id', $branch_id) // Filters by the branch ID.
            ->where('status', '!=', 'finished')           // Excludes finished queues.
            ->orderBy('queue_number', 'desc')             // Orders by queue number in descending order (most recent).
            ->first();
                                            // Retrieves the first (latest) record.
                                            
        // If a previous queue exists, increment the queue number by 1, else start at 1.
        $nextQueueNumber = $lastQueue ? $lastQueue->queue_number + 1 : 1;
    
        // Create a new queue entry in the database with the given customer and teller information.
        $queue = Queue::create([
            'token' => $request->token,                      // The unique token for the customer.
            'name' => $request->name,                        // The customer's name.
            'email' => $request->email,                      // The customer's email.
            'type_id' => $type_id,                           // The 'type_id' for the service.
            'teller_id' => $assignedTellerId,                // The selected teller's ID.
            'email_status' => $request->email_status,        // Email status (if applicable).
            'queue_number' => $nextQueueNumber,               // The next available queue number.
            'status' => 'waiting',                            // Initial status for the new queue: 'waiting'.
            'waiting_customer' => null,                      // Placeholder for customer waiting status.
            'currency_selected' => $request->currency,       // The currency selected by the customer.
            'priority_service' => $request->priority_service, // Priority service flag.
            'branch_id' => $branch_id,                     // The branch ID of the teller.
        ]);
    

        // Insert a new entry in the 'queue_numbers' table to associate the queue with the teller and the customer.
        DB::table('queue_numbers')->insert([
            'status' => 'waiting',                            // Set the status as 'waiting' for the new queue number.
            'queue_number' => $nextQueueNumber,               // The next queue number for the customer.
            'type_id' => $type_id,                           // The 'type_id' for the service.
            'branch_id' => $branch_id,                     // The branch ID of the teller.
            'teller_id' => $assignedTellerId,                // The assigned teller's ID.
            'customer_id' => $queue->id                      // The customer ID associated with this queue.
        ]);
    
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
            $lastQueue = DB::table('queue_numbers')
                ->where('type_id', $type_id_teller)
                ->where('teller_id', $assignedTellerId)
                ->where('branch_id', $branch_id)
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
            ->select('type_id', 'teller_id', 'queue_number', 'email', 'name', 'email_status', 'token', 'id', 'branch_id' )
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
                if ($data->appointment_date == $dateNow) {
                    return response()->json([
                        'value' => $data
                    ]);
                } else {
                    return response()->json([
                        'errors' => "The reference number appointment for Online Application is no longer valid."
                    ],400);
                }
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
}

