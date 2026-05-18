<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Welcome to the API',
        'version' => '1.0.0'
    ]);
});
