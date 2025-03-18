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
            $tellerID = DB::table('tellers')->insertGetId([
                'teller_firstname' => $request->teller_firstname,
                'teller_lastname' => $request->teller_lastname,
                'teller_username' => $request->teller_username,
                'teller_password' => Hash::make($request->teller_password),
                'type_id' => $request->type_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
    
            //  Fetch the newly inserted section with the grade level
            $newTeller = DB::table('tellers as t')
                ->select(
                    "t.id",
                    "t.teller_firstname",
                    "t.teller_lastname",
                    "t.teller_username",
                    "t.teller_password",
                    "t.type_id",
                    "tp.name"
                )
                ->leftJoin("types as tp", "tp.id", "=", "t.type_id")
                ->where("t.id", $tellerID)
                ->first();
    
            return response()->json([
                'success' => true,
                'message' => 'Teller added successfully!',
                'row' => $newTeller 
            ]);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return response()->json([
                'success' => false,
                'message' => $message,
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


    public function update(TellerRequest $request){
        try{
            $teller = Teller::find($request->id);

            // Check if student exists
            if (!$teller) {
                return response()->json([
                    "message" => "Student not found!"
                ], 404);
            }
    
            // Update the student
            $teller->update($request->all());
    
            // Fetch updated data
            $row = $this->getData($teller->id);
    
            return response()->json([
                "row" => $row,
                "message" => "Student Successfully Updated!"
            ]);
        }catch(\Exception $e){
            $message = $e->getMessage();
            return response()->json([
                "message"=>env('APP_DEBUG')
                    ? $message
                    : $message
            ]);
        }
        
    }

    public function delete(Request $request)
{
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

            if (is_string($type_id) && !is_numeric($type_id)) {
                $id = DB::table('types')->where('name', $type_id)->value('id');
                $tellers = DB::table('tellers')
                ->where('type_id', $id)
                ->select('id', 'teller_firstname', 'teller_lastname')
                ->get(); // Execute query

            // Format response for dropdown
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
            }else{
                    // Fetch tellers based on type_id
                $tellers = DB::table('tellers')
                ->where('type_id', $type_id)
                ->select('id', 'teller_firstname', 'teller_lastname')
                ->get(); // Execute query

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
            "t.type_id",
            "tp.name"
        )
        ->leftJoin("types as tp", "tp.id", "t.type_id")
        ->orderBy('t.created_at', 'desc'); // Ordering by created_at in descending order


        

        if($id){
            $res = $res
                ->where("t.id",$id)
                -> first();
        }else{
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
                ]);
            }
    }
}