<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminRequest;
use App\Http\Requests\ManagerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Manager;
use Illuminate\Support\Carbon;

class ManagerController extends Controller
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

    public function create (ManagerRequest $request) {
        $managerID = DB::table('managers')->insertGetId([
            'manager_firstname' => $request->manager_firstname,
            'manager_lastname' => $request->manager_lastname,
            'manager_username' => $request->manager_username,
            'manager_password' => Hash::make($request-> manager_password),
            'manager_status' => 'Offline',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // check if existing image on database
        $managerImage = DB::table('managers')
            ->where('id',$managerID)
            ->first();

        // delete old image if it exist
        if($managerImage->Image) {
            $oldImagePath = public_path($managerImage->Image);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath); // Delete the file
            }
        }

        // process and save the upload file
        if($request->hasFile('Image')) {
            $file = $request->file('Image');
            $filename = time() . '.' . $file->getClientOriginalExtension();

            // define the folder path 
            $folderPath = public_path('assets/manager/' . $managerID);

            // ensure the folder exists
            if (!file_exists($folderPath)) {
                mkdir($folderPath,0755, true); // Create folder with proper permissions
            }

            // move file to the folder
            $file->move($folderPath,$filename);

            // correct url public access
            $filePath = "assets/manager/{$managerID}/{$filename}";

            // Update database using Query Builder (no `save()` method)
            DB::table('managers')
                ->where('id',$managerID)
                ->update(["Image" => $filePath]);   
        }

        // Fetch the newly inserted manager and join with the types table
        $newManager = DB::table('managers as m')
        ->select(
            "m.id",
            "m.manager_firstname",
            "m.manager_lastname",
            "m.manager_username",
            "m.manager_password",
            "m.manager_status",
            "Image",
            "b.branch_name"
         )
        ->where("m.id", $managerID)
        ->leftJoin("branchs as b","b.id","=","m.branch_id")
        ->first();

        return response()->json([
            'success' => true,
            'message' => 'Manager added successfully!',
            'row' => $newManager
        ]);
    }

    // fetching image each of manager
    public function fetchImage (Request $request) {
        $id = $request->id;
        
        $managerImage = DB::table('managers')
            ->where('id',$id)
            ->first();

        return response()->json([
            'Image' => $managerImage->Image ? asset($managerImage->Image) : asset('assets/no-image-user.png')
        ]);
    }

    public function update(ManagerRequest $request)
    {
        try {
            $manager = Manager::find($request->id);
    
            // Check if manager exists
            if (!$manager) {
                return response()->json([
                    "message" => "Manager not found!"
                ], 404);
            }
    
            // Update fields manually
            $manager->manager_firstname = $request->manager_firstname;
            $manager->manager_lastname = $request->manager_lastname;
            $manager->manager_username = $request->manager_username;
    
            // Update password only if provided
            if ($request->manager_password != "oldpass") {
                $manager->manager_password = Hash::make($request->manager_password);
            }
    
            // Update the image if provided
            if ($request->hasFile('Image')) {
                // Delete the old image if it exists
                if ($manager->Image && file_exists(public_path($manager->Image))) {
                    unlink(public_path($manager->Image)); // Delete the old image file
                }
    
                // Process and save the new uploaded image
                $file = $request->file('Image');
                $filename = time() . '.' . $file->getClientOriginalExtension();
    
                // Define the folder path (inside the public directory)
                $folderPath = public_path('assets/manager/' . $request->id);
    
                // Ensure the folder exists
                if (!file_exists($folderPath)) {
                    mkdir($folderPath, 0755, true);  // Create folder with proper permissions
                }
    
                // Move the file to the folder
                $file->move($folderPath, $filename);
    
                // Save the file path in the database
                $filePath = "assets/manager/{$request->id}/{$filename}";
    
                // Update the image path using Eloquent
                $manager->Image = $filePath;
            }
    
            // Save the updates (will save the image path as well)
            $manager->save();
    
            // Fetch updated data
            $row = $this->getData($manager->id);
    
            return response()->json([
                "row" => $row,
                "message" => "Manager Successfully Updated!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : "An error occurred"
            ]);
        }
    }

    public function delete(Request $request){
        try {
            $data = DB::table('managers')
                        ->whereIn('id', $request->ids)
                        ->get();

            // Check if no data was found
            if (!$data) {
                return response()->json([
                    'error' => "Manager not found."
                ], 404);
            }
            
            // Loop through each manager and check if they are online
            foreach ($data as $val) {
                  // Check if the manager is online
                if ($val->manager_status == 'Online') {
                    return response()->json([
                        'error' => "Sorry, we cannot delete this manager because they are still online."
                    ], 400);
                }

                // get date now
                $dataNow = Carbon::now()->toDateString();
                // Archive the manager's data
                DB::table('archives')
                ->insert([
                    'First_name' => $val->manager_firstname,
                    'Lastname' => $val->manager_lastname,
                    'Username' => $val->manager_username,
                    'Password' => $val->manager_password,
                    'Branch_id' => $val->branch_id,
                    'Image' => $val->Image,
                    'types' => '0',
                    'created_at' => $dataNow
                ]);
            }
                    
            // Proceed with deletion
            Manager::destroy($request->ids);

            return response()->json([
                "message" => "Successfully Deleted!"
            ]);
        } catch (\Exception $e) {
        return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : "Something went wrong!"
            ]);
        }
    }
    public function getData($id = null){
        $res = DB::table('managers as m')
            ->select(
                "m.id",
                "m.manager_firstname",
                "m.manager_lastname",
                "m.manager_username",
                "m.manager_password",
                "m.manager_status",
                "m.Image", // Assuming the status is in the window table
                "b.branch_name"
            )
            ->leftJoin("branchs as b","b.id", "=", "m.branch_id")
            ->orderBy('m.created_at', 'desc'); // Ordering by created_at in descending order
    
        if ($id) {
            $res = $res->where("m.id", $id)->first();
        } else {
            $res = $res->get();
        }
        
        return $res;
    }

    public function validationLoginManager (AdminRequest $request) {
        $manager = DB::table('managers')
            ->where('manager_username', $request->Username)
            ->first();
            
                if ($manager) {
                    // Check if the admin exists and if the provided password matches the stored hashed password
                    if (!$manager || !Hash::check($request->Password, $manager->manager_password)) {
                        // If the username doesn't exist or the password is incorrect, return an error response
                        return response()->json([
                            'error' => 'Incorrect password'
                        ],401);
                    }else if( $manager->branch_id == null){
                        // If the manager is not assigned to a branch, return an error response
                        return response()->json([
                            'error' => 'Manager not assigned to a branch'
                        ], 401);
                    }
    
                    // Generate a simple authentication token (or use Laravel Sanctum for better security)
                    $token = base64_encode(Str::random(40));
                    DB::table('managers')
                    ->where('id', $manager->id)
                    ->update(['manager_status' => 'Online']);
                    // If authentication is successful, return a success response
                    return response()->json([
                        'managerInformation' => [
                            'id' => $manager->id,
                            'manager_firstname' => $manager->manager_firstname,
                            'manager_lastname' => $manager->manager_lastname,
                            'manager_username' => $manager->manager_username,
                            'Image' => $manager->Image,
                            'manager_status' => $manager->manager_status,
                            'branch_id' => $manager->branch_id,
                            'token' => $token,
                        ],
    
                        'result' => 'manager'
                    ]);
                } else {
                    // If the username doesn't exist, return an error response
                    return response()->json([
                        'result' => 'teller'
                    ]);
                }
        }
    

        public function managerLogout(Request $request) {
            $manager = DB::table('managers')
                ->where('id', $request->manager_id)
                ->update(['manager_status' => 'Offline']);

            return response()->json([
                'message' => 'Logout successful'
            ]);
        }
}
