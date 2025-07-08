<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PenjualanController; // Import controller ini

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rute API untuk pencarian produk
Route::get('/products/search', [PenjualanController::class, 'searchProduct']);