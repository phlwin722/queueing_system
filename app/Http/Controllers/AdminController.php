<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class AdminController extends Controller
{
    public function adminValidate(AdminRequest $request) 
    {
        // Find an admin record in the database with the provided username
        $admin = Admin::where('Username', $request->Username)->first();
    
        // Check if the admin exists and if the provided password matches the stored hashed password
        if (!$admin || !Hash::check($request->Password, $admin->Password)) {
            // If the username doesn't exist or the password is incorrect, return an error response
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    
        // If authentication is successful, return a success response
        return response()->json(['message' => 'Login successful']);
    }
    
      // Function to Insert Admin
      public function createAdmin(Request $request)
      {
           /*  // Validate request
            $request->validate([
                'Username' => 'required|unique:admins,Username',
                'Password' => 'required|min:6'
            ]);
     */
          // Insert admin into database
          $admin = Admin::create([
              'Username' => $request->Username,
              'Password' => Hash::make($request->Password) // Hash password before storing
          ]);
  
          return response()->json([
              'message' => 'Admin created successfully',
              'admin' => $admin
          ], 201);
      }
}
