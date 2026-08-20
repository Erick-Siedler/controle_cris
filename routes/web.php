<?php

use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupNoteController;
use App\Http\Controllers\SystemUpdateController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GroupController::class, 'index'])->name('index');
Route::post('system/update', SystemUpdateController::class)
    ->name('system.update');
Route::get('groups/{group}/scope', [GroupController::class, 'scope'])
    ->name('groups.scope');
Route::post('groups/{group}/notes', [GroupNoteController::class, 'store'])
    ->name('groups.notes.store');
Route::patch('groups/{group}/notes/{note}', [GroupNoteController::class, 'update'])
    ->name('groups.notes.update');
Route::delete('groups/{group}/notes/{note}', [GroupNoteController::class, 'destroy'])
    ->name('groups.notes.destroy');
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
Route::post(
    'users/updateDailyChecks',
    [UserController::class, 'updateDailyChecks']
)->name('users.updateDailyChecks');
