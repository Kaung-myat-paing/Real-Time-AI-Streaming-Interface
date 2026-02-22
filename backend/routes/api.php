<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiStreamController;

Route::get('/ai/stream', AiStreamController::class);

