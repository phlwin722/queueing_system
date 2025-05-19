<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Currency;
use App\Http\Requests\CurrencyRequest;
use Illuminate\Support\Facades\DB;
use App\Events\CustomerDashBoardQueuelist;

use function PHPUnit\Framework\isEmpty;

class CurrencyController extends Controller
{
    public function showData(Request $request)
    {
        try {
            $rows = '';


            $rows = $this->getData();
            return response()->json(['rows' => $rows]);
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
            $checking = DB::table('currencies')
                ->where('currency_name', $request->currency_name)
                ->first();

            if ($checking) {
                return response()->json([
                    "message" => "This Currency Name already exists!"
                ], 400);
            }
            $row = Currency::create($request->all());

            $fetch = $this->getData($row->id);
            $newCurrency = DB::table('currencies')->where('id', $row)->first();
            broadcast(new CustomerDashBoardQueuelist($newCurrency));
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

            $fetch = $this->getData($id);
            $newCurrency = DB::table('currencies')->where('id', $row)->first();
            broadcast(new CustomerDashBoardQueuelist($newCurrency));
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
            broadcast(new CustomerDashBoardQueueList(['action' => 'deleted', 'id' => $request->ids]));
            return response()->json([
                "message" => "Successfully Deleted!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG') ? $e->getMessage() : "Something went wrong!"
            ], 500);
        }
    }

    public function getData($id = null)
    {
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
                    ->orderBy('money.created_at', 'asc')
                    ->first();
            } else {
                $res = $res->orderBy('money.created_at', 'asc')
                    ->get();
            }
            return $res;
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function generateDefaultCurrencies()
    {
        try {
            $currency = DB::table('currencies')
                ->get();

            if ($currency->isEmpty()) {
                $currencies = [
                    ['currency_name' => 'US Dollar',     'currency_symbol' => '$',   'flag' => 'fi-us', 'buy_value' => 55.20, 'sell_value' => 56.00],
                    ['currency_name' => 'Euro',                     'currency_symbol' => '€',   'flag' => 'fi-eu', 'buy_value' => 59.00, 'sell_value' => 60.20],
                    ['currency_name' => 'British Pound',   'currency_symbol' => '£',   'flag' => 'fi-gb', 'buy_value' => 68.00, 'sell_value' => 69.50],
                    ['currency_name' => 'Japanese Yen',             'currency_symbol' => '¥',   'flag' => 'fi-jp', 'buy_value' => 0.35,  'sell_value' => 0.38],
                    ['currency_name' => 'Australian Dollar',        'currency_symbol' => 'A$',  'flag' => 'fi-au', 'buy_value' => 36.50, 'sell_value' => 37.30],
                    ['currency_name' => 'Canadian Dollar',          'currency_symbol' => 'C$',  'flag' => 'fi-ca', 'buy_value' => 40.00, 'sell_value' => 41.20],
                    ['currency_name' => 'Swiss Franc',              'currency_symbol' => 'CHF', 'flag' => 'fi-ch', 'buy_value' => 62.00, 'sell_value' => 63.50],
                    ['currency_name' => 'Chinese Yuan',             'currency_symbol' => '¥',   'flag' => 'fi-cn', 'buy_value' => 7.60,  'sell_value' => 7.90],
                    ['currency_name' => 'Singapore Dollar',         'currency_symbol' => 'S$',  'flag' => 'fi-sg', 'buy_value' => 41.20, 'sell_value' => 42.00],
                    ['currency_name' => 'Hong Kong Dollar',         'currency_symbol' => 'HK$', 'flag' => 'fi-hk', 'buy_value' => 7.00,  'sell_value' => 7.30],
                ];

                foreach ($currencies as &$currency) {
                    $currency['created_at'] = now();
                    $currency['updated_at'] = now();
                }

                foreach ($currencies as $currency) {
                    Currency::create($currency);
                }

                $newCurrency = DB::table('currencies')->get();
                broadcast(new CustomerDashBoardQueuelist($newCurrency));
                return response()->json([
                    "message" => "Default currencies generated successfully!"
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                "message" => env('APP_DEBUG')
                    ? $e->getMessage()
                    : "Something went wrong in generating default currencies!"
            ]);
        }
    }
}
