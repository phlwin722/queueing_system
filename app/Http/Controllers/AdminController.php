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
use App\Events\AdminInfoEvent;


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
                        'error' => 'Invalid username or password'
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
                    "message" => env('APP_DEBUG') ? $message : 'Something went wrong'
                ]
            );
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
                    "message" => env('APP_DEBUG') ? $message : 'Something went wrong'
                ]
            );
        }
    }

    public function index(Request $request)
    {
        try {
            // Get the ID from the request
            $id = $request->input('id');
            $branch_id = $request->branch_id;

            if ($branch_id != null) {

                // Fetch the admin data based on the ID
                $managerInfo = DB::table('managers as m')
                    ->select([
                        'm.id',
                        'm.manager_firstname',
                        'm.manager_lastname',
                        'm.manager_username',
                        'm.Image',
                        'm.branch_id',
                        'b.branch_name'
                    ])
                    ->where('m.id', $id)
                    ->leftJoin('branchs as b', 'b.id', '=', 'm.branch_id')
                    ->first();

                if ($managerInfo) {
                    // Return the admin data if found
                    return response()->json([
                        'dataValue' => [
                            'id' => $managerInfo->id,
                            'Firstname' => $managerInfo->manager_firstname,
                            'Lastname' => $managerInfo->manager_lastname,
                            'Username' => $managerInfo->manager_username,
                            'Image' => $managerInfo->Image ? asset($managerInfo->Image)
                                : asset('assets/no-image-user.png'),
                            'branch_id' => $managerInfo->branch_id,
                            'branch_name' => $managerInfo->branch_name,
                        ]
                    ]);
                }
            } else {
                // Fetch the admin data based on the ID
                $adminInfo = DB::table('admins')
                    ->where('id', $id)
                    ->first();

                if ($adminInfo) {
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
                }
            }
        } catch (\Exception $e) {
            // Return any exception that occurs during processing
            $message = $e->getMessage();
            return response()->json([
                "message" => env('APP_DEBUG') ? $message : 'An error occurred'
            ], 500);  // Return 500 for server errors
        }
    }

    public function updateqInformation(Request $request)
    {
        try {
            // Check if the request includes a branch_id (distinguishes between manager and admin)
            if ($request->branch_id != null) {

                // Validate the request data for managers
                $validator = Validator::make($request->all(), [
                    'Firstname' => [
                        'required',      // Field is required
                        'string',        // Must be a string
                        'max:255'        // Maximum length is 255 characters
                    ],
                    "Username" => [
                        'required',                   // Field is required
                        'regex:/^[a-zA-Z\s]+$/'       // Only letters and spaces allowed
                    ],
                    'Lastname' => [
                        'required',
                        'string',
                        'max:255'
                    ],
                ]);

                // If validation fails, return a 422 error with validation messages
                if ($validator->fails()) {
                    return response()->json([
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);
                }

                // Update manager's information in the 'managers' table
                DB::table('managers')
                    ->where('id', $request->id)  // Match manager by ID
                    ->update([
                        'manager_username' => $request->Username,    // Set new username
                        'manager_firstname' => $request->Firstname,  // Set new first name
                        'manager_lastname' => $request->Lastname,    // Set new last name
                        'updated_at' => now(),                       // Set current timestamp
                    ]);

                // Prepare the data for broadcasting (avoid passing the full $request)
                $data = [
                    'id' => $request->id,
                    'Username' => $request->Username,
                    'Firstname' => $request->Firstname,
                    'Lastname' => $request->Lastname,
                ];

                // Broadcast the updated manager info event (for frontend updates)
                broadcast(new AdminInfoEvent($data));

                // Return a success message
                return response()->json([
                    'message' => 'Admin Information updated successfully'
                ]);
            } else {
                // If branch_id is not set, treat as admin

                // Validate the request data for admins
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

                // If validation fails, return error with details
                if ($validator->fails()) {
                    return response()->json([
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);
                }

                // Update admin's information in the 'admins' table
                DB::table('admins')
                    ->where('id', $request->id)
                    ->update([
                        'Username' => $request->Username,
                        'Firstname' => $request->Firstname,
                        'Lastname' => $request->Lastname,
                        'updated_at' => now(),
                    ]);

                // Prepare data for broadcasting (not the whole request)
                $data = [
                    'id' => $request->id,
                    'Username' => $request->Username,
                    'Firstname' => $request->Firstname,
                    'Lastname' => $request->Lastname,
                ];

                // Broadcast the updated admin info event
                broadcast(new AdminInfoEvent($data));

                // Return success message
                return response()->json([
                    'message' => 'Admin Information updated successfully'
                ]);
            }
        } catch (\Exception $e) {
            // Catch any exceptions that occur during the process
            $message = $e->getMessage();

            // Return the exception message in the response
            // Optionally you can hide the message when APP_DEBUG is false
            return response()->json([
                "message" => env('APP_DEBUG') ? $message : $message
            ]);
        }
    }


    public function updatePassword(AdminpasswordRequest $request)
    {
        try {
            if ($request->branch_id != null) {
                // Ensure the admin exists before updating
                $admin = DB::table('managers')->where('id', $request->id)->first();

                if (!$admin) {
                    return response()->json([
                        "message" => "Admin with the given ID not found."
                    ], 404);
                }

                // Proceed to update the password
                $affectedRows = DB::table('managers')
                    ->where('id', $request->id)
                    ->update([
                        "manager_password" => Hash::make($request->newPassword),
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
            } else {
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
            }
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
            if ($request->branch_id != null) {
                // Validate input
                $request->validate([
                    'Image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
                ]);

                // Get the admin ID
                $adminId = $request->id;

                // Retrieve admin record
                $admin = DB::table('managers')->where('id', $adminId)->first();

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
                    $folderPath = public_path('assets/manager/' . $adminId);

                    // Ensure the folder exists
                    if (!file_exists($folderPath)) {
                        mkdir($folderPath, 0755, true); // Create folder with proper permissions
                    }

                    // Move file to the folder
                    $file->move($folderPath, $filename);

                    // Correct URL for public access
                    $filePath = "assets/manager/{$adminId}/{$filename}";

                    // **FIXED:** Update database using Query Builder (no `save()` method)
                    DB::table('managers')->where('id', $adminId)->update([
                        'Image' => $filePath
                    ]);

                    return response()->json([
                        'message' => 'Image uploaded successfully!',
                        'Image' => asset($filePath) // Convert relative path to full URL
                    ]);
                }
            } else {
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
                    if (file_exists(filename: $oldImagePath)) {
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
