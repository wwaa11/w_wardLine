<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;

Route::get('/', [WebController::class, 'main'])->name('index');
Route::get('/filter', [WebController::class, 'filterData']);

// Route::get('/', [WebController::class, 'mainOutsite'])->name('index');
// Route::get('/filter', [WebController::class, 'mainOutfilter']);
// Route::get('/outsite', [WebController::class, 'mainOutsite']);
// Route::get('/outsite/filter', [WebController::class, 'mainOutfilter']);

Route::post('/denail', [WebController::class, 'denialData']);
Route::get('/denail', [WebController::class, 'denialData']);
