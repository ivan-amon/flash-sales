<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrganizerAuthController;
use App\Http\Controllers\UserController;
// use App\Http\Controllers\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth routes for regular users
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Email verification
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');
Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
    ->middleware(['auth:sanctum', 'throttle:6,1'])
    ->name('verification.send');

// Organizer authentication routes
Route::post('/organizer/register', [OrganizerAuthController::class, 'register']);
Route::post('/organizer/login', [OrganizerAuthController::class, 'login']);

// Organizer-only management
Route::middleware(['auth:sanctum', 'abilities:is_organizer'])->group(function () {
    Route::post('/organizer/logout', [OrganizerAuthController::class, 'logout']);
    Route::get('/organizer/events', [EventController::class, 'organizerEvents']);
});

// Other authenticated user routes
Route::middleware(['auth:sanctum', 'abilities:is_user'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::patch('/user/country', [UserController::class, 'updateCountry']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Authenticated routes for orders and events
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{event}', [EventController::class, 'update']);
    Route::patch('/events/{event}', [EventController::class, 'update']);
    Route::delete('/events/{event}', [EventController::class, 'destroy']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/orders/{order}/pay', [OrderController::class, 'processPayment']);
});

// Public event routes
Route::get('/events', [EventController::class, 'index'])->middleware('resolve.country');
Route::get('/events/{event}', [EventController::class, 'show']);

// Public location routes
Route::get('/countries', [CountryController::class, 'index']);
Route::get('/cities', [CityController::class, 'index']);
