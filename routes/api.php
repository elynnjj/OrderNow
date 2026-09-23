<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\IngredientController;
use App\Http\Controllers\Api\MenuItemController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('menu-items', MenuItemController::class);
Route::get('ingredients/low-stock', [IngredientController::class, 'lowStock']);
Route::patch('ingredients/{ingredient}/adjust', [IngredientController::class, 'adjust']);
Route::apiResource('ingredients', IngredientController::class)->only(['index', 'store']);

Route::get('dashboard', [DashboardController::class, 'index']);
Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show']);
Route::post('orders/{order}/confirm', [OrderController::class, 'confirm']);
Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);


