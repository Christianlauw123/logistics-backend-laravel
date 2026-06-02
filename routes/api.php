<?php

use App\Http\Controllers\Api\v1\AttachmentController;
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\BankAccountController;
use App\Http\Controllers\Api\v1\CityController;
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

    // Extra: status update as a separate endpoint (PATCH, not PUT)
    Route::patch('transactions/{transaction}/status', [TransactionController::class, 'updateStatus'])->name('transactions.v1.update_status')->whereUuid('transaction');
    Route::patch('attachments/{attachment}/status', [AttachmentController::class, 'updateStatus'])->name('attachments.v1.update_status')->whereUuid('attachment');
    Route::patch('transaction_details/{transaction_detail}/status', [TransactionDetailController::class, 'updateStatus'])->name('transaction_details.v1.update_status')->whereUuid('transaction_detail');

    Route::get('auth/me', [AuthController::class, 'me'])->name('auth.v1.me');
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.v1.logout');

    // Export Transaction
    Route::post('/transactions/export', [TransactionController::class, 'export']);
    Route::get('/transactions/export-status/{jobId}', [TransactionController::class, 'checkStatus']);
    Route::get('/transactions/download-export/{jobId}', [TransactionController::class, 'downloadExport'])->name('transaction.download-export');

    Route::get('/trip_prices/sub_districts', [TripPriceController::class, 'listTripAllowedSubDistricts']);
});

Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login'])->name('auth.v1.login');
});

