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
           $rows = '';
           if ($request->branch_id != 0) {
              $rows = DB::table('currencies as money')
                ->select(
                    'money.id',
                    'money.branch_id',
                    'money.currency_name',
                    'money.currency_symbol',
                    'money.flag',
                    'money.buy_value',
                    'money.sell_value',
                    'b.branch_name',
                )

                ->leftJoin('branchs as b','b.id','=','money.branch_id')
                ->where('money.branch_id',$request->branch_id)
                ->orderBy('money.created_at','desc')
                ->get();

           }else {
             $rows = $this->getData();
           }
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

            $fetch = $this->getData($row->id);

            return response()->json([
                "row" => $fetch,
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
            $row = Currency::findOrFail($id)
                 ->update($request->all());

            $fetch = $this->getData( $id );
            return response()->json([
                "row" => $fetch, // Return updated record
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
    
    public function getData ($id = null) {
         try {
           $res = DB::table('currencies as money')
                ->select(
                    'money.id',
                    'money.currency_name',
                    'money.currency_symbol',
                    'money.flag',
                    'money.buy_value',
                    'money.sell_value',
                    'money.branch_id',
                    'b.branch_name',
                )
                ->leftJoin('branchs as b', 'b.id', '=', 'money.branch_id');

            if ($id) {
                $res = $res->where('money.id', $id)
                            ->orderBy('money.created_at','desc')
                            ->first();
            }else {
                $res = $res->orderBy('money.created_at','desc')
                          ->get();
            }
            return $res;
        }  catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        } 
    }
}