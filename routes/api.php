<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    Route::apiResource('transactions', TransactionController::class);

    // Extra: status update as a separate endpoint (PATCH, not PUT)
    Route::patch('transactions/{transaction}/status', [TransactionController::class, 'updateStatus']);
});

Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});
