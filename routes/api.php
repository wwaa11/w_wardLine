<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;

Route::post('/getlist', [WebController::class, 'getList']);