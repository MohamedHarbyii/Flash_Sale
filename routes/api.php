<?php

use App\Http\Controllers\HoldController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('product/{id}',[ProductController::class,'show']);
Route::post('holds',[HoldController::class,'store']);
Route::post('orders',[OrderController::class,'store']);
Route::post('payments/webhook',[PaymentController::class,'store']);
