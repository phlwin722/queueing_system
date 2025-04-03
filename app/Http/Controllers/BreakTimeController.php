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
            $validatedData = $request->validated(); // ✅ Use validated() method

            // ✅ Check if 'from' time is earlier than 'to' time
            $breakFrom = Carbon::parse($validatedData['break_from']);
            $breakTo = Carbon::parse($validatedData['break_to']);
            $message = '';
            $breakTime = BreakTime::first();
            if ($breakFrom >= $breakTo) {
                return response()->json([
                    'message' => 'The "From" time must be earlier than the "To" time.'
                ], 400);
            }else{
                if ($breakTime) {
                    $breakTime->update($validatedData);
                    $message = "Break time updated successfully!";
                } else {
                    $breakTime = BreakTime::create($validatedData);
                    $message = "Break time created successfully!";
                }
            }
            
            return response()->json([
                "message" => $message,
                "time" => [
                    'id' => $breakTime->id,
                    'break_from' => $breakTime->break_from,
                    'break_to' => $breakTime->break_to
                ]
            ], 201);
            

        } catch (\Exception $e) {
            Log::error($e); // ✅ Log error for debugging
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }



    public function fetchBreakTime()
    {
        try {
            // ✅ Fetch only the first break time
            $breakTime = DB::table('break_times')->first();
    
            return response()->json([
                'message' => 'Data fetched successfully',
                'dataValue' => $breakTime // ✅ No need for an array
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : 'Something went wrong'
            ]);
        }
    }
    

}
