<?php

use App\Http\Controllers\Api\v1\ActivityController;
use App\Http\Controllers\Api\v1\AttachmentController;
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\BankAccountController;
use App\Http\Controllers\Api\v1\DriverController;
use App\Http\Controllers\Api\v1\CustomerController;
use App\Http\Controllers\Api\v1\DistrictController;
use App\Http\Controllers\Api\v1\RoleController;
use App\Http\Controllers\Api\v1\SubDistrictController;
use App\Http\Controllers\Api\v1\TransactionController;
use App\Http\Controllers\Api\v1\TransactionDetailController;
use App\Http\Controllers\Api\v1\TripPriceController;
use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\Api\v1\VehicleController;

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('roles', RoleController::class)->names('roles.v1')->whereUuid('role')->only(['index']);
    Route::apiResource('users', UserController::class)->names('users.v1')->whereUuid('user');
    Route::apiResource('transactions', TransactionController::class)->names('transactions.v1')->whereUuid('transaction');
    Route::apiResource('attachments', AttachmentController::class)->names('attachments.v1')->whereUuid('attachment')->except(['index', 'update']);
    Route::apiResource('bank_accounts', BankAccountController::class)->names('bank_accounts.v1')->whereUuid('bank_account');
    // Route::apiResource('cities', CityController::class)->names('cities.v1');
    Route::apiResource('customers', CustomerController::class)->names('customers.v1')->whereUuid('customer');
    Route::apiResource('districts', DistrictController::class)->names('districts.v1')->whereUuid('district');
    Route::apiResource('sub_districts', SubDistrictController::class)->names('sub_districts.v1')->whereUuid('sub_district');
    Route::apiResource('transaction_details', TransactionDetailController::class)->names('transaction_details.v1')->whereUuid('transaction_detail')->except(['index']);
    Route::apiResource('trip_prices', TripPriceController::class)->names('trip_prices.v1')->whereUuid('trip_price');
    Route::apiResource('vehicles', VehicleController::class)->names('vehicles.v1')->whereUuid('vehicle');
    Route::apiResource('drivers', DriverController::class)->names('drivers.v1')->whereUuid('driver');

    // Custom Routes
    // Transaction
    Route::get('/transactions/{transaction}/current-limit', [TransactionController::class, 'getCurrentTransactionLimit'])->name('transactions.v1.current_limit')->whereUuid('transaction');
    Route::patch('transactions/{transaction}/status', [TransactionController::class, 'updateStatus'])->name('transactions.v1.update_status')->whereUuid('transaction');
    Route::get('transactions/{transaction}/logs', [ActivityController::class, 'transactionLogs'])->name('transactions.v1.logs')->whereUuid('transaction');
    Route::get('transaction_details/{transaction_detail}/logs_details', [ActivityController::class, 'transactionDetailLogs'])->name('transaction_details.v1.logs_details')->whereUuid('transaction');


    // Export Transaction
    Route::post('/transactions/export', [TransactionController::class, 'export'])->name('transactions.v1.export');
    Route::post('/transactions/simple_export', [TransactionController::class, 'simpleExport'])->name('transactions.v1.simpleExport');
    Route::get('/transactions/export-status/{jobId}', [TransactionController::class, 'checkStatus'])->name('transactions.v1.export_status');
    Route::get('/transactions/download-export/{jobId}', [TransactionController::class, 'downloadExport'])->name('transactions.v1.download_export');

    // Attachment
    Route::patch('attachments/{attachment}/status', [AttachmentController::class, 'updateStatus'])->name('attachments.v1.update_status')->whereUuid('attachment');

    // Transaction Details
    Route::patch('transaction_details/{transaction_detail}/status', [TransactionDetailController::class, 'updateStatus'])->name('transaction_details.v1.update_status')->whereUuid('transaction_detail');

    // Auth
    Route::get('auth/me', [AuthController::class, 'me'])->name('auth.v1.me');
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.v1.logout');

    // Trip Price
    Route::get('/trip_prices/sub_districts', [TripPriceController::class, 'listTripAllowedSubDistricts'])->name('trip_price.v1.listTripAllowedSubDistricts');
    Route::get('/trip_prices/sub_districts/weight_categories', [TripPriceController::class, 'listTripWeightCategorySubDistricts'])->name('trip_price.v1.listTripWeightCategorySubDistricts');
});


Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login'])->name('auth.v1.login');
    Route::patch('transaction_details/{transaction_detail}/update-detail-based-on-outsider', [TransactionDetailController::class, 'updateDetailBasedOnOutsider'])->name('transaction_details.v1.update_detail_based_on_outsider')->whereUuid('transaction_detail')->middleware('outsider.auth');
});

