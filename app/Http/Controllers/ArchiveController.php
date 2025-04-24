<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArchiveController extends Controller
{
    public function index (Request $request) {
        try {
            if ($request->branch_id == 0) {
                $row = $this->getData(null, $request->personel);
                return response()->json([
                    'rows' => $row 
                ]);
            }else {
                $row = $this->getData($request->branch_id, $request->personel);
                return response()->json([
                    'rows' => $row 
                ]);
            }
        }   catch(\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getData ($branch_id = null, $personel = null) {
        try {
            $res = DB::table('archives as arc')
                    ->select(
                        'arc.id',
                        DB::raw("CONCAT(arc.First_name,' ',arc.Lastname) as fullname"),
                        'arc.Username as username',
                        'arc.Branch_id',
                        'arc.created_at as archived_at',
                        'b.branch_name'
                    )

                    ->leftJoin('branchs as b', 'b.id', '=', 'arc.branch_id')
                    ->orderBy('arc.created_at','desc');
                    
                    if ($branch_id) {
                        $res = $res->where('Branch_id', $branch_id)->where('types', $personel)->get();    
                    }else {
                        $res = $res->where('types',$personel)->get();
                    }
                return $res;

        } catch (\Exception $e) {
            return response()->json([
               'error' => $e->getMessage() 
            ]);
        }
    }

    public function restore (Request $request) {
        try {   
            $data = DB::table('archives')
                    ->whereIn('id', $request->ids)
                    ->get();
            
            // check if no data was found
            if ($data->isEmpty()) {
                return response()->json([
                    'error' => 'No id found'
                ],400);
            }

            // loop throught each manager and check if type if personel or manager
            foreach($data as $val) {
                  // check if branch id is available
                  $check = DB::table('branchs')
                    ->where('id', $val->Branch_id)
                    ->exists();
              
                    // if not exist
                if (!$check) {
                    return response()->json([
                        'error' => "Sorry we inform to you the branch of {$val->Firstname} {$val->Lastname} is no longer available" 
                    ],400);
                }
                
                if ($val->types == '1') {
                    // personel
                    DB::table('tellers')
                        ->insert([
                            'teller_firstname' => $val->First_name,
                            'teller_lastname' => $val->Lastname,
                            'teller_username' => $val->Username,
                            'teller_password' => $val->Password,
                            'branch_id' => $val->Branch_id,
                            'Image' => $val->Image,
                        ]);
                } else if ($val->types == '0') {
                    // manager
                   $id = DB::table('managers')
                   ->insertGetId([
                       'manager_firstname' => $val->First_name,
                       'manager_lastname' => $val->Lastname,
                       'manager_username' => $val->Username,
                       'manager_status' => 'Offline',
                       'manager_password' => $val->Password,
                       'branch_id' => $val->Branch_id,
                       'Image' => $val->Image,
                   ]);

                   DB::table('branchs')
                    ->where('id',$val->Branch_id)
                    ->update([
                        'manager_id' => $id
                    ]);
                }
            }

            // proceed delete
            DB::table('archives')
            ->whereIn('id', $request->ids)
            ->delete();
        
            return response()->json([
                "message" => "Successfully Restored!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }
}
