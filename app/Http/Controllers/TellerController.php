<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teller;
use App\Http\Requests\TellerRequest;
use Illuminate\Support\Facades\DB;

class TellerController extends Controller
{
    public function index(Request $request) 
    {
        try {
            $rows = $this->getData(); 
            
            return response()->json([
                'rows' => $rows,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : "Something went wrong!"
            ]);
        }
    } 

    public function create(TellerRequest $request) 
    {
        try {
            $validatedData = $request->validated(); 
            $row = Teller::create($validatedData);

            $typeName = DB::table('types')
                ->where('id', $row->types_id)
                ->value('name');

            return response()->json([
                "Teller" => $this->getData($row->id),
                "message" => "Successfully Created!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : "Something went wrong!"
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

    public function update(TellerRequest $request) 
    {   
        try {
            $row = Teller::findOrFail($request->id);
            $row->update($request->validated());
            $updatedRow = $this->getData($request->id);

            return response()->json([
                "Teller" => $updatedRow,
                "message" => "Successfully Updated!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : "Something went wrong!"
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


    public function getData($id = null)
    {
        $query = Teller::leftJoin('types', 'Teller.types_id', '=', 'types.id')
            ->select('Teller.*', 'types.name as type_name');
    
        return $id 
            ? $query->where('Teller.id', $id)->first() 
            : $query->get();
    }
}