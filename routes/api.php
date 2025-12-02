<?php

use App\Http\Controllers\UserController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);



// Route::put('update/{id}', [UserController::class, 'update']);
Route::middleware([IsUser::class])->group(function () {
    Route::put('update/{id}', [UserController::class, 'update']);
    Route::post('logout', [UserController::class, 'logout']);
    Route::get('getuser/{id}', [UserController::class, 'getuser']);
});

Route::middleware([IsAdmin::class])->group(function () {
    // Route::put('update/{id}', [UserController::class, 'update']);
    Route::delete('delete/{id}', [UserController::class, 'delete']);
    Route::get('getall', [UserController::class, 'getallusers']);
});
