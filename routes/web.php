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
use App\Http\Controllers\BreakTimeController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ServingTimeController;
use App\Http\Controllers\SurveyResponseController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ResetWindowSettingController;
use App\Models\Appointment;

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

Route::post('/admin/validate', [AdminController::class, 'adminValidate']);
Route::post('/admin/Information', [AdminController::class, "index"]);
Route::post('/admin/updateInformation', [AdminController::class, "updateqInformation"]);
Route::post('/admin/updatePassword', [AdminController::class, "updatePassword"]);
Route::post('/createAdmin', [AdminController::class, 'createAdmin']);

Route::post('/customer-join', [QueueController::class, 'joinQueue']);
Route::post('/customer-join-switch-teller', [QueueController::class, 'joinSwitchQueue']);
Route::post('/customer-list', [QueueController::class, 'getQueueList']);
Route::post('/customer-leave', [QueueController::class, 'leaveQueue']);
Route::post('/customer-fetch', [QueueController::class, 'customerData']);
Route::post('/update-teller_id', [QueueController::class, 'updateTellerId']);

Route::post('/admin/queue-list', [QueueController::class, 'getCQueueList']);
Route::post('/admin/cater', [QueueController::class, 'caterCustomer']);
Route::post('/admin/cancel', [QueueController::class, 'cancelCustomer']);
Route::post('/admin/finish', [QueueController::class, 'finishCustomer']);
Route::post('/admin/start-wait', [QueueController::class, 'startWait']);
Route::post('/send-fetchInfo', [QueueController::class, 'fetchData']);
Route::post('/admin/queue-logs', [QueueController::class, 'queueLogs']);
Route::post('/admin/reports', [QueueController::class, 'fetchReports']);
Route::post('/resetQueue', [QueueController::class, 'resetTodayQueueNumbers']);
Route::post('/waitCustomer', [QueueController::class, 'WaitCustomer']);
Route::post('/customer-check-waiting', [QueueController::class, 'checkWaitingCustomer']);
Route::post('/waitCustomerReset', [QueueController::class, 'WaitingCustomerReset']);
Route::post('/update-queue-positions', [QueueController::class, 'updatePositions']);

Route::post('/admin/waiting_Time', [Waiting_timeController::class, 'store']);
Route::post('/admin/waiting_Time-fetch', [Waiting_timeController::class, 'index']);
Route::post('/admin/waiting_Time-update', [Waiting_timeController::class, 'update']);
Route::post('/upload-image', [AdminController::class, 'uploadImage']);

Route::post('/admin/break_time', [BreakTimeController::class, 'storeBreakTime']);
Route::post('/admin/fetch_break_time', [BreakTimeController::class, 'fetchBreakTime']);

// adminside
// Teller Routes
Route::post('/tellers/index', [TellerController::class, 'index']);
Route::post('/tellers/indexx', [TellerController::class, 'windowFetch']);
Route::post('/tellers/create', [TellerController::class, 'create']);
Route::post('/tellers/fetch-assigned', [TellerController::class, 'fetchingAssignedTeller']);
Route::post('/tellers/update', [TellerController::class, 'update']);
Route::post('/tellers/delete', [TellerController::class, 'delete']);
Route::post('/teller/typeid-value', [TellerController::class, 'valueTypeid']);
Route::post('/teller/validate', [TellerController::class, 'validationLoginTeller']);
Route::post('/tellers/dropdown', [TellerController::class, 'viewTellerDropdown']);
Route::post('/teller/queue-logs', [TellerController::class, 'queueLogs']);
Route::post('/teller/waiting_Time-fetch', [Waiting_timeController::class, 'index']);
Route::post('/teller/image', [TellerController::class, 'fetchImage']);
Route::post('/teller/image-teller', [TellerController::class, 'fetchImageTeller']);
Route::post('/teller/image-fetch-csdashboard', [TellerController::class, 'fetchImageTellerCsDashboaard']);
Route::post('/teller/logout', [TellerController::class, 'tellerLogout']);
Route::post('/dropdown/types',[TellerController::class, 'dropdownTypes']);

Route::post('/teller/queue-list', [TellercaterController::class, 'getTellerQueueList']);
Route::post('/teller/cater', [TellercaterController::class, 'caterTellerCustomer']);
Route::post('/teller/cancel', [TellercaterController::class, 'cancelCustomer']);
Route::post('/teller/finish', [TellercaterController::class, 'finishCustomer']);

