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
use App\Http\Controllers\ResetWindowSettingController;
use App\Http\Controllers\WindowArchiveController;

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

Route::post('/admin/queue-list', [QueueController::class, 'getCQueueList']);
Route::post('/admin/cater', [QueueController::class, 'caterCustomer']);
Route::post('/admin/cancel', [QueueController::class, 'cancelCustomer']);
Route::post('/admin/finish', [QueueController::class, 'finishCustomer']);
Route::post('/admin/start-wait', [QueueController::class, 'startWait']);
Route::post('/send-fetchInfo',[QueueController::class,'fetchData']);
Route::post('/admin/queue-logs', [QueueController::class, 'queueLogs']);
Route::post('/admin/reports', [QueueController::class, 'fetchReports']);
Route::post('/resetQueue', [QueueController::class, 'resetTodayQueueNumbers']);
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
Route::post('/teller/validate',[TellerController::class, 'validationLoginTeller']);
Route::post('/tellers/dropdown', [TellerController::class, 'viewTellerDropdown']);
Route::post('/teller/queue-list', [TellercaterController::class, 'getTellerQueueList']);
Route::post('/teller/cater', [TellercaterController::class, 'caterTellerCustomer']);
Route::post('/teller/cancel', [TellercaterController::class, 'cancelCustomer']);
Route::post('/teller/finish', [TellercaterController::class, 'finishCustomer']);
Route::post('/teller/waiting_Time-fetch',[Waiting_timeController::class,'index']);

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
Route::post('/windows/getWindows', [WindowController::class, 'getWindows']);
Route::post('/windows/reset-tellers', [WindowController::class, 'resetTellers']);

//Reset-Window
Route::post('/waiting_Time-fetch', [ResetWindowSettingController::class, 'fetch']);
Route::post('/waiting_Time', [ResetWindowSettingController::class, 'store']);

//window-Logs
Route::post('/admin/window-logs', [WindowArchiveController::class, 'getArchivedWindows']);
Route::post('/windows/reset-tellers', [WindowController::class, 'resetWindows']);

// adminside

Route::post('/generate-qr', [QrCodeController::class, 'generateQrCode']);
Route::post('/scan-qr', [QrCodeController::class, 'scanQrCode']);

Route::post('/send-email', [MailController::class, 'sendEmail']);
