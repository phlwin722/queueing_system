<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\QrCodeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MailController;
use App\Http\Controllers\TellercaterController;
use App\Http\Controllers\Waiting_timeController;
use App\Http\Controllers\TellerController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\WindowController;
use App\Http\Controllers\ResetSettingController;
use App\Http\Controllers\WindowArchiveController;
use App\Http\Controllers\CurrencyController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::post('/admin/validate',[AdminController::class,'adminValidate'] );
Route::post('/admin/Information',[AdminController::class,"index"]);
Route::post('/admin/updateInformation',[AdminController::class,"updateqInformation"]);
Route::post('/admin/updatePassword',[AdminController::class,"updatePassword"]);
Route::post('/createAdmin',[AdminController::class,'createAdmin'] );

Route::post('/customer-join',[QueueController::class, 'joinQueue']);
Route::post('/customer-list',[QueueController::class, 'getQueueList']);
Route::post('/customer-leave',[QueueController::class, 'leaveQueue']);
Route::post('/customer-fetch',[QueueController::class, 'customerData']);
Route::post('/update-teller_id',[QueueController::class, 'updateTellerId']);

Route::post('/admin/queue-list', [QueueController::class, 'getCQueueList']);
Route::post('/admin/cater', [QueueController::class, 'caterCustomer']);
Route::post('/admin/cancel', [QueueController::class, 'cancelCustomer']);
Route::post('/admin/finish', [QueueController::class, 'finishCustomer']);
Route::post('/admin/start-wait', [QueueController::class, 'startWait']);
Route::post('/send-fetchInfo',[QueueController::class,'fetchData']);
Route::post('/admin/queue-logs', [QueueController::class, 'queueLogs']);
Route::post('/admin/reports', [QueueController::class, 'fetchReports']);
Route::post('/resetQueue', [QueueController::class, 'resetTodayQueueNumbers']);
Route::post('/waitCustomer', [QueueController::class, 'WaitCustomer']);
Route::post('/customer-check-waiting', [QueueController::class, 'checkWaitingCustomer']);
Route::post('/waitCustomerReset', [QueueController::class, 'WaitingCustomerReset']);

Route::post('/admin/waiting_Time',[Waiting_timeController::class,'store']);
Route::post('/admin/waiting_Time-fetch',[Waiting_timeController::class,'index']);
Route::post('/admin/waiting_Time-update',[Waiting_timeController::class,'update']);
Route::post('/upload-image', [AdminController::class, 'uploadImage']);

// adminside
// Teller Routes
Route::post('/tellers/index', [TellerController::class, 'index']);
Route::post('/tellers/create', [TellerController::class, 'create']);
Route::post('/tellers/update', [TellerController::class, 'update']);
Route::post('/tellers/delete', [TellerController::class, 'delete']);
Route::post('/teller/typeid-value',[TellerController::class,'valueTypeid']);
Route::post('/teller/validate',[TellerController::class, 'validationLoginTeller']);
Route::post('/tellers/dropdown', [TellerController::class, 'viewTellerDropdown']);
Route::post('/teller/queue-list', [TellercaterController::class, 'getTellerQueueList']);
Route::post('/teller/cater', [TellercaterController::class, 'caterTellerCustomer']);
Route::post('/teller/cancel', [TellercaterController::class, 'cancelCustomer']);
Route::post('/teller/finish', [TellercaterController::class, 'finishCustomer']);

Route::post('/teller/waiting_Time-fetch',[Waiting_timeController::class,'index']);
Route::post('/teller/image',[TellerController::class,'fetchImage']);
Route::post('/teller/image-teller',[TellerController::class,'fetchImageTeller']);
Route::post('/teller/image-fetch-csdashboard',[TellerController::class, 'fetchImageTellerCsDashboaard']);


// Type Routes
Route::post('/types/index', [TypeController::class, 'index']);
Route::post('/types/create', [TypeController::class, 'create']);
Route::post('/types/update', [TypeController::class, 'update']);
Route::post('/types/delete', [TypeController::class, 'delete']);
Route::post('/types/dropdown', [TypeController::class, 'viewTypesDropdown']);


// Window Routes
Route::post('/windows/index', [WindowController::class, 'index']);
Route::post('/windows/create', [WindowController::class, 'create']);
Route::post('/windows/update', [WindowController::class, 'update']);
Route::post('/windows/delete', [WindowController::class, 'delete']);

//Reset-Manual button
Route::post('/windows/getWindows', [WindowController::class, 'getWindows']);
//Manual Reset Button Window Logs
Route::post('/windows/reset-tellers', [WindowController::class, 'resetTellers']);
//window-Logs Get Data Table
Route::post('/admin/window-logs', [WindowArchiveController::class, 'getWindowLogs']);

//Automatic Reset 
Route::post('/windows/reset-windows', [WindowController::class, 'resetWindows']);

// Currency Routes
Route::post('/currency/showData', [CurrencyController::class, 'showData']);
Route::post('/currency/create', [CurrencyController::class, 'create']);
Route::post('/currency/update', [CurrencyController::class, 'update']);
Route::post('/currency/delete', [CurrencyController::class, 'delete']);


//Reset-Window
Route::post('/waiting_Time-fetch', [ResetWindowSettingController::class, 'fetch']);
Route::post('/waiting_Time', [ResetWindowSettingController::class, 'store']);


//Reset-Settings Automatic 
Route::post ('/windows/fetch-reset-settings', [ResetSettingController::class, 'fetchSettings']);
Route::post('/windows/reset-settings', [ResetSettingController::class, 'saveSettings']);

// adminside
Route::post('/generate-qr', [QrCodeController::class, 'generateQrCode']);
Route::post('/scan-qr', [QrCodeController::class, 'scanQrCode']);

Route::post('/send-email', [MailController::class, 'sendEmail']);

//Waiting-Time 
Route::post('/waiting_Time',[Waiting_timeController::class,'store']); 
Route::post('/waiting_Time-fetch',[Waiting_timeController::class,'index']);
Route::post('/waiting_Time-update',[Waiting_timeController::class,'update']);
