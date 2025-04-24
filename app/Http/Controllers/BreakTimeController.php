<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests\BreakTimeRequest;
use Illuminate\Support\Facades\DB;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // ✅ Import Log

class BreakTimeController extends Controller
{


    public function storeBreakTime(BreakTimeRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $breakFrom = Carbon::parse($validatedData['break_from']);
            $breakTo = Carbon::parse($validatedData['break_to']);
            $branchId = $request->input('branch_id');

            if ($breakFrom >= $breakTo) {
                return response()->json([
                    'message' => 'The "From" time must be earlier than the "To" time.'
                ], 400);
            }

            // ✅ Check if a break time entry exists for the branch_id
            $breakTime = BreakTime::where('branch_id', $branchId)->first();

            if ($breakTime) {
                $breakTime->update([
                    'break_from' => $breakFrom,
                    'break_to' => $breakTo,
                ]);
                $message = "Break time updated successfully!";
            } else {
                $breakTime = BreakTime::create([
                    'break_from' => $breakFrom,
                    'break_to' => $breakTo,
                    'branch_id' => $branchId,
                ]);
                $message = "Break time created successfully!";
            }

            return response()->json([
                "message" => $message,
                "time" => [
                    'id' => $breakTime->id,
                    'branch_id' => $breakTime->branch_id,
                    'break_from' => $breakTime->break_from,
                    'break_to' => $breakTime->break_to
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }




    public function fetchBreakTime(Request $request)
    {
        try {
            $lastUpdated = $request->input('last_updated');
            $branchId = $request->input('branch_id');
            $breakTime = DB::table('break_times')
            ->where('branch_id', $branchId)
            ->first();
            $latestUpdate = optional($breakTime)->updated_at;
    
            if ($lastUpdated && $latestUpdate && $latestUpdate <= $lastUpdated) {
                return response()->json([
                    'updated' => false,
                ]);
            }
    
            return response()->json([
                'updated' => true,
                'last_updated_at' => $latestUpdate,
                'dataValue' => $breakTime,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : 'Something went wrong'
            ]);
        }
    }
    
    

}
