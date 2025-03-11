<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminRequest;
use App\Http\Requests\AdminpasswordRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;



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
            }else {
                return response()->json([
                    'error' => 'Invalid credentials'
                ], 401);
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
            /*  // Validate request
            $request->validate([
                'Username' => 'required|unique:admins,Username',
                'Password' => 'required|min:6'
            ]);
     */
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

      public function index (Request $request) {
        try {
            // Fetch all waiting times from the database
            $adminInfo = DB::table('admins')->get();

            return response()->json([
                'dataValue' => $adminInfo
            ]);

        } catch (\Exception $e) {
            $message =$e->getMessage();
            return response()->json([
                "message" => env ('APP_DEBUG') ? $message : $message
            ]);
        }
      }

      public function updateqInformation (AdminRequest $request) {
        try {
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
                DB::table('admins')
                    ->where('id', $request->id)
                    ->update([
                        "Password" => Hash::make($request->newPassword)
                    ]);
                    
                    $message = 'Admin password updated successfully';

                return response()->json([
                    "message"=> $message
                ]);
           } catch (\Exception $e) {
            $message =$e->getMessage();
                return response()->json([
                    "message" => env ('APP_DEBUG') ? $message : $message
                ]);
           }
      }
}
