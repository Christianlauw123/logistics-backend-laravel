<?php

use App\Http\Controllers\Api\V1\AttachmentController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BankAccountController;
use App\Http\Controllers\Api\V1\CityController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\DistrictController;
use App\Http\Controllers\Api\V1\SubDistrictController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\TransactionDetailController;
use App\Http\Controllers\Api\V1\TripPriceController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\VehicleController;

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class)->names('users.v1');
    Route::apiResource('transactions', TransactionController::class)->names('transactions.v1');
    Route::apiResource('attachments', AttachmentController::class)->names('attachments.v1');
    Route::apiResource('bank_accounts', BankAccountController::class)->names('bank_accounts.v1');
    Route::apiResource('cities', CityController::class)->names('cities.v1');
    Route::apiResource('customers', CustomerController::class)->names('customers.v1');
    Route::apiResource('districts', DistrictController::class)->names('customers.v1');
    Route::apiResource('sub_districts', SubDistrictController::class)->names('sub_districts.v1');
    Route::apiResource('transaction_details', TransactionDetailController::class)->names('transaction_details.v1');
    Route::apiResource('trip_prices', TripPriceController::class)->names('trip_prices.v1');
    Route::apiResource('vehicles', VehicleController::class)->names('vehicles.v1');

    // Extra: status update as a separate endpoint (PATCH, not PUT)
    Route::patch('transactions/{transaction}/status', [TransactionController::class, 'updateStatus'])->name('transactions.v1.update_status');
    Route::patch('attachments/{attachment}/status', [AttachmentController::class, 'updateStatus'])->name('attachments.v1.update_status');
    Route::patch('transaction_details/{transaction_detail}/status', [TransactionDetailController::class, 'updateStatus'])->name('transaction_details.v1.update_status');
});

Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login'])->name('auth.v1.login');
    Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('auth.v1.logout');
});

