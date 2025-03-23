<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Currency;
use App\Http\Requests\CurrencyRequest;
use Illuminate\Support\Facades\DB;

class CurrencyController extends Controller
{
    public function showData(Request $request) 
    {
        try {
            $rows = Currency::all(); // Use Eloquent instead of DB::table
            return response()->json([ 'rows' => $rows ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') 
                ? $e->getMessage() 
                : "Something went wrong in showing Currencies!"
            ]);
        }
    }
    


    public function create(CurrencyRequest $request) 
    {
        try {
            $row = Currency::create($request->all());
            return response()->json([
                "row" => $row,
                "message" => "Successfully Created!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') 
                ? $e->getMessage() 
                : "Something went wrong in create Currency!"
            ]);
        }
    }

    public function update(CurrencyRequest $request) 
    {
        $id = $request->id;
        try {
            $row = Currency::findOrFail($id);
            $row->update($request->all());
            return response()->json([
                "row" => $row->fresh(), // Return updated record
                "message" => "Successfully Updated!"
            ]);
        } catch (\Exception $e) {
           
            return response()->json([
                "message" => env('APP_DEBUG') 
                ? $e->getMessage() 
                : "Something went wrong in update Currency!"
            ]);
        }
    }

    public function delete(Request $request)
    {
        try {
            // Check if ids exist and it's an array
            if (!$request->has('ids') || !is_array($request->ids)) {
                return response()->json([
                    "message" => "Invalid request data."
                ], 400);
            }
    
            // Use whereIn to delete multiple records
            Currency::whereIn('id', $request->ids)->delete();
    
            return response()->json([
                "message" => "Successfully Deleted!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : "Something went wrong!"
            ], 500);
        }
    }
    
}