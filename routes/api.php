<?php

use App\Http\Controllers\HoldController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('product/{id}',[ProductController::class,'show']);
Route::post('holds',[HoldController::class,'store']);
