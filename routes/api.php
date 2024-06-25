<?php

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RentController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\BuildingController;
use App\Http\Controllers\Api\TransactionController;

// test api :
Route::get('/test',[ChatController::class, 'testapi']);
Route::post('/posttest',[ChatController::class, 'posttestapi']);

// chatbot
Route::post('/chat',[ChatController::class, 'responselocal']);

Route::get('/user', function (Request $request) {
    return new UserResource($request->user());
})->middleware('auth:api');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/city', [RegionController::class, 'city'])->name('city');
Route::get('/province', [RegionController::class, 'province'])->name('province');

Route::get('/building/city/{city}', [BuildingController::class, 'getBuildingByCity'])->name('building.city');
Route::get('/building/province/{province}', [BuildingController::class, 'getBuildingByProvince'])->name('building.province');
Route::apiResource('building', BuildingController::class);

Route::apiResource('room', RoomController::class)->except(['create', 'edit', 'update']);
Route::apiResource('rent', RentController::class)->except(['store', 'create', 'edit']);

Route::middleware(['auth:api'])->group(function () {
    Route::get('/me', [UserController::class, 'me']);

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/updateUsers/{id}', [AuthController::class, 'update']);
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/rent/{room}', [RentController::class, 'addRoom'])->name('rent.addRoom');

    Route::post('/payment', [PaymentController::class, 'payment'])->name('payment');
    Route::post('/payment/{transaction}/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');
    Route::get('/payment/{transaction}/callback', [PaymentController::class, 'callback'])->name('payment.callback');

    Route::apiResource('transaction', TransactionController::class)->only(['index', 'show']);
});
