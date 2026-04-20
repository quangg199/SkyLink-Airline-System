<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AirportController;


Route::get('/airports', [AirportController::class, 'index']);