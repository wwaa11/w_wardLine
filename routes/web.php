<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;
use App\Http\Middleware\pr9Auth;

Route::get('/auth', [WebController::class, 'Auth']);
Route::post('/authcheck', [WebController::class, 'AuthCheck']);
Route::post('/unauth', function () {  Auth::logout(); });

Route::middleware([pr9Auth::class])->group(function () {
    // Route::get('/', [WebController::class, 'main'])->name('index');
    // Route::get('/filter', [WebController::class, 'filterData']);
    // Pr9web
    Route::get('/', [WebController::class, 'mainOutsite'])->name('index');
    Route::get('/filter', [WebController::class, 'mainOutfilter']);

    // Route::get('/outsite', [WebController::class, 'mainOutsite']);
    // Route::get('/outsite/filter', [WebController::class, 'mainOutfilter']);

    Route::post('/denail', [WebController::class, 'denialData']);
    Route::get('/denail', [WebController::class, 'denialData']);
});
