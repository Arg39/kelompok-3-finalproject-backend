<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PromotionController;

// test api :
Route::get('/test',[ChatController::class, 'testapi']);
Route::post('/posttest',[ChatController::class, 'posttestapi']);

// chatbot
Route::post('/chat',[ChatController::class, 'responselocal']);

// authenticate
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// middleware
Route::middleware(['auth:api'])->group(function () {

    // authenticate
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/updateUsers/{id}', [AuthController::class, 'update']);
    
    // promotion
    Route::post('/promosi/store', [PromotionController::class, 'store'])->name('promosi.store');
    Route::put('/promosi/{id}/update', [PromotionController::class, 'update'])->name('promosi.update');
    Route::delete('/promosi/{id}/delete', [PromotionController::class, 'destroy'])->name('promosi.destroy');
});

Route::get('/promosi', [PromotionController::class, 'index'])->name('promosi.show');