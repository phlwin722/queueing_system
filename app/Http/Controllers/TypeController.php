<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Type;
use App\Http\Requests\TypeRequest;
use \App\Models\Teller;
use \App\Models\Window;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Illuminate\Support\Facades\DB;

class TypeController extends Controller
{
    public function index(Request $request) 
    {
        try {
            if ($request->branch_id != 0) {
                $rows = DB::table('types as tp')
                        ->select ([
                            'tp.id',
                            'tp.name',
                            'tp.indicator',
                            'tp.serving_time',
                            'tp.branch_id',
                            'b.branch_name'
                        ])
                        ->where('tp.branch_id',"=",$request->branch_id)
                        ->leftJoin('branchs as b', 'b.id','=','tp.branch_id')
                        ->orderBy('tp.created_at','desc')
                        ->get();
                    return response()->json([
                        'rows' => $rows
                    ]);
            }else {
                $rows = DB::table('types as tp')
                ->select ([
                    'tp.id',
                    'tp.name',
                    'tp.indicator',
                    'tp.serving_time',
                    'tp.branch_id',
                    'b.branch_name'
                ])
                ->leftJoin('branchs as b', 'b.id','=','tp.branch_id')
                ->orderBy('tp.created_at','desc')
                ->get();
            return response()->json([
                'rows' => $rows
            ]);
                return response()->json([ 'rows' => $rows ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') 
                ? $e->getMessage() 
                : "Something went wrong in index Type!"
            ]);
        }
    }

    public function filteredTypes(Request $request) 
    {
        try {
            $res = DB::table('types as tp')
                ->select(
                    "tp.id",
                    "tp.name",
                    "tp.indicator",
                    "tp.serving_time",
                    "ts.type_id",
                    "ts.status",

                )
                ->where("tp.branch_id", $request->branch_id)
                ->leftJoin("tellers as ts", "ts.type_id", "tp.id")
                ->distinct(); // This ensures unique rows;
    
            return response()->json([
                'rows' => $res->get()
            ]);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return response()->json([
                "message" => env('APP_DEBUG') ? $message : "Something went wrong!"
            ]);
        }
    }
    
    public function viewTypesDropdown(Request $request){
        try {
            $rows = DB::table('types')->select('id', 'name')->get(); // Only fetch necessary fields
            return response()->json([
                'rows' => $rows
            ]);
            
        } catch(\Exception $e) {
            $message = $e->getMessage();
            return response()->json([
                "message" => env('APP_DEBUG') ? $message : "Something went wrong!"
            ]);
        }
    }

    public function fetchBranch (Request $request) {
        try{ 
            $branch = DB::table('branchs')
                     ->select('id','branch_name')
                     ->get();
            
            return response()->json([
                'branch'=> $branch
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function create(TypeRequest $request) 
    {
        try {
            $row = Type::create($request->all());
            $rows = $this->getData($row->id);

            return response()->json([
                "row" => $rows,
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

            $getData = $this->getData($id);
            return response()->json([
                "row" => $getData, // Return updated record
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
            // Ensure dataHandleDelete is not null or empty
            if (empty($request->dataHandleDelete)) {
                return response()->json([
                    "message" => "No data provided for deletion."
                ], 400);
            }
    
            // Check if any tellers or windows are using this type
            $tellerCount = Teller::whereIn('Type_id', array_column($request->dataHandleDelete, 'id'))->count();
            $windowCount = Window::whereIn('type_id', array_column($request->dataHandleDelete, 'id'))->count();
    
            if ($tellerCount > 0 || $windowCount > 0) {
                return response()->json([
                    "message" => "Cannot delete type. It is still referenced in windows."
                ], 400);
            }
    
            // Get the ids from dataHandleDelete
            $ids = array_column($request->dataHandleDelete, 'id');
    
            // Perform the deletion
            DB::table('types')
                ->whereIn('id', $ids)
                ->delete();
    
            return response()->json([
                "message" => "Successfully Deleted!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : "Something went wrong!"
            ]);
        }
    }
    

    public function getData($id = null){
        
        $res = DB::table('types as tp')
                    ->select ([
                        'tp.id',
                        'tp.name',
                        'tp.indicator',
                        'tp.serving_time',
                        'tp.branch_id',
                        'b.branch_name'
                    ])
                    ->leftJoin('branchs as b', 'b.id','=','tp.branch_id')
                    ->orderBy('tp.created_at','desc');
        if($id){
            $res = $res
                ->where("tp.id",$id)
                -> first();
        }else{
            $res = $res->get();
        }
        return $res; 
    }
}