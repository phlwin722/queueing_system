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
            $waitingTime = WaitingTime::where('branch_id', $request->branch_id)->first();
            $message = '';

            if ($waitingTime) {
                // Update the existing record
                $waitingTime->update([
                    "Waiting_time" => $totalSeconds,
                ]);
                $message = "Waiting time updated successfully!";
            } else {
                // Create a new record
                $waitingTime = WaitingTime::create([
                    "Waiting_time" => $totalSeconds,
                    "branch_id" => $request->branch_id
                ]);
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
            $branchId = $request->input('branch_id'); // get branch_id from request
            $lastUpdated = $request->input('last_updated');

            // If no branch ID, return error
            if (!$branchId) {
                return response()->json([
                    'message' => 'branch_id is required'
                ], 400);
            }

            // Get latest updated_at for this branch only
            $latestUpdate = DB::table('waiting_times')
                ->where('branch_id', $branchId)
                ->max('updated_at');

            // Skip fetch if not updated
            if ($lastUpdated && $latestUpdate && $latestUpdate <= $lastUpdated) {
                return response()->json([
                    'updated' => false,
                ]);
            }

            // Fetch waiting time for this branch only
            $waitingTime = DB::table('waiting_times')
                ->where('branch_id', $branchId)
                ->first();


            return response()->json([
                'updated' => true,
                'last_updated_at' => $latestUpdate,
                'dataValue' => $waitingTime,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => env('APP_DEBUG') ? $e->getMessage() : 'Something went wrong',
            ], 500);
        }
    }



}
