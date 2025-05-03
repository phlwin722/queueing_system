<?php

namespace App\Http\Controllers;


use App\Http\Requests\BranchRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Models\Branch;


class BranchController extends Controller
{
    // PSGC API
    //https://psgc.cloud/docs/psgc-regions-api-documentation/
    protected $baseUrl = 'https://psgc.cloud/api';
    
    public function getRegions () {
        try {
            $reponse = Http::get("{$this->baseUrl}/regions/");
            return response()->json([
                'regions' => $reponse->json()
            ]);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return response()->json([
                'message' => $message
            ]);
        }
    }

    public function getProvinces(Request $request)
    {
        try {
            $response = Http::get("{$this->baseUrl}/regions/$request->regionCode/provinces/");
            return response()->json([
                'provinces' => $response->json()
            ]);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return response()->json([
                'message' => $message
            ]);
        }
    }

    public function getCities(Request $request)
    {
        try {
            if ($request->provinceCode == 'National Capital Region (NCR)') {
                $response = Http::get("{$this->baseUrl}/regions/$request->provinceCode/cities-municipalities/");
            }else {
                $response = Http::get("{$this->baseUrl}/provinces/$request->provinceCode/cities-municipalities/");
            }
            return response()->json([
                'cities' => $response->json()
            ]);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return response()->json([
                'message' => $message
            ]);
        }
    }

    public function getBarangays(Request $request)
    {
        try {
            $response = Http::get("{$this->baseUrl}/cities-municipalities/$request->cityCode/barangays/");
            return response()->json([
                'barangays' => $response->json()
            ]);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return response()->json([
                'message' => $message
            ]);
        }
    }

    public function index(Request $request){

        try{
            $rows =  $this->getData();
            
            return response()->json([
                'rows' => $rows
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

    public function create (BranchRequest $request) {
        $branchID = DB::table('branchs')->insertGetId([
            'branch_name' => $request->branch_name,
            'manager_id' => $request->manager_name,
            'region' => $request->region,
            'province' =>  $request->province,
            'city' => $request->city,
            'Barangay' => $request->Barangay,
            'address' => $request->address,
            'status' => $request->status,
            'opening_date' => $request->opening_date,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // create service offer on branch
        DB::table('types')
            ->insert([
                [
                    'name' => 'Foreign Exchange',
                    'indicator' => 'FE',
                    'serving_time' => '10',
                    'branch_id' => $branchID
                ],
                [
                    'name' => 'Online Appointment',
                    'indicator' => 'OA',
                    'serving_time' => '10',
                    'branch_id' => $branchID
                ],
                [
                    'name' => 'Manual Queueing',
                    'indicator' => 'MQ',
                    'serving_time' => '0',
                    'branch_id' => $branchID,
                ]
          ]);


        // update the managers table on branch
        DB::table('managers')
            ->where('id',$request->manager_name)
            ->update(['branch_id' => $branchID]);

        // Fetch the newly inserted teller and join with the types table
        $newBranch = DB::table('branchs as b')
        ->select(
            "b.id",
            "b.branch_name",
            "b.region",
            "b.province",
            "b.city",
            "b.Barangay",
            "b.address",
            "b.status",
            "b.opening_date",
            DB::raw("CONCAT(m.manager_firstname, ' ', m.manager_lastname) as manager_name"),
            "m.branch_id",
         )
        ->leftJoin("managers as m","m.id", "=", "b.manager_id")
        ->where("b.id", $branchID)
        ->first();

        return response()->json([
            'success' => true,
            'message' => 'Branch added successfully!',
            'row' => $newBranch
        ]);
    }
    
    // fetch manager
    public function fetchManager (Request $request) {
        try {
            $manager = DB::table('managers')
                ->get();
            
                // Check if data exists
            if ($manager->isEmpty()) {
                return response()->json([
                    'message' => 'No managers found.'
                ], 404); // 404 Not Found
            }
            
            return response()->json([
                'manager' => $manager
            ]);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return response()->json([
                'message' => $message
            ]);
        }
    }

    public function update(BranchRequest $request)
    {
        try {
            $manager = DB::table('branchs')
                        ->where('id',$request->id)
                        ->first();
    
            // Check if teller exists
            if (!$manager) {
                return response()->json([
                    "message" => "Branch not found!"    
                ], 404);
            }

            // check on manager if branch_id in changes
           $branchs = DB::table('branchs')
                ->where('id',$request->id)
                ->first();
            if ($branchs->manager_id != $request->manager_id){
                DB::table('managers')
                    ->where('id',$branchs->manager_id)
                    ->update([
                        'branch_id' => null
                    ]);
            }
    
            DB::table('branchs')
                ->where('id',$request->id)
                ->update([
                    'branch_name' => $request->branch_name,
                    'manager_id' => $request->manager_id,
                    'region' => $request->region,
                    'province' => $request->province,
                    'city' => $request->city,
                    'Barangay' => $request->Barangay,
                    'address' => $request->address,
                    'status' => $request->status,
                    'opening_date' => $request->opening_date
                ]);
    
            // Fetch updated data
            $row = $this->getData($manager->id);
    
            return response()->json([
                "row" => $row,
                "message" => "Branch Successfully Updated!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : "An error occurred"
            ]);
        }
    }

    public function delete(Request $request){
        try {
            // check if existing the id on manager
            $hasExistingManager = DB::table('managers')
                            ->whereIn('branch_id', $request->ids)
                            ->exists();
            if ($hasExistingManager) {
                return response()->json([
                    "message" => "Cannot disable branch. One or more managers are assigned to this branch."
                ],400);
            }

            // check if existing the id on teller
            $hasExistingTeller = DB::table('tellers')
                                ->whereIn('branch_id', $request->ids)
                                ->exists();
            if ($hasExistingTeller) {
                return response()->json([
                    "message" => "Cannot disable branch. One or more teller are assigned to this branch."
                ],400);
            }

            // check if existing the id on teller
            $hasExistingTypes = DB::table('types')
                                ->whereIn('branch_id', $request->ids)
                                ->exists();
            if ($hasExistingTypes) {
                return response()->json([
                    "message" => "Cannot disable branch. One or more service types are assigned to this branch."
                ],400);
            }

            // check if existing the id on teller
            $hasExistingWindow = DB::table('windows')
                                ->whereIn('branch_id', $request->ids)
                                ->exists();
            if ($hasExistingWindow) {
                return response()->json([
                    "message" => "Cannot disable branch. One or more teller are assigned to this branch."
                ],400);
            }

            // Delete the branches
            $delete = DB::table('branchs')
                ->whereIn('id', $request->ids)
                ->delete();

            if ($delete) {
                return response()->json([
                    "message" => "Successfully disable!"
                ]);
            }
        } catch (\Exception $e) {
        return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : "Something went wrong!"
            ]);
        }
    }

    public function getData($id = null){
        $res = DB::table('branchs as b')
            ->select(
                "b.id",
                "b.branch_name",
                "b.manager_id",
                "b.region",
                "b.province",
                "b.city",
                "b.Barangay",
                "b.address", 
                "b.status",
                "b.opening_date",
                DB::raw("CONCAT(m.manager_firstname, ' ', m.manager_lastname) as manager_name")
            )
            ->leftJoin("managers as m","m.id", "=", "b.manager_id")
            ->orderBy('b.created_at', 'desc'); // Ordering by created_at in descending order
    
        if ($id) {
            $res = $res->where("b.id", $id)->first();
        } else {
            $res = $res->get();
        }
        
        return $res;
    }
}