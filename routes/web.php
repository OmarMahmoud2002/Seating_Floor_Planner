<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventPreviewController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FloorplanController;
use App\Http\Controllers\FloorplanEditorController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\GuestImportController;
use App\Http\Controllers\GuestTypeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\SsoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', HomeController::class);
Route::get('/sso/{token}', [SsoController::class, 'consume'])->name('sso.consume');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::resource('organizations', OrganizationController::class)->only(['index', 'show']);
    Route::resource('events', EventController::class);
    Route::post('/events/{event}/preview-token', [EventController::class, 'refreshPreviewToken'])
        ->name('events.preview-token.refresh');
    Route::get('/events/{event}/guests/export', [ExportController::class, 'guests'])
        ->name('events.guests.export');
    Route::get('/events/{event}/guests', [GuestController::class, 'index'])
        ->name('events.guests.index');
    Route::post('/events/{event}/guests', [GuestController::class, 'store'])
        ->name('events.guests.store');
    Route::get('/events/{event}/guests/import', [GuestImportController::class, 'create'])
        ->name('events.guests.import.create');
    Route::post('/events/{event}/guests/import/preview', [GuestImportController::class, 'preview'])
        ->name('events.guests.import.preview');
    Route::post('/events/{event}/guests/import', [GuestImportController::class, 'store'])
        ->name('events.guests.import.store');
    Route::get('/guests/{guest}/edit', [GuestController::class, 'edit'])
        ->name('guests.edit');
    Route::put('/guests/{guest}', [GuestController::class, 'update'])
        ->name('guests.update');
    Route::delete('/guests/{guest}', [GuestController::class, 'destroy'])
        ->name('guests.destroy');
    Route::resource('guest-types', GuestTypeController::class)->except(['create', 'show']);
    Route::get('/floorplans', [FloorplanController::class, 'index'])
        ->name('floorplans.index');
    Route::get('/events/{event}/floorplans/create', [FloorplanController::class, 'create'])
        ->name('events.floorplans.create');
    Route::post('/events/{event}/floorplans', [FloorplanController::class, 'store'])
        ->name('events.floorplans.store');
    Route::get('/floorplans/{floorplan}/edit', [FloorplanController::class, 'edit'])
        ->name('floorplans.edit');
    Route::put('/floorplans/{floorplan}', [FloorplanController::class, 'update'])
        ->name('floorplans.update');
    Route::delete('/floorplans/{floorplan}', [FloorplanController::class, 'destroy'])
        ->name('floorplans.destroy');
    Route::get('/floorplans/{floorplan}/editor', [FloorplanEditorController::class, 'show'])
        ->name('floorplans.editor');
    Route::post('/floorplans/{floorplan}/export/pdf', [ExportController::class, 'floorplanPdf'])
        ->name('floorplans.export.pdf');
    Route::prefix('editor')->group(function () {
        Route::get('/floorplans/{floorplan}/data', [FloorplanEditorController::class, 'data'])
            ->name('editor.floorplans.data');
        Route::post('/floorplans/{floorplan}/save', [FloorplanEditorController::class, 'save'])
            ->name('editor.floorplans.save');
        Route::post('/floorplans/{floorplan}/seats/assign', [FloorplanEditorController::class, 'assignSeat'])
            ->name('editor.floorplans.seats.assign');
        Route::post('/floorplans/{floorplan}/seats/unassign', [FloorplanEditorController::class, 'unassignSeat'])
            ->name('editor.floorplans.seats.unassign');
        Route::post('/floorplans/{floorplan}/guests', [FloorplanEditorController::class, 'storeGuest'])
            ->name('editor.floorplans.guests.store');
        Route::post('/floorplans/{floorplan}/guests/{guest}/type', [FloorplanEditorController::class, 'updateGuestType'])
            ->name('editor.floorplans.guests.type');
    });
});

Route::get('/preview/events/{previewToken}', [EventPreviewController::class, 'show'])
    ->name('events.preview');
Route::get('/preview/events/{previewToken}/floorplans/{floorplan}', [EventPreviewController::class, 'floorplan'])
    ->name('events.floorplans.preview');
