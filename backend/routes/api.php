<?php

use App\Http\Controllers\AiStreamController;
use Illuminate\Support\Facades\Route;

Route::post('/ai/stream', AiStreamController::class)
    ->middleware('throttle:30');
