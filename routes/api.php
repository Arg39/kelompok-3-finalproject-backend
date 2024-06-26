<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BuildingController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\RegionController;

// test api :
Route::get('/test',[ChatController::class, 'testapi']);
Route::post('/posttest',[ChatController::class, 'posttestapi']);

// chatbot
Route::post('/chat',[ChatController::class, 'responselocal']);

// authenticate
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// region building
Route::prefix('regions')->group(function () {
    Route::get('/provinces', [RegionController::class, 'province']);
    Route::get('/provinces/{province_id}', [RegionController::class, 'regencyInProvince']);
    Route::get('/regencies/{regency_id}', [RegionController::class, 'regency']);
});

// building
Route::get('/buildings',[BuildingController::class, 'index']);
Route::get('/building/show/{id}',[BuildingController::class, 'show']);
Route::get('/building/{building_id}/images',[BuildingController::class, 'showImages']);

// middleware
Route::middleware(['auth:api'])->group(function () {
    
    // authenticate
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/updateUsers/{id}', [AuthController::class, 'update']);
    
    // promotion
    Route::post('/promotion/store', [PromotionController::class, 'store'])->name('promosi.store');
    Route::put('/promotion/{id}/update', [PromotionController::class, 'update'])->name('promosi.update');
    Route::delete('/promotion/{id}/delete', [PromotionController::class, 'destroy'])->name('promosi.destroy');
    
    // building
    Route::get('/building/{user_id}',[BuildingController::class, 'showByUserId']);
    Route::post('/building/store/{user_id}',[BuildingController::class, 'store']);
    Route::put('/building/update/{id}',[BuildingController::class, 'update']);
    Route::delete('/building/delete/{id}',[BuildingController::class, 'destroy']);
    Route::post('buildings/{building_id}/store/image', [BuildingController::class, 'storeImage']);
    Route::delete('buildings/image/{building_id}', [BuildingController::class, 'destroyImage']);
});

Route::get('/promotion', [PromotionController::class, 'index'])->name('promosi.show');