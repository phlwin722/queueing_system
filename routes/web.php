<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\QrCodeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MailController;
use App\Http\Controllers\Waiting_timeController;
use App\Http\Controllers\TellerController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\WindowController;

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

// adminside
// Teller Routes
Route::post('/tellers/index', [TellerController::class, 'index']);
Route::post('/tellers/create', [TellerController::class, 'create']);
Route::post('/tellers/update', [TellerController::class, 'update']);
Route::post('/tellers/delete', [TellerController::class, 'delete']);

// Type Routes
Route::post('/types/index', [TypeController::class, 'index']);
Route::post('/types/create', [TypeController::class, 'create']);
Route::post('/types/update', [TypeController::class, 'update']);
Route::post('/types/delete', [TypeController::class, 'delete']);


// Window Routes
Route::post('/windows/index', [WindowController::class, 'index']);
Route::post('/windows/create', [WindowController::class, 'create']);
Route::post('/windows/update', [WindowController::class, 'update']);
Route::post('/windows/delete', [WindowController::class, 'delete']);
Route::post('/windows/form', [WindowController::class, 'form']);
// adminside

Route::post('/generate-qr', [QrCodeController::class, 'generateQrCode']);
Route::post('/scan-qr', [QrCodeController::class, 'scanQrCode']);

Route::post('/send-email', [MailController::class, 'sendEmail']);
