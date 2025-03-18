<?php

namespace App\Http\Controllers;

use App\Http\Requests\Waiting_time;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\WaitingTime;

class Waiting_timeController extends Controller
{
    public function store(Waiting_time $request)
    {
        try {
            list($minutes, $seconds) = explode(':', $request->Waiting_time);
            $totalSeconds = ($minutes * 60) + $seconds;

            // Check if a record already exists
            $waitingTime = WaitingTime::first();
            $message = '';

            if ($waitingTime) {
                // Update the existing record
                $waitingTime->update(["Waiting_time" => $totalSeconds]);
                $message = "Waiting time updated successfully!";
            } else {
                // Create a new record
                $waitingTime = WaitingTime::create(["Waiting_time" => $totalSeconds]);
                $message = "Waiting time created successfully!";
            }

            return response()->json([
                "message" => $message,
                "time" => [
                    'id' => $waitingTime->id,
                    'time' => $waitingTime->formatted_time
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : 'Something went wrong'
            ]);
        }
    }

    public function index(Request $request)
    {
        try {
            // Fetch all waiting times from the database
            $waitingTimes = DB::table('waiting_times')->get();

            // Return the data as a JSON response
            return response()->json([
                'message' => 'Data fetched successfully',
                'dataValue' => $waitingTimes
            ]);
        } catch (\Exception $e) {
            // Catch any errors and return them as a response
            $message = $e->getMessage();
            return response()->json([
                "message" => env('APP_DEBUG') ? $message : 'Something went wrong'
            ]);
        }
    }

}
