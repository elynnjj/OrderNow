<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('dashboard'))->name('dashboard');
Route::get('/menu', fn() => view('menu.index'))->name('menu.index');
Route::get('/ingredients', fn() => view('ingredients.index'))->name('ingredients.index');
Route::get('/orders', fn() => view('orders.index'))->name('orders.index');
Route::get('/orders/create', fn() => view('orders.create'))->name('orders.create');
Route::get('/orders/{id}', fn($id) => view('orders.show', ['orderId' => $id]))->name('orders.show');
Route::get('/low-stock', fn() => view('low-stock'))->name('low-stock');

