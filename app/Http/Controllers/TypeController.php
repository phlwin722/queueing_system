<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Type;
use App\Http\Requests\TypeRequest;
use \App\Models\Teller;
use \App\Models\Window;

class TypeController extends Controller
{
    public function index(Request $request) 
    {
        try {
            $rows = Type::all(); // Use Eloquent instead of DB::table
            return response()->json([ 'rows' => $rows ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') 
                ? $e->getMessage() 
                : "Something went wrong in index Type!"
            ]);
        }
    } 

    public function create(TypeRequest $request) 
    {
        try {
            $row = Type::create($request->all());
            return response()->json([
                "row" => $row,
                "message" => "Successfully Created!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') 
                ? $e->getMessage() 
                : "Something went wrong in create Type!"
            ]);
        }
    }

    public function update(TypeRequest $request) 
    {
        $id = $request->id;
        try {
            $row = Type::findOrFail($id);
            $row->update($request->all());
            return response()->json([
                "row" => $row->fresh(), // Return updated record
                "message" => "Successfully Updated!"
            ]);
        } catch (\Exception $e) {
           
            return response()->json([
                "message" => env('APP_DEBUG') 
                ? $e->getMessage() 
                : "Something went wrong in update Type!"
            ]);
        }
    }

        public function delete(Request $request)
    {
        try {
            // Check if any tellers or windows are using this type
            $tellerCount = Teller::whereIn('Type_id', $request->ids)->count();
            $windowCount = Window::whereIn('type_id', $request->ids)->count(); // Make sure key is 'type_id'

            if ($tellerCount > 0 || $windowCount > 0) {
                return response()->json([
                    "message" => "Cannot delete type. It is still referenced in windows."
                ], 400);
            }

            // Proceed with deletion
            Type::destroy($request->ids);

            return response()->json([
                "message" => "Successfully Deleted!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : "Something went wrong!"
            ]);
        }
    }
}