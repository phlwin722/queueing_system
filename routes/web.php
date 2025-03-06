<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\QrCodeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MailController;


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
Route::post('/resetQueue', [QueueController::class, 'resetTodayQueueNumbers']);


Route::post('/generate-qr', [QrCodeController::class, 'generateQrCode']);
Route::post('/scan-qr', [QrCodeController::class, 'scanQrCode']);

Route::post('/send-email', [MailController::class, 'sendEmail']);
