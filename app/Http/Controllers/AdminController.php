<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminRequest;
use App\Http\Requests\AdminpasswordRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class AdminController extends Controller
{
    public function adminValidate(AdminRequest $request) 
    {
        try {
            // Find an admin record in the database with the provided username
            $admin = Admin::where('Username', $request->Username)->first();
        
                if ($admin) {
                    // Check if the admin exists and if the provided password matches the stored hashed password
                    if (!$admin || !Hash::check($request->Password, $admin->Password)) {
                        // If the username doesn't exist or the password is incorrect, return an error response
                        return response()->json([
                            'error' => 'Invalid credentials'
                        ], 401);
                    }

                    // Generate a simple authentication token (or use Laravel Sanctum for better security)
                    $token = base64_encode(Str::random(40));

                    // If authentication is successful, return a success response
                    return response()->json([
                        // 'result' => $admin,
                        'adminInformation' => $admin,
                        'token' => $token,
                        'result' => "admin",
                        'message' => 'Login successful'
                    ]);
                }
            } catch (\Exception $e) {
                $message = $e->getMessage();
                return response()->json(
                [
                    "message"=> env('APP_DEBUG')? $message : 'Something went wrong'
                ]);
        }
    }
    
      // Function to Insert Admin
      public function createAdmin(Request $request)
      {
        try {
          // Insert admin into database
            $admin = Admin::create([
                'Firstname' => $request->Firstname,
                'Lastname' => $request->Lastname,
                'Username' => $request->Username,
                'Password' => Hash::make($request->Password) // Hash password before storing
            ]);

            return response()->json([
                'message' => 'Admin created successfully',
                'admin' => $admin
            ], 201);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return response()->json(
                [
                    "message"=> env('APP_DEBUG')? $message : 'Something went wrong'
                ]);
        }
      }

        public function index(Request $request) {
            try {
                // Get the ID from the request
                $id = $request->input('id');
        
                // Validate the ID (Ensure it's required and exists in the admins table)
                $validator = Validator::make(['id' => $id], [
                    'id' => 'required|exists:admins,id'  // Ensure it's an integer and exists in the database
                ]);
        
                // Check if validation fails
                if ($validator->fails()) {
                    return response()->json([
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);  // Return 422 for validation errors
                }
        
                // Fetch the admin data based on the ID
                $adminInfo = DB::table('admins')
                            ->where('id', $id)
                            ->first();
        
                // Return the admin data if found
                return response()->json([
                    'dataValue' => [
                        'id' => $adminInfo->id,
                        'Firstname' => $adminInfo->Firstname,
                        'Lastname' => $adminInfo->Lastname,
                        'Username' => $adminInfo->Username,
                        'Image' => $adminInfo->Image ? asset($adminInfo->Image) 
                                                     : asset('assets/no-image-user.png')
                    ]
                ]);
        
            } catch (\Exception $e) {
                // Return any exception that occurs during processing
                $message = $e->getMessage();
                return response()->json([
                    "message" => env('APP_DEBUG') ? $message : 'An error occurred'
                ], 500);  // Return 500 for server errors
            }
        }
    
      public function updateqInformation (Request $request) {
        try {

            // Validate Firstname and Lastname fields
            $validator = Validator::make($request->all(), [
                'Firstname' => [
                    'required',
                    'string', 
                    'max:255'
                ],
                "Username" => [
                    'required',
                    'regex:/^[a-zA-Z\s]+$/'
                ],

                'Lastname' => [
                    'required',
                    'string', 
                    'max:255'
                ],
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // update information
            DB:: table('admins')
                ->where('id',$request->id)
                ->update([
                    'Username' => $request->Username,
                    'Firstname' => $request->Firstname,
                    'Lastname' => $request->Lastname,
                    'updated_at' => now(),
                ]);

                return response()->json([
                    'message' => 'Admin Information updated successfully'
                ]);
            
        } catch (\Exception $e) {
            $message =$e->getMessage();
            return response()->json([
                "message" => env ('APP_DEBUG') ? $message : $message
            ]);
        }
      }

      public function updatePassword (AdminpasswordRequest $request) {
        try {
            // Ensure the admin exists before updating
            $admin = DB::table('admins')->where('id', $request->id)->first();
            
            if (!$admin) {
                return response()->json([
                    "message" => "Admin with the given ID not found."
                ], 404);
            }
    
            // Proceed to update the password
            $affectedRows = DB::table('admins')
                ->where('id', $request->id)
                ->update([
                    "Password" => Hash::make($request->newPassword),
                    "updated_at" => now()
                ]);
            
            // Check if any rows were affected
            if ($affectedRows === 0) {
                return response()->json([
                    "message" => "No rows were updated, possibly incorrect ID."
                ], 400);
            }
    
            return response()->json([
                "message" => "Admin password updated successfully"
            ]);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return response()->json([
                "message" => env('APP_DEBUG') ? $message : 'An error occurred while updating the password.'
            ], 500);
        }
    }
    
    public function uploadImage(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'id' => 'required|exists:admins,id',
                'Image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);
    
            // Get the admin ID
            $adminId = $request->id;
    
            // Retrieve admin record
            $admin = DB::table('admins')->where('id', $adminId)->first();
            
            if (!$admin) {
                return response()->json(['message' => 'Admin not found'], 404);
            }

            // Delete the old image if it exists
            if ($admin->Image) {
                $oldImagePath = public_path($admin->Image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath); // Delete the file
                }
            }
    
            // Process and save the uploaded file
            if ($request->hasFile('Image')) {
                $file = $request->file('Image');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                
                // Define the folder path (inside public directory)
                $folderPath = public_path('assets/admin/' . $adminId);  
    
                // Ensure the folder exists
                if (!file_exists($folderPath)) {
                    mkdir($folderPath, 0755, true); // Create folder with proper permissions
                }
    
                // Move file to the folder
                $file->move($folderPath, $filename);
    
                // Correct URL for public access
                $filePath = "assets/admin/{$adminId}/{$filename}";
    
                // **FIXED:** Update database using Query Builder (no `save()` method)
                DB::table('admins')->where('id', $adminId)->update([
                    'Image' => $filePath
                ]);
    
                return response()->json([
                    'message' => 'Image uploaded successfully!',
                    'Image' => asset($filePath) // Convert relative path to full URL
                ]);
            }
    
            return response()->json(['message' => 'No image uploaded'], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Upload failed!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
}