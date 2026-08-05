<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GroupController;

Route::get('/', [GroupController::class, 'index'])->name('index');
Route::get('groups/{group}/scope', [GroupController::class, 'scope'])
    ->name('groups.scope');
Route::get(
    'groups/{group}/users/{user}',
    [GroupController::class, 'participant']
)->name('groups.participants.show');
Route::resource('groups', GroupController::class);
Route::resource('users', UserController::class);
Route::post('users/storeByGroup', [UserController::class, 'storeByGroup'])->name('users.storeByGroup');
Route::post('users/createAdditionals', [UserController::class, 'storeAdditionals'])->name('users.storeAdditionals');
Route::post(
    'users/createAdditionalsBatch',
    [UserController::class, 'storeAdditionalsBatch']
)->name('users.storeAdditionalsBatch');
Route::post('users/updateAdditionals', [UserController::class, 'updateAdditionals'])->name('users.updateAdditionals');
Route::post('users/updateDaily', [UserController::class, 'updateDaily'])->name('users.updateDaily');
