<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
// use App\Http\Controllers\OrderController;
// use App\Http\Controllers\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\OrganizerAuthController;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;

// Auth routes for regular users
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Organizer authentication routes
Route::post('/organizer/register', [OrganizerAuthController::class, 'register']);
Route::post('/organizer/login', [OrganizerAuthController::class, 'login']);

// Organizer-only event management
Route::middleware(['auth:sanctum', CheckAbilities::class.':is_organizer'])->group(function () {
    Route::post('/organizer/logout', [OrganizerAuthController::class, 'logout']);
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{event}', [EventController::class, 'update']);
    Route::patch('/events/{event}', [EventController::class, 'update']);
    Route::delete('/events/{event}', [EventController::class, 'destroy']);
});

// Public event routes
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{event}', [EventController::class, 'show']);

// Other authenticated user routes
Route::middleware(['auth:sanctum', CheckAbilities::class.':is_user'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
});
