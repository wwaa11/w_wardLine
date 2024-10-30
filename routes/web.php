<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;

Route::get('/', [WebController::class, 'main'])->name('index');
Route::get('/filter', [WebController::class, 'filterData']);
Route::post('/denail', [WebController::class, 'denialData']);
