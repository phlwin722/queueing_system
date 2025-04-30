<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teller;
use App\Http\Requests\TellerRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Requests\AdminRequest; 
use Illuminate\Support\Carbon as SupportCarbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;


class TellerController extends Controller
{

    public function index(Request $request)
    {

        try {
            if ($request->branch_id != null) {
                $res = DB::table('tellers as t')
                ->select(
                    "t.id",
                    "t.teller_firstname",
                    "t.teller_lastname",
                    "t.teller_username",
                    "t.teller_password",
                    "t.type_id",
                    "t.type_ids_selected",
                    "t.Image",
                    "t.branch_id",
                    "b.branch_name",
                    DB::raw("IFNULL(ts.status, 'Offline') as status"), // Set 'Offline' if null
                    DB::raw('GROUP_CONCAT(tp.name SEPARATOR ", ") as type_names')
                )
                ->leftJoin("types as tp", DB::raw('JSON_CONTAINS(t.type_ids_selected, JSON_QUOTE(CAST(tp.id AS CHAR)))'), '>', DB::raw('0'))
                ->leftJoin('branchs as b',"b.id","=","t.branch_id")
                ->leftJoin('windows as ts', 't.id', '=', 'ts.teller_id')
                ->where('t.branch_id', $request->branch_id )
                ->groupBy(
                    "t.id",
                    "t.teller_firstname",
                    "t.teller_lastname",
                    "t.teller_username",
                    "t.teller_password",
                    "t.type_ids_selected",
                    "t.Image",
                    "ts.status"
                )
                ->orderBy('t.created_at', 'desc')
                ->get();
        
                return response()->json([
                    'rows' => $res
                ]);
    
            }else {
                $rows = $this->getData();

                return response()->json([
                    'rows' => $rows
                ]);
    
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return response()->json([
                "message" => env('APP_DEBUG')
                    ? $message
                    : "Something went wrong!"
            ]);
        }
    }

    // public function create(TellerRequest $request) 
    // {
    //     try {
    //         $validatedData = $request->validated(); 
    //         $row = Teller::create($validatedData);

    //         $typeName = DB::table('types')
    //             ->where('id', $row->types_id)
    //             ->value('name');

    //         return response()->json([
    //             "Teller" => $this->getData($row->id),
    //             "message" => "Successfully Created!"
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             "message" => env('APP_DEBUG') ? $e->getMessage() : "Something went wrong!"
    //         ]);
    //     }
    // }

    public function create(TellerRequest $request)
    {
        try {
            // Insert a new teller and get the ID
            $tellerID = DB::table('tellers')->insertGetId([
                'teller_firstname' => ucwords($request->teller_firstname),
                'teller_lastname' => ucwords($request->teller_lastname),
                'teller_username' => $request->teller_username,
                'teller_password' => Hash::make($request->teller_password),
                'type_ids_selected' => json_encode($request->type_ids_selected), // Store selected types as JSON
                'branch_id' => $request->branch_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // insert image on database
            $teller = DB::table('tellers')
                ->where('id', $tellerID)
                ->first();
            // delete the old message if it exist
            if ($teller->Image) {
                $oldImagePath = public_path($teller->Image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath); // Delete the file
                }
            }

            // Process and save the uploaded file
            if ($request->hasFile('Image')) {
                $file = $request->file('Image');
                $filename = time() . '.' . $file->getClientOriginalExtension();

                // define the folder path (inside public directory)
                $folderPath = public_path('assets/teller/' . $tellerID);

                // Ensure the folder exists
                if (!file_exists($folderPath)) {
                    mkdir($folderPath, 0755, true);  // Create folder with proper permissions
                }

                // move file to the folder
                $file->move($folderPath, $filename);

                // correct url public access
                $filePath = "assets/teller/{$tellerID}/{$filename}";

                /// **FIXED:** Update database using Query Builder (no `save()` method)
                DB::table('tellers')
                    ->where('id', $tellerID)
                    ->update(['Image' => $filePath]);
            }

            // Fetch the newly inserted teller and join with the types table
            $newTeller = DB::table('tellers as t')
                ->select(
                    "t.id",
                    "t.teller_firstname",
                    "t.teller_lastname",
                    "t.teller_username",
                    "t.teller_password",
                    "t.type_ids_selected",
                    "t.Image",
                    't.branch_id',
                    'b.branch_name',
                    DB::raw('GROUP_CONCAT(tp.name SEPARATOR ", ") as type_names')  // Concatenate type names
                )
                ->leftJoin("types as tp", DB::raw('JSON_CONTAINS(t.type_ids_selected, JSON_QUOTE(CAST(tp.id AS CHAR)))'), '>', DB::raw('0'))
                ->leftJoin("branchs as b","b.id","=","t.branch_id")
                ->where("t.id", $tellerID)
                ->groupBy("t.id")
                ->first();


            return response()->json([
                'success' => true,
                'message' => 'Successfully Added!',
                'row' => $newTeller
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    // fetching image each of teller admin teller 
    public function fetchImage(Request $request)
    {
        $image = $request->image;

        $tellerImage = DB::table('tellers')
            ->where('Image', $image)
            ->first();

        return response()->json([
            'Image' => $tellerImage->Image ? asset($tellerImage->Image) : asset('assets/no-image-user.png')
        ]);
    }


    // fetching image each of teller layout
    public function fetchImageTeller(Request $request)
    {
        try {
            $id = $request->id;

            $tellerImage = DB::table('tellers')
                ->where('id', $id)
                ->first();

            return response()->json([
                'Image' => $tellerImage->Image ? asset($tellerImage->Image) : asset('assets/no-image-user.png')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    // customer dashboard folder
    public function fetchImageTellerCsDashboaard(Request $request)
    {
        try {
            $id = $request->id;

            $tellerImage = DB::table('tellers')
                ->where('id', $id)
                ->first();

            return response()->json([
                'Image' => $tellerImage->Image ? asset($tellerImage->Image) : asset('assets/no-image-user.png')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }


    public function form(Request $request)
    {
        try {
            $rows = DB::table("types")
                ->select('id as value', 'name as label')
                ->get();

            return response()->json([
                "types" => $rows,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : "Something went wrong!"
            ]);
        }
    }


    public function update(TellerRequest $request)
    {
        try {
            $teller = Teller::find($request->id);

            // Check if teller exists
            if (!$teller) {
                return response()->json([
                    "message" => "Teller not found!"
                ], 404);
            }

            // Update fields manually
            $teller->teller_firstname = ucwords($request->teller_firstname);
            $teller->teller_lastname = ucwords($request->teller_lastname);
            $teller->teller_username = $request->teller_username;
            $teller->type_ids_selected = $request->type_ids_selected;
            $teller->branch_id = $request->branch_id;

            // Update password only if provided
            if ($request->teller_password != "oldpass") {
                $teller->teller_password = Hash::make($request->teller_password);
            }

            // Update the image if provided
            if ($request->hasFile('Image')) {
                // Delete the old image if it exists
                if ($teller->Image && file_exists(public_path($teller->Image))) {
                    unlink(public_path($teller->Image)); // Delete the old image file
                }

                // Process and save the new uploaded image
                $file = $request->file('Image');
                $filename = time() . '.' . $file->getClientOriginalExtension();

                // Define the folder path (inside the public directory)
                $folderPath = public_path('assets/teller/' . $request->id);

                // Ensure the folder exists
                if (!file_exists($folderPath)) {
                    mkdir($folderPath, 0755, true);  // Create folder with proper permissions
                }

                // Move the file to the folder
                $file->move($folderPath, $filename);

                // Save the file path in the database
                $filePath = "assets/teller/{$request->id}/{$filename}";

                // Update the image path using Eloquent
                $teller->Image = $filePath;
            }

            // Save the updates (will save the image path as well)
            $teller->save();

            // Fetch updated data
            $row = $this->getData($teller->id);

            return response()->json([
                "row" => $row,
                "message" => "Successfully Updated!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : "An error occurred"
            ]);
        }
    }

    public function dropdownTypes (Request $request) {
        try {
            $rows = DB::table('types')
                    ->where('branch_id',$request->branch_id)
                    ->get();
            return response()->json([
                'rows' =>  $rows
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : "An error occurred"
            ]);
        }
    }


    public function delete(Request $request)
    {
        try {
            /* // Check if any windows are using this teller
            $windowCount = \App\Models\Window::whereIn('teller_id', $request->ids)->count();

            if ($windowCount > 0) {
                return response()->json([
                    "message" => "Cannot delete teller. It is still referenced in windows."
                ], 400);
            }
            */
            $data = DB::table('tellers')
                    ->whereIn('id', $request->ids)
                    ->get();

            // loop each teller check if ther are online
            foreach($data as $val) {
                // check if the manager is online
                if ($val->status == 'Online') {
                    return response()->json([
                        'error' => "Sorry, we cannot delete this manager because they are still online."
                    ],400);
                }

                // update windows
                DB::table('windows')
                    ->where('teller_id', $val->id)
                    ->update([
                        'teller_id' => null
                    ]);

                // get date now 
                $dateNow = Carbon::now()->toDateString();
                // Archive the manager's data
                DB::table('archives')
                    ->insert([
                        'First_name' => $val->teller_firstname,
                        'Lastname' => $val->teller_lastname,
                        'Username' => $val->teller_username,
                        'Password' => $val->teller_password,
                        'Branch_id' => $val->branch_id,
                        'Image' => $val->Image,
                        'types' => '1',
                        'created_at' => $dateNow
                    ]);
            }
            
            // Proceed with deletion
            Teller::destroy($request->ids);

            return response()->json([
                "message" => "Successfully Deleted!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : "Something went wrong!"
            ]);
        }
    }
    public function queueLogs(Request $request)
    {
        try {
            // Get the 'teller_id' from the incoming request
            $teller_id = $request->teller_id;
            $fromDate = $request->fromDate;
            $toDate = $request->toDate;

            // Begin querying the 'queues' table with alias 'qs'
            $res = DB::table('queues as qs')
                // Add a condition to filter the results by the 'teller_id' 
                // ->where('teller_id', $teller_id)
                // Select specific columns from the 'queues' table and related tables
                ->select(
                    "qs.name", // Customer name from the 'queues' table
                    "qs.email", // Customer email from the 'queues' table
                    "qs.queue_number", // Queue number from the 'queues' table
                    "tp.name as type_id", // Name of the type from the 'types' table (alias 'tp')
                    DB::raw("CONCAT(ts.teller_firstname, ' ', ts.teller_lastname) as teller_id"), // Concatenate first and last name of the teller from 'tellers' table
                    "qs.status", // The status of the queue from the 'queues' table
                    "qs.updated_at" // The timestamp when the queue was last updated
                )
                // Perform a LEFT JOIN with the 'types' table on 'type_id'
                ->leftJoin("types as tp", "tp.id", "qs.type_id")
                // Perform a LEFT JOIN with the 'tellers' table on 'teller_id'
                ->leftJoin("tellers as ts", "ts.id", "qs.teller_id")
                // Exclude queues that are in the 'serving' or 'waiting' statuses
                ->whereNotIn('qs.status', ['serving', 'waiting'])
                // Order the results by 'updated_at' in descending order (latest first)
                ->orderBy('qs.updated_at', 'desc');

                // Filter by teller_id if provided
                if (!empty($teller_id)) {
                    $res->where('qs.teller_id', $teller_id);
                }

                // Filter by fromDate if provided (ensure it's not empty)
                if (!empty($fromDate)) {
                    $res->whereDate('qs.updated_at', '>=', $fromDate); // Compare with 'updated_at'
                }

                // Filter by toDate if provided (ensure it's not empty)
                if (!empty($toDate)) {
                    $res->whereDate('qs.updated_at', '<=', $toDate); // Compare with 'updated_at'
                }

    
            // Return the query results as a JSON response
            return response()->json([
                'rows' => $res->get() // Execute the query and return the rows
            ]);

        } catch (\Exception $e) {
            // In case of an error, catch the exception and return an error message
            $message = $e->getMessage(); // Get the exception message
            return response()->json([
                "message" => env('APP_DEBUG') ? $message : "Something went wrong!" // Return the error message or a generic one based on the app's debug mode
            ]);
        }
    }

    public function windowFetch(Request $request)
    {
        try {
            // Query the 'tellers' table to retrieve the 'id', 'teller_firstname', and 'teller_lastname' columns
            $tellers = DB::table('tellers')
                ->select('id', 'teller_firstname', 'teller_lastname') // Selecting the necessary columns
                ->where('branch_id',$request->branch_id)
                ->get(); // Execute the query and get the results

            // Format the results to prepare for a dropdown list
            $formattedTellers = $tellers->map(function ($teller) {
                return [
                    'value' => $teller->id, // Set 'id' as the value for the dropdown
                    'label' => $teller->teller_firstname . ' ' . $teller->teller_lastname // Combine first and last name as the label for the dropdown
                ];
            });

            // Return the formatted list of tellers as a JSON response
            return response()->json([
                'rows' => $formattedTellers, // Return the formatted tellers
            ]);
        } catch (\Exception $e) {
            // In case of an error, catch the exception and return an error message
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : "Something went wrong!" // Return the error message or a generic one based on the app's debug mode
            ]);
        }
    }

    public function viewTellerDropdown(Request $request)
    {
        try {
            $type_id = $request->input('type_id');

            // Check if type_id is a string and not numeric
            if (is_string($type_id) && !is_numeric($type_id)) {
                // Resolve the ID based on type_id (name of the type)
                $id = DB::table('types')->where('name', $type_id)->value('id');

                // Ensure $id is in the right format for comparison
                $tellers = DB::table('tellers')
                    ->whereRaw('JSON_CONTAINS(type_ids_selected, ?)', [json_encode([strval($id)])]) // Ensure ID is treated as a string
                    ->select('id', 'teller_firstname', 'teller_lastname')
                    ->get(); // Execute the query

                // Format the results for the dropdown
                $formattedTellers = $tellers->map(function ($teller) {
                    return [
                        'value' => $teller->id, // ID as the value
                        'label' => $teller->teller_firstname . ' ' . $teller->teller_lastname // Full name as the label
                    ];
                });

                return response()->json([
                    'rows' => $formattedTellers,
                    'id_type' => $id,
                ]);
            } else {
                // If type_id is numeric, handle it differently
                $tellers = DB::table('tellers')
                    ->whereRaw('JSON_CONTAINS(type_ids_selected, ?)', [json_encode([strval($type_id)])]) // Ensure type_id is treated as a string
                    ->select('id', 'teller_firstname', 'teller_lastname')
                    ->get(); // Execute the query

                // Format response for dropdown
                $formattedTellers = $tellers->map(function ($teller) {
                    return [
                        'value' => $teller->id, // ID as the value
                        'label' => $teller->teller_firstname . ' ' . $teller->teller_lastname // Full name as the label
                    ];
                });

                return response()->json([
                    'rows' => $formattedTellers,
                    'id_type' => $type_id
                ]);
            }

        } catch (\Exception $e) {
            // Handle exceptions and return an error message
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : "Something went wrong!"
            ]);
        }
    }




    public function getData($id = null)
    {
        $res = DB::table('tellers as t')
            ->select(
                "t.id",
                "t.teller_firstname",
                "t.teller_lastname",
                "t.teller_username",
                "t.teller_password",
                "t.type_id",
                "t.type_ids_selected",
                "t.Image",
                "t.branch_id",
                "b.branch_name",
                DB::raw("IFNULL(ts.status, 'Offline') as status"), // Set 'Offline' if null
                DB::raw('GROUP_CONCAT(tp.name SEPARATOR ", ") as type_names')
            )
            ->leftJoin("types as tp", DB::raw('JSON_CONTAINS(t.type_ids_selected, JSON_QUOTE(CAST(tp.id AS CHAR)))'), '>', DB::raw('0'))
            ->leftJoin('branchs as b',"b.id","=","t.branch_id")
            ->leftJoin('windows as ts', 't.id', '=', 'ts.teller_id')
            ->groupBy(
                "t.id",
                "t.teller_firstname",
                "t.teller_lastname",
                "t.teller_username",
                "t.teller_password",
                "t.type_ids_selected",
                "t.Image",
                "ts.status"
            )
            ->orderBy('t.created_at', 'desc');
    

        if ($id) {
            $res = $res->where("t.id", $id)->first();
        } else {
            $res = $res->get();
        }
    

        return $res;
    }
    
    
    public function validationLoginTeller (AdminRequest $request) {

        $teller = DB::table('tellers')
            ->where('teller_username', $request->Username)
            ->first();

            if ($teller) {
                 // Check if the admin exists and if the provided password matches the stored hashed password
                 if (!$teller || !Hash::check($request->Password, $teller->teller_password)) {
                    // If the username doesn't exist or the password is incorrect, return an error response
                    return response()->json([
                        'error' => 'Invalid username or password'
                    ],401);
                 }

                // update tellers has logged in
                DB::table('windows')
                ->where('teller_id', $teller->id)
                ->update(['status' => 'Online']);

                DB::table('tellers')
                ->where('id', $teller->id)
                ->update(['status' => 'Online']);

                // Generate a simple authentication token (or use Laravel Sanctum for better security)
                $token = base64_encode(Str::random(40));

                // If authentication is successful, return a success response
                return response()->json([
                    'tellerInformation' => [
                        'id' => $teller->id,
                        'tellerFirstname' => $teller->teller_firstname,
                        'tellerLastname' => $teller->teller_lastname,
                        'tellerUsername' => $teller->teller_username,
                        'token' => $token,
                        'type_id' => $teller->type_id,
                        'branch_id' => $teller->branch_id,
                    ],

                    'result' => 'teller'
                ]);
            }else {
                return response()->json([
                    'error' => 'Invalid credentials'
                ],400);
            }
        }   

    public function tellerLogout (Request $request) {
        try {
            // Extract the teller_id and type_id from the incoming request.
            $teller_id = $request->teller_id;
            $type_id = $request->type_id;
            $branch_id = $request->branch_id;
    
            // Check if there are any customers currently waiting for this teller to serve them.
            $list_waiting = DB::table('queues')
                            ->where('teller_id', $teller_id)  // Find queues assigned to this specific teller.
                            ->where('status', 'waiting')      // Filter only customers who are still waiting.
                            ->get();                          // Get all waiting customers.
    
            // Loop through all waiting customers to reassign them to other tellers.
            foreach ($list_waiting as $queue) {
                // Fetch all tellers who are signed in and assigned to this type of service (type_id).
                $tellers = DB::table('windows')
                    ->where('teller_id', '!=' , $teller_id)
                    ->where('type_id', $type_id)       // Find tellers for this specific type of service.
                    ->where('branch_id', $branch_id)   // Filter by branch ID.
                    ->where('status', 'Online')      // Filter only tellers who are currently signed in.
                    ->pluck('teller_id');              // Get a list of teller IDs.
    
                // Check if there are available tellers to handle the queues.
                if ($tellers->isEmpty()) {
                    return response()->json([
                        'message' => 'Please log in some tellers or finish the pending customers'  // No available tellers for this type_id
                    ], 400);  // HTTP status 400 indicating a bad request.
                }
    
                // Get the last assigned queue for this type of service, ordered by the most recent.
                $lastAssigned = DB::table('queues')
                                ->where('type_id', $type_id)       // Filter by the type_id of the service.
                                ->where('branch_id', $branch_id)   // Filter by branch ID.
                                ->orderBy('created_at', 'desc')    // Sort by creation date, getting the latest assigned queue.
                                ->first();                         // Retrieve only the most recent entry.
    
                // If there are available tellers, assign the next teller in a round-robin manner.
                // If there was no last assigned queue, start with the first teller (index 0).
                if ($tellers->count() > 0) {
                    // Find the index of the last assigned teller and move to the next one using modulo for round-robin.
                    $nextTellerIndex = $lastAssigned ? 
                        ($tellers->search($lastAssigned->teller_id) + 1) % $tellers->count() : 0;
                    // Get the teller ID at the calculated index.
                    $assignedTellerId = $tellers[$nextTellerIndex];
                }
                else {
                    // If no tellers are available, return an error message.
                    return response()->json([
                        'message' => 'Please log in some tellers or finish the pending customers'  // No tellers available for round-robin assignment
                    ], 400);  // HTTP status 400 for bad request.
                }
    

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
    
            $position = null;
    
            if ($queue->priority_service !== null) {
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
    
                // Update the queue with the new assigned teller and the next queue number.
                DB::table('queues')
                    ->where('id', $queue->id)  // Find the queue entry by its ID.
                    ->update([
                        'queue_number' => $nextQueueNumber,        // Update the queue number.
                        'email_status' => 'sending_customer',     // Mark the email status to indicate it's sending the customer info.
                        'teller_id' => $assignedTellerId,          // Reassign the queue to the new teller.
                        'position' => $position,                // Update the position for the queue.

                    ]);
    
            }
                // Update the status of the teller to 'Offline' when they log out.
                DB::table('windows')
                    ->where('teller_id', $teller_id)  // Locate the teller by their ID.
                    ->update(['status' => 'Offline']); // Set their status to 'Offline'.
            
                DB::table('tellers')
                    ->where('id', $teller_id)  
                    ->update(['status' => 'Offline']); 
                    
            // If everything was processed successfully, return a success message.
            return response()->json([
                'message' => 'Successfully logged out'  // Inform the user that the logout process is complete.
            ]);
    
        } catch (\Exception $e) {
            // If an exception occurs during the process, catch it and return the error message.
            $message = $e->getMessage();  // Get the error message.
            return response()->json([
                'message' => env('APP_DEBUG') ? $message : 'Something went wrong'  // Show the error message in debug mode, otherwise show a generic error.
            ]);
        }
    }
    

    public function valueTypeid (Request $request) {
        try {
            $type_id = $request->type_id;

            $serviceType = DB::table('types')
                ->where('id', $type_id)
                ->first();

            return response()->json([
                'servicename' => $serviceType ? $serviceType->name : null,
                'indicator' => $serviceType ? $serviceType->indicator : null,
            ]);

        } catch (\Exception $e) {
            $message = $e->getMessage();
            return response()->json([
                'message' => env('APP_DEBUG') ? $message : $message
            ]);
        }
    }

    public function fetchingAssignedTeller(Request $request)
    {
        try {
            // Get all service types
            $types = DB::table('types as tp')
                ->select('tp.id as type_id', 'tp.name as type_name', 'tp.indicator as type_indicator')
                ->where('branch_id', $request->branch_id)
                ->get();

            $typeList = [];

            foreach ($types as $type) {
                // Get tellers based on window assignments
                $tellers = DB::table('tellers as t')
                    ->join('windows as w', 'w.teller_id', '=', 't.id') // Inner join to get only assigned windows
                    ->select(
                        't.id',
                        't.teller_firstname',
                        't.teller_lastname',
                        DB::raw('GROUP_CONCAT(w.window_name) as window_names'),
                        'w.type_id as window_type_id'
                    )
                    ->where('w.type_id', $type->type_id)
                    ->where('t.branch_id', $request->branch_id)
                    ->groupBy(
                        't.id',
                        't.teller_firstname',
                        't.teller_lastname',
                        'w.type_id'
                    )
                    ->get();

                // Add the waiting queue length, customer details, and currently served customer for each teller
                foreach ($tellers as $teller) {
                    // Convert window_names string to array
                    $teller->window_name = explode(',', $teller->window_names);
                    unset($teller->window_names);

                    // Get all windows for this teller of this type
                    $windows = DB::table('windows')
                        ->where('teller_id', $teller->id)
                        ->where('type_id', $type->type_id)
                        ->pluck('window_name')
                        ->toArray();
                    
                    $teller->windows = $windows;

                    // Get the customers waiting for this teller
                    $waitingCustomers = DB::table('queues as q')
                        ->leftJoin('currencies as c', 'c.id', '=', 'q.currency_selected')
                        ->where('q.teller_id', $teller->id)
                        ->where('q.status', 'waiting')
                        ->select(
                            'q.name',
                            'q.queue_number',
                            'q.currency_selected',
                            'q.priority_service',
                            'c.currency_name',
                            'c.currency_symbol',
                            'c.flag'
                        )
                        ->get();

                    $teller->waiting_customers = $waitingCustomers;

                    // Get the currently served customer for this teller
                    $currentlyServed = DB::table('queues')
                        ->where('teller_id', $teller->id)
                        ->where('status', 'serving')
                        ->select('name', 'queue_number')
                        ->first();

                    $teller->currently_served = $currentlyServed;
                }

                if (count($tellers) > 0) {  // Only add types that have tellers with windows
                    $typeList[] = [
                        'type_id' => $type->type_id,
                        'type_name' => $type->type_name,
                        'type_indicator' => $type->type_indicator,
                        'tellers' => $tellers
                    ];
                }
            }

            return response()->json([
                'services' => $typeList
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching data: ' . $e->getMessage()
            ], 500);
        }
    }




}