// Type Routes
Route::post('/types/index', [TypeController::class, 'index']);
Route::post('/types/filteredTypes', [TypeController::class, 'filteredTypes']);
Route::post('/types/create', [TypeController::class, 'create']);
Route::post('/types/update', [TypeController::class, 'update']);
Route::post('/types/delete', [TypeController::class, 'delete']);
Route::post('/types/dropdown', [TypeController::class, 'viewTypesDropdown']);
Route::post('/type/Branch',[TypeController::class,'fetchBranch']);

// Window Routes
Route::post('/windows/index', [WindowController::class, 'index']);
Route::post('/windows/create', [WindowController::class, 'create']);
Route::post('/windows/update', [WindowController::class, 'update']);
Route::post('/windows/delete', [WindowController::class, 'delete']);
Route::post('/windows/types/dropdown',[WindowController::class,'viewTypesDropdown']);
Route::post('/window/tellers/dropdown',[WindowController::class,'viewtellersDropdown']);

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

//Serving Time Routes
Route::post('/teller/save-serving-time', [ServingTimeController::class, 'saveServingTime']);
Route::post('/teller/today-serving-stats', [ServingTimeController::class, 'getTodayServingStats']);
Route::post('/teller/update-serving-time', [ServingTimeController::class, 'updateServingTime']);
Route::post('/teller/update-all-serving-time', [ServingTimeController::class, 'updateAllServingTime']);
Route::post('/teller/fetch-serving-time', [ServingTimeController::class, 'fetchServingTime']);

/* 
//Reset-Window
Route::post('/waiting_Time-fetch', [ResetWindowSettingController::class, 'fetch']);
Route::post('/waiting_Time', [ResetWindowSettingController::class, 'store']);
 */

//Reset-Settings Automatic 
Route::post('/windows/fetch-reset-settings', [ResetSettingController::class, 'fetchSettings']);
Route::post('/windows/reset-settings', [ResetSettingController::class, 'saveSettings']);

// adminside
Route::post('/generate-qr', [QrCodeController::class, 'generateQrCode']);
Route::post('/scan-qr', [QrCodeController::class, 'scanQrCode']);

Route::post('/send-email', [MailController::class, 'sendEmail']);
Route::post('/sent-email-finish', [MailController::class, 'sendEmailFinish']);
Route::post('/sent-email-dashboard', [MailController::class, 'sentEmailDashboard']);

//Waiting-Time 
Route::post('/waiting_Time', [Waiting_timeController::class, 'store']);
Route::post('/waiting_Time-fetch', [Waiting_timeController::class, 'index']);
Route::post('/waiting_Time-update', [Waiting_timeController::class, 'update']);

//thank you page (survey)
Route::post('/survey', [SurveyResponseController::class, 'store']);
Route::post('/admin/survey-stats', [SurveyResponseController::class, 'SurveyStats']);

//online appointment
Route::post('/appointments', [AppointmentController::class, 'store']);
Route::post('/branches/slots', [AppointmentController::class, 'getSlots']);
Route::post('/type/service',[AppointmentController::class, 'services']);
Route::post('/apply_slots', [AppointmentController::class, 'apply_slots']);
Route::post('/get_weekly_slots', [AppointmentController::class, 'getWeeklySlots']);

// Manager Routes
Route::post('/manager/index', [ManagerController::class, 'index']);
Route::post('/manager/create', [ManagerController::class, 'create']);
Route::post('/manager/update', [ManagerController::class, 'update']);
Route::post('/manager/delete', [ManagerController::class, 'delete']);
Route::post('/manager/image',[ManagerController::class,'fetchImage']);
Route::post('/manager/validate',[ManagerController::class, 'validationLoginManager']);

// Branch Routes
Route::post('/branch/index', [BranchController::class, 'index']);
Route::post('/admin-branch/fetchManager', [BranchController::class, 'fetchManager']);
Route::post('/branch/create', [BranchController::class, 'create']);
Route::post('/branch/update', [BranchController::class, 'update']);
Route::post('/branch/delete', [BranchController::class, 'delete']);
Route::post('/branch/image',[ManagerController::class,'fetchImage']);

// fetching PSGC API
Route::post('/api/regions', [BranchController::class, 'getRegions']);
Route::post('/api/provinces', [BranchController::class, 'getProvinces']);
Route::post('/api/cities', [BranchController::class, 'getCities']);
Route::post('/api/barangays', [BranchController::class, 'getBarangays']);

