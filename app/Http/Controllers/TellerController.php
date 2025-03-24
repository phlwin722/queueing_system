<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teller;
use App\Http\Requests\TellerRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Requests\AdminRequest;

class TellerController extends Controller
{


    public function index(Request $request){

        try{
            $rows =  $this->getData();
            
            return response()->json([
                'rows' => $rows
            ]);

        }catch(\Exception $e){
            $message = $e->getMessage();
            return response()->json([
                "message"=>env('APP_DEBUG')
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

    public function create(TellerRequest $request){
        try {
            // Insert a new teller and get the ID
            $tellerID = DB::table('tellers')->insertGetId([
                'teller_firstname' => $request->teller_firstname,
                'teller_lastname' => $request->teller_lastname,
                'teller_username' => $request->teller_username,
                'teller_password' => Hash::make($request->teller_password),
                'type_ids_selected' => json_encode($request->type_ids_selected), // Store selected types as JSON
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // insert image on database
            $teller = DB::table('tellers')
                ->where('id',$tellerID)
                ->first();
            // delete the old message if it exist
            if ($teller->Image) {
                $oldImagePath = public_path($teller->Image);
                if (file_exists($oldImagePath)) {
                    unlink ($oldImagePath); // Delete the file
                }
            }

            // Process and save the uploaded file
            if ($request->hasFile('Image')) {
                $file = $request->file('Image');
                $filename = time(). '.' . $file->getClientOriginalExtension();

                // define the folder path (inside public directory)
                $folderPath = public_path('assets/teller/' . $tellerID);
                    
                // Ensure the folder exists
                if (!file_exists($folderPath)) {
                    mkdir($folderPath,0755,true);  // Create folder with proper permissions
                }

                // move file to the folder
                $file->move($folderPath,$filename);

                // correct url public access
                $filePath = "assets/teller/{$tellerID}/{$filename}";

                /// **FIXED:** Update database using Query Builder (no `save()` method)
                DB::table('tellers')
                    ->where('id',$tellerID)
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
                    DB::raw('GROUP_CONCAT(tp.name SEPARATOR ", ") as type_names')  // Concatenate type names
                )
                ->leftJoin("types as tp", DB::raw('JSON_CONTAINS(t.type_ids_selected, JSON_QUOTE(CAST(tp.id AS CHAR)))'), '>', DB::raw('0'))
                ->where("t.id", $tellerID)
                ->groupBy("t.id")
                ->first();

    
            return response()->json([
                'success' => true,
                'message' => 'Teller added successfully!',
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
    public function fetchImage (Request $request) {
        $id = $request->id;
        
        $tellerImage = DB::table('tellers')
            ->where('id',$id)
            ->first();

        return response()->json([
            'Image' => $tellerImage->Image ? asset($tellerImage->Image) : asset('assets/no-image-user.png')
        ]);
    }


    // fetching image each of teller layout
    public function fetchImageTeller (Request $request){
        try{
            $id = $request->id;

            $tellerImage = DB::table('tellers')
                ->where('id',$id)
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
    public function fetchImageTellerCsDashboaard (Request $request) {
        try {
            $id = $request->id;

            $tellerImage = DB::table('tellers')
                ->where('id',$id)
                ->first();
    
                return response()->json([
                    'Image' => $tellerImage->Image ? asset($tellerImage->Image) : asset('assets/no-image-user.png')
                ]);
        } catch (\Exception $e) {
            return response ()-> json([
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
            $teller->teller_firstname = $request->teller_firstname;
            $teller->teller_lastname = $request->teller_lastname;
            $teller->teller_username = $request->teller_username;
            $teller->type_ids_selected = $request->type_ids_selected;
    
            // Update password only if provided
            if ($request->filled('teller_password')) {
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
                "message" => "Teller Successfully Updated!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : "An error occurred"
            ]);
        }
    }
    

    public function delete(Request $request){
        try {
            // Check if any windows are using this teller
            $windowCount = \App\Models\Window::whereIn('teller_id', $request->ids)->count();

            if ($windowCount > 0) {
                return response()->json([
                    "message" => "Cannot delete teller. It is still referenced in windows."
                ], 400);
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


    public function getData($id = null){
        $res = DB::table('tellers as t')
            ->select(
                "t.id",
                "t.teller_firstname",
                "t.teller_lastname",
                "t.teller_username",
                "t.teller_password",
                "t.type_ids_selected",
                "t.Image",
                DB::raw('GROUP_CONCAT(tp.name SEPARATOR ", ") as type_names')  // Concatenate type names
            )
            ->leftJoin("types as tp", DB::raw('JSON_CONTAINS(t.type_ids_selected, JSON_QUOTE(CAST(tp.id AS CHAR)))'), '>', DB::raw('0'))
            ->groupBy(
                "t.id", 
                "t.teller_firstname", 
                "t.teller_lastname", 
                "t.teller_username", 
                "t.teller_password", 
                "t.type_ids_selected",
                "t.Image"
            )
            ->orderBy('t.created_at', 'desc'); // Ordering by created_at in descending order
    
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
                        'error' => 'Invalid Credentials'
                    ],401);
                 }
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
                        'type_id' => $teller->type_id
                    ],
                    'message' => "Login sucessfull teller",
                    'result' => 'teller'
                ]);
            }else {
                return response()->json([
                    'error' => 'Invalid credentials'
                ],400);
            }
    }

    public function valueTypeid (Request $request) {
        try {
            $type_id = $request->type_id;

            $serviceType = DB::table('types')
                            ->where('id',$type_id)
                            ->first();

            return response()->json([
                'servicename' =>  $serviceType ? $serviceType->name : null
            ]);

        } catch(\Exception $e) {
            $message = $e->getMessage();
            return response()->json([
                'message' => env('APP_DEBUG')? $message : $message
            ]);
        }
    }
}