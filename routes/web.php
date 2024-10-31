<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;

Route::get('/', [WebController::class, 'main'])->name('index');
Route::get('/filter', [WebController::class, 'filterData']);
Route::post('/denail', [WebController::class, 'denialData']);

Route::get('/outsite', [WebController::class, 'mainOutsite']);
Route::get('/outsite/filter', [WebController::class, 'mainOutfilter']);

