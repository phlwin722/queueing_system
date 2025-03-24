<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\WindowRequest;
use App\Models\Window;
use App\Models\Type;   
use App\Models\Teller;
use App\Models\ResetSetting;
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
            
            $type_id = $request->type_id;
            $teller_id = $request->teller_id;

            DB::table('tellers')
                ->where('id', $teller_id)
                ->update(['type_id' => $type_id]);
        


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

            $type_id = $request->type_id;
            $teller_id = $request->teller_id;

            DB::table('tellers')
                ->where('id', $teller_id)
                ->update(['type_id' => $type_id]);
        
    
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
    public function resetWindows()
{
    Log::info("Reset Windows Function Triggered");

    try {
        DB::transaction(function () {
            $windows = Window::all();

            foreach ($windows as $window) {
                WindowArchive::create([
                    'window_id'   => $window->id,
                    'window_name' => $window->window_name,
                    'type_id'     => $window->type_id,
                    'teller_id'   => $window->teller_id,
                    'archived_at' => now(),
                ]);
            }

            // Reset tellers after archiving
            Window::query()->update(['teller_id' => null]);
            Teller::query()->update(['type_id' => null]);
        });

        Log::info("✅ Windows reset successfully.");
        return response()->json(['message' => 'Windows reset successfully']);
    } catch (\Exception $e) {
        Log::error("❌ Error resetting windows: " . $e->getMessage());
        return response()->json(['message' => 'Reset failed'], 500);
    }
}

    //For Automatic Call the Reset Settings 
    public function autoResetWindows()
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
        $this->resetWindows();
    } else {
        Log::info("⏳ Not yet time for reset.");
    }
}

}

