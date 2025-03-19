<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\WindowRequest;
use App\Models\Window;
use App\Models\Type;   
use App\Models\Teller;
use App\Models\WindowArchive;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WindowController extends Controller
{
    // public function getWindows()
    // {
    //     $windows = $this->getData();

    //     return response()->json([
    //         'rows' => $windows
    //     ]);
    // }

    public function create(WindowRequest $request)
    {
        
        try{
            $res = Window::create($request->all());
            $row = $this->getData($res ->id);
            return response()->json([
                "row"=> $row,
                "message"=>"Window added successfully!"
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

   

 

    public function update(WindowRequest $request){
        try {
            // Find the student
            $window = Window::find($request->id);
    
            // Check if student exists
            if (!$window) {
                return response()->json([
                    "message" => "Window not found!"
                ], 404);
            }
    
            // Update the student
            $window->update($request->all());
    
            // Fetch updated data
            $row = $this->getData($window->id);
    
            return response()->json([
                "row" => $row,
                "message" => "Window Successfully Updated!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : "Something went wrong!"
            ]);
        }
    }



    public function delete(Request $request){
        try{
            $row  = Window::find($request->id);
            
            if(!$row){
                return response()->json([
                    "message"=>"window not found!"
                ],404);
            }else{
                Window::destroy($request->id);
                return response()->json([
                "message"=>"Window Succesfully Deleted!"
    
            ]);
            }
            
        }catch(\Exception $e){
            $message = $e->getMessage();
            return response()->json([
                "message"=>env('APP_DEBUG')
                    ? $message
                    : "Something went wrong!"
            ]);
        }
        
    }
    


    // public function getWindowFormData()
    // {
    //     // Get tellers that are NOT assigned to any window
    //     $tellers = DB::table("tellers")
    //         ->whereNotIn('id', function ($query) {
    //             $query->select('teller_id')->from('windows')->whereNotNull('teller_id');
    //         })
    //         ->select('id as value', DB::raw("CONCAT(teller_firstname, ' ', teller_lastname) as label"))
    //         ->get();
    
    //     $types = DB::table("types")->select('id as value', 'name as label')->get();
    
    //     return response()->json([
    //         "types" => $types,
    //         "tellers" => $tellers
    //     ]);
    // }


    public function getWindows($id = null){
        $res = DB::table('windows as wd')
            ->select(
                "wd.id",
                "wd.window_name",
                "tp.name as type_id",
                DB::raw("CONCAT(ts.teller_firstname, ' ', ts.teller_lastname) as teller_id"),
                "ts.id as pId"
            )
            ->leftJoin("types as tp", "tp.id", "wd.type_id")
            ->leftJoin("tellers as ts", "ts.id", "wd.teller_id")
            ->orderBy('wd.created_at', 'desc');
    
        if ($id) {
            $res = $res->where("wd.id", $id)->first();
        } else {
            $res = $res->get();
        }
    
        return response()->json([
            'rows' => $res
        ]);
    }
    

    public function getData($id = null){
        
        $res = DB::table('windows as wd')
            ->select(
                "wd.id",
                "wd.window_name",
                "tp.name as type_id",
                DB::raw("CONCAT(ts.teller_firstname, ' ', ts.teller_lastname) as teller_id"),
                "ts.id as pId"
            )
            ->leftJoin(
                "types as tp",
                "tp.id",
                "wd.type_id"
            )
            ->leftJoin(
                "tellers as ts",
                "ts.id",
                "wd.teller_id"
            )
            ->orderBy('wd.created_at', 'desc');
        

        if($id){
            $res = $res
                ->where("wd.id",$id)
                -> first();
        }else{
            $res = $res->get();
        }
        return $res;
    
        
    }

    public function resetTellers()
    {
        try {
            Window::query()->update(['teller_id' => null]);
    return response()->json(['message' => 'All tellers reset successfully!']);
} catch (\Exception $e) {
            return response()->json(['message' => 'Failed to reset tellers.'], 500);
        }
    }

    public function resetWindows()
{
    Log::info("Reset Windows Function Triggered");
    try {
        $windows = Window::all();

        foreach ($windows as $window) {
            $typeName = Type::find($window->type_id)->name ?? 'N/A';
            $tellerName = Teller::find($window->teller_id)->name ?? 'N/A';

            WindowArchive::create([
                'window_name' => $window->window_name,
                'type_name'   => $typeName,
                'teller_name' => $tellerName,
                'archived_at' => now(),
            ]);
        }

        Window::query()->update(['teller_id' => null]);

        return response()->json(['message' => 'Windows reset successfully']);
    } catch (\Exception $e) {
        Log::error("Error resetting windows: " . $e->getMessage());
        return response()->json(['message' => 'Reset failed'], 500);
    }
}
}