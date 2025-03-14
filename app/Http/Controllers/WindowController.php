<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\WindowRequest;
use App\Models\Window;
use Illuminate\Support\Facades\DB;

class WindowController extends Controller
{
    public function getWindows()
    {
        $windows = Window::with(['teller', 'type'])->get()->map(function ($window) {
            return [
                'id' => $window->id,
                'name' => $window->window_name,
                'teller' => $window->teller ? [
                    'value' => $window->teller->id,
                    'label' => $window->teller->teller_firstname . ' ' . $window->teller->teller_lastname
                ] : null,
                'type' => $window->type ? [
                    'value' => $window->type->id,
                    'label' => $window->type->name
                ] : null
            ];
        });

        return response()->json(['rows' => $windows]);
    }

    public function create(WindowRequest $request)
{
    $validatedData = $request->validated();
    
    // Ensure 'window_name' is set before inserting
    if (!isset($validatedData['window_name'])) {
        $validatedData['window_name'] = "Window " . (Window::count() + 1);
    }

    $window = Window::create($validatedData);

    return response()->json([
        "row" => [
            'id' => $window->id,
            'name' => $window->window_name,
            'teller' => $window->teller ? ['value' => $window->teller->id, 'label' => $window->teller->teller_firstname . ' ' . $window->teller->teller_lastname] : null,
            'type' => $window->type ? ['value' => $window->type->id, 'label' => $window->type->name] : null,
        ],
        "message" => "Window created successfully!"
    ]);
}


    public function update(WindowRequest $request)
    {
        $validatedData = $request->validated();
        $window = Window::findOrFail($request->id);
        $window->update($validatedData);

        return response()->json([
            "row" => $this->getData($window->id),
            "message" => "Window updated successfully!"
        ]);
    }

    public function delete(Request $request)
    {
        if (!$request->has('ids') || empty($request->ids)) {
            return response()->json(["message" => "No IDs provided!"], 400);
        }
    
        Window::whereIn('id', $request->ids)->delete();
        return response()->json(["message" => "Window(s) deleted successfully"]);
    }
    
//     public function getWindowFormData(Request $request)
// {
//     $windowId = $request->query('window_id'); // Get window_id from request if editing

//     // Get the currently assigned teller (if editing)
//     $assignedTeller = null;
//     if ($windowId) {
//         $assignedTeller = DB::table("tellers")
//             ->join("windows", "tellers.id", "=", "windows.teller_id")
//             ->where("windows.id", $windowId)
//             ->select("tellers.id as value", DB::raw("CONCAT(teller_firstname, ' ', teller_lastname) as label"))
//             ->first();
//     }

//     // Get tellers that are NOT assigned to any window
//     $availableTellers = DB::table("tellers")
//         ->whereNotIn('id', function ($query) {
//             $query->select('teller_id')->from('windows')->whereNotNull('teller_id');
//         })
//         ->select('id as value', DB::raw("CONCAT(teller_firstname, ' ', teller_lastname) as label"))
//         ->get();

//     // If editing and there is an assigned teller, include them in the list
//     if ($assignedTeller) {
//         $availableTellers->prepend($assignedTeller);
//     }

//     // Get window types
//     $types = DB::table("types")->select('id as value', 'name as label')->get();

//     return response()->json([
//         "types" => $types,
//         "tellers" => $availableTellers
//     ]);
// }

    public function getWindowFormData()
    {
        // Get tellers that are NOT assigned to any window
        $tellers = DB::table("tellers")
            ->whereNotIn('id', function ($query) {
                $query->select('teller_id')->from('windows')->whereNotNull('teller_id');
            })
            ->select('id as value', DB::raw("CONCAT(teller_firstname, ' ', teller_lastname) as label"))
            ->get();
    
        $types = DB::table("types")->select('id as value', 'name as label')->get();
    
        return response()->json([
            "types" => $types,
            "tellers" => $tellers
        ]);
    }
    
    // public function getWindowFormData()
    // {
    //     $types = DB::table("types")->select('id as value', 'name as label')->get();
    //     $tellers = DB::table("tellers")
    //         ->select('id as value', DB::raw("CONCAT(teller_firstname, ' ', teller_lastname) as label"))
    //         ->get();

    //     return response()->json([
    //         "types" => $types,
    //         "tellers" => $tellers
    //     ]);
    // }

    private function getData($id)
    {
        $window = Window::with(['teller', 'type'])->findOrFail($id);
        return [
            'id' => $window->id,
            'name' => $window->window_name,
            'teller' => $window->teller ? ['value' => $window->teller->id, 'label' => $window->teller->teller_firstname . ' ' . $window->teller->teller_lastname] : null,
            'type' => $window->type ? ['value' => $window->type->id, 'label' => $window->type->name] : null,
        ];
    }
}