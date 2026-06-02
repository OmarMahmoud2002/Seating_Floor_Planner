<?php

use App\Http\Controllers\Api\AuthenticatedUserController;
use App\Http\Controllers\Api\SsoController;
use App\Http\Controllers\Api\SyncEventController;
use App\Http\Controllers\Api\SyncGuestController;
use App\Http\Controllers\Api\SyncGuestDeleteController;
use App\Http\Controllers\Api\SyncGuestGiftController;
use App\Http\Controllers\Api\SyncGuestStatusController;
use App\Http\Controllers\Api\SyncOrganizationController;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', AuthenticatedUserController::class);

Route::prefix('sync')
    ->middleware(['sync', 'sync.token'])
    ->withoutMiddleware(ThrottleRequests::class.':api')
    ->name('api.sync.')
    ->group(function (): void {
        Route::post('/organizations', SyncOrganizationController::class)->name('organizations');
        Route::post('/events', SyncEventController::class)->name('events');
        Route::post('/guests', SyncGuestController::class)->name('guests');
        Route::patch('/guests/status', SyncGuestStatusController::class)->name('guests.status');
        Route::patch('/guests/gift', SyncGuestGiftController::class)->name('guests.gift');
        Route::delete('/guests', SyncGuestDeleteController::class)->name('guests.delete');
        Route::post('/sso-links', SsoController::class)->name('sso-links');
    });
