<?php

use App\Http\Controllers\AiStreamController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::match(['post', 'options'], '/ai/stream', AiStreamController::class)
    ->middleware('throttle:30');

Route::get('/ai/stream', fn (): JsonResponse => response()->json([
    'message' => 'Send a POST request with a JSON body containing a "prompt" field.',
], 200));
