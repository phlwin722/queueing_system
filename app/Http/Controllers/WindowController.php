<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\WindowRequest;
use App\Models\Window;
use App\Models\Type;   
use App\Models\Teller;
use App\Models\ResetSetting;
use App\Models\WindowArchive;
use Hamcrest\Arrays\IsArray;
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
            $tller_id = DB::table('windows')
                        -> where ('teller_id',$request->teller_id)
                        ->first();
            if ($tller_id) {
                return response()->json([
                    'message' => 'The personel is already assigned'
                ],400);
            }

            $res = Window::create($request->all());
            $row = $this->getData($res ->id);
            
            $type_id = $request->type_id;
            $teller_id = $request->teller_id;

            DB::table('tellers')
                ->where('id', $teller_id)
                ->update(['type_id' => $type_id]);

            return response()->json([
                "row"=> $row,
                "message"=>"Successfully Added!"
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
            $teller_id = DB::table('windows')
            // Look for a row where the teller_id is the same as the one provided in the request
            ->where('teller_id', $request->teller_id)
            
            // Exclude the current window being updated by comparing IDs. This prevents the check from affecting the window being updated itself.
            ->where('id', '!=', $request->id) 
            
            // Use 'exists()' to check if there is any matching row. If a row is found, it returns true; otherwise, it returns false.
            ->exists();  
        
        // If the teller_id is already assigned to another window (excluding the current window), return an error response
        if ($teller_id) {
            // Return a JSON response with an error message if a conflict is found.
            return response()->json([
                'message' => 'The teller is already assigned to another window.' // Error message for conflicting teller_id
            ], 400); // HTTP 400 status code for bad request
        }
        DB::table('tellers')
            ->where('id', $request->old_teller_id)
            ->update(['type_id' => null]); // Reset the type_id of the old teller to null
        
            // Find the student
            $window = Window::find($request->id);
    
            // Check if student exists
            if (!$window) {
                return response()->json([
                    "message" => "Window not found!"
                ], 404);
            }
    
            $window->update($request->all());
    
            // Fetch updated data
            $row = $this->getData($window->id);

            $type_id = $request->type_id;
            $teller_id = $request->teller_id;

            DB::table('tellers')
                ->where('id', $teller_id)
                ->update(['type_id' => $type_id]);
        
    
            return response()->json([
                "row" => $row,
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
            $windows = $request->deleteWindow; // array of window data

            // Get the ids for deletion
            $ids = array_column($windows, 'id');

            // Delete windows first
            

            // Archive each window
            foreach ($windows as $win) {
                WindowArchive::create([
                    'window_id'   => $win['id'],
                    'window_name' => $win['window_name'],
                    'type_id'     => $win['type_id'],
                    'teller_id'   => $win['teller_id'],
                    'branch_id'   => $win['branch_id'],
                    'archived_at' => now(),
                ]);

                DB::table('tellers')
                    ->where('id', $win['teller_id'])
                    ->update(['type_id' => null]);
            }
            DB::table('windows')->whereIn('id', $ids)->delete();
            return response()->json([
                "message" => "Successfully Deleted!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error deleting window(s)",
                "error" => $e->getMessage()
            ], 500);
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


    public function getWindows(Request $request){
            if ($request->branch_id == null) {
            $res = DB::table('windows as wd')
                ->select(
                    "wd.id",
                    "wd.window_name",
                    "tp.name as type_name",
                    "tp.id as type_id",
                    "wd.branch_id", 
                    "wd.status",
                    DB::raw("CONCAT(ts.teller_firstname, ' ', ts.teller_lastname) teller_name"),
                    "ts.id as teller_id",
                    'b.branch_name'
                )
                ->leftJoin("types as tp", "tp.id", "wd.type_id")
                ->leftJoin('branchs as b', 'b.id', '=','wd.branch_id')
                ->leftJoin("tellers as ts", "ts.id", "wd.teller_id")
                ->orderBy('wd.created_at', 'desc')
                ->get();
        
            return response()->json([
                'rows' => $res
            ]);
        }else {
            $res = DB::table('windows as wd')
                ->select(
                    "wd.id",
                    "wd.window_name",
                    "tp.name as type_name",
                    "tp.id as type_id",
                    "wd.branch_id", 
                    "wd.status",
                    DB::raw("CONCAT(ts.teller_firstname, ' ', ts.teller_lastname) teller_name"),
                    "ts.id as teller_id",
                    'b.branch_name'
                )
                ->leftJoin("types as tp", "tp.id", "wd.type_id")
                ->leftJoin('branchs as b', 'b.id', '=','wd.branch_id')
                ->leftJoin("tellers as ts", "ts.id", "wd.teller_id")
                ->where('wd.branch_id',$request->branch_id)
                ->orderBy('wd.created_at', 'desc')
                ->get();
        
            return response()->json([
                'rows' => $res
            ]);
        }
    }

    public function viewTypesDropdown (Request $request) {
        try {
            $rows = DB::table('types as tp')
                    ->select([
                        'tp.id',
                        'tp.name',
                        'tp.branch_id'
                    ])
                    ->where('tp.branch_id',$request->branch_id)
                    ->get();
            return response()->json([
                'rows' => $rows
            ]);
        }  catch (\Exception $e) {
            return response()->json([
               'message' => $e->getMessage() 
            ]);
        }
    }

    public function viewtellersDropdown (Request $request) {
        try {
            $rows = DB::table('tellers as t')
                    ->select([
                        't.id as id',
                        DB::raw('CONCAT(t.teller_firstname, " ", t.teller_lastname) as name'),
                        't.branch_id',
                        't.type_id',
                        't.type_ids_selected'
                    ])
                    ->where('t.branch_id',$request->branch_id)
                    ->whereRaw('JSON_CONTAINS(type_ids_selected, ?)',[json_encode(strval($request->type_ids_selected))]) // Ensure ID is treated as a string
                    ->get();
            return response()->json([
                'rows' => $rows
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }
    

    public function getData($id = null){
        
        $res = DB::table('windows as wd')
            ->select(
                "wd.id",
                "wd.window_name",
                "wd.branch_id",
                "wd.type_id",
                "wd.teller_id",
                "tp.name as type_name",
                DB::raw("CONCAT(ts.teller_firstname, ' ', ts.teller_lastname) as teller_name"),
                "b.branch_name",
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
            ->leftJoin(
                "branchs as b",
                "b.id",
                "=",
                "wd.branch_id",
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

    // **🔄 Manual Reset**
    public function resetTellers()
    {
        try {
            Window::query()->update(['teller_id' => null]);
            Teller::query()->update(['type_id' => null]);
            return response()->json(['message' => 'All tellers reset successfully!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to reset tellers.'], 500);
        }
    }

    // **🔄 Auto Reset with Archive**
    public function resetWindows(Request $request){

    try {
        $branch_id = $request->branch_id;
       if ($branch_id != 0) {
        DB::transaction(function () use ($branch_id) {
            // Get only windows that have an assigned teller
            $windows = Window::where('branch_id', $branch_id)->whereNotNull('teller_id')->get();

            if ($windows->isEmpty()) {
                Log::info("⚠ No assigned tellers to reset.");
                throw new \Exception("No assigned tellers to reset.");
            }

            foreach ($windows as $window) {
                WindowArchive::create([
                    'window_id'   => $window->id,
                    'window_name' => $window->window_name,
                    'type_id'     => $window->type_id,
                    'teller_id'   => $window->teller_id,
                    'branch_id' => $window->branch_id,
                    'archived_at' => now(),
                ]);
            }

            // Reset only affected rows
            Window::where('branch_id', $branch_id)->whereNotNull('teller_id')->update(['teller_id' => null]);
            Teller::where('branch_id', $branch_id)->whereIn('id', $windows->pluck('teller_id'))->update(['type_id' => null]);

            Log::info("✅ Window Reset successfully.");
        });

        return response()->json(['message' => 'Windows with assigned tellers reset successfully']);
       }else {
        DB::transaction(function () {
            // Get only windows that have an assigned teller
            $windows = Window::whereNotNull('teller_id')->get();

            if ($windows->isEmpty()) {
                Log::info("⚠ No assigned tellers to reset.");
                throw new \Exception("No assigned tellers to reset.");
            }

            foreach ($windows as $window) {
                WindowArchive::create([
                    'window_id'   => $window->id,
                    'window_name' => $window->window_name,
                    'type_id'     => $window->type_id,
                    'teller_id'   => $window->teller_id,
                    'branch_id'    => $window->branch_id,
                    'archived_at' => now(),
                ]);
            }

            // Reset only affected rows
            Window::whereNotNull('teller_id')->update(['teller_id' => null]);
            Teller::whereIn('id', $windows->pluck('teller_id'))->update(['type_id' => null]);

            Log::info("✅ Window Reset successfully.");
        });

        return response()->json(['message' => 'Windows with assigned tellers reset successfully']);
       }
    } catch (\Exception $e) {
        Log::error("❌ Error resetting windows: " . $e->getMessage());
        return response()->json(['message' => $e->getMessage()], 500);
    }
}


    //For Automatic Call the Reset Settings 
    public function autoResetWindows(Request $request)
{
    $settings = DB::table('reset_settings')->first();

    if (!$settings) {
        Log::info("❌ No reset settings found.");
        return;
    }

    $currentTime = now()->format('H:i');
    $currentDay = now()->format('l'); // Example: "Monday"
    $currentDate = now()->format('Y-m-d');

    $shouldReset = false;

    if ($settings->reset_type === "Daily" && $currentTime === $settings->reset_time) {
        $shouldReset = true;
    } elseif ($settings->reset_type === "Weekly" && $currentTime === $settings->reset_time && $currentDay === $settings->reset_day) {
        $shouldReset = true;
    } elseif ($settings->reset_type === "Monthly" && $currentTime === $settings->reset_time && $currentDate === $settings->reset_date) {
        $shouldReset = true;
    }

    if ($shouldReset) {
        Log::info("✅ Reset triggered automatically.");
        $this->resetWindows($request);
    } else {
        Log::info("⏳ Not yet time for reset.");
    }
}

}

