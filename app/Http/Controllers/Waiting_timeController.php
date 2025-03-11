<?php

namespace App\Http\Controllers;

use App\Http\Requests\Waiting_time;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Waiting_timeController extends Controller
{
    public function store (Waiting_time $request) {
        try {
            // Insert the validated data into the database
            $waitingTime = DB::table('waiting_times')->insert ([
                "Waiting_time" => $request->Waiting_time,
                "Prepared" => $request->Prepared,
                "created_at" => now(),
                "updated_at" => now(),
            ]);
    
            if ($waitingTime) {
                return response () -> json ([
                    "message" => "Waiting time created successfully"
                ], 201);
            }
        } catch (\Exception $e) {
            // Catch any errors and return them as a response
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
            $waitingTimes = DB::table('waiting_times')->get();

                // Return the data as a JSON response
                return response()->json ([
                    'message' => 'Data fetched successfully',
                    'dataValue' => $waitingTimes
                ]);
        } catch (\Exception $e) {
             // Catch any errors and return them as a response
             $message = $e->getMessage();
             return response()->json([
                     "message"=> env('APP_DEBUG')? $message : 'Something went wrong'
                 ]);
        }
    }

    public function update (Waiting_time $request) {
        try {
            // Update existing record
            $waitingTime = DB::table('waiting_times')
                ->where('id', $request->id)->update([
                    'Waiting_time' => $request->Waiting_time,
                    'Prepared' => $request->Prepared,
                    'updated_at' => now(),
            ]);
            $message = 'Waiting time updated successfully';

            return response()->json([
                "message"=> $message
            ]);
        }
        catch (\Exception $e) {
            // Catch any errors and return them as a response
            $message = $e->getMessage();
            return response()->json(
                [
                    "message"=> env('APP_DEBUG')? $message : 'Something went wrong'
                ]);
        }
    }
}
