<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TravelController;
use App\Http\Controllers\PDFController;
use OpenAI\Laravel\Facades\OpenAI;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAuthController;

// Explicit Livewire route registration (auto-discovered in Laravel 11+, but explicit for safety)
\Livewire\Livewire::setScriptRoute(function ($handle) {
    return Route::get('/livewire/livewire.js', $handle);
});
\Livewire\Livewire::setUpdateRoute(function ($handle) {
    return Route::post('/livewire/update', $handle);
});
\Livewire\Livewire::setAssetRoute(function ($handle) {
    return Route::get('/livewire/livewire.css', $handle);
});

Route::get('/', [HomeController::class, 'index']);


// Auth
Route::get('/signup', [AuthController::class, 'showSignup']);
Route::post('/signup', [AuthController::class, 'signup']);
Route::get('/signin', [AuthController::class, 'showSignin']);
Route::post('/signin', [AuthController::class, 'signin']);
Route::post('/signout', [AuthController::class, 'signout']);

// Past itinerary history (user only)
Route::middleware('auth')->group(function () {
    Route::get('/history', [\App\Http\Controllers\TripHistoryController::class, 'index']);
    Route::get('/history/{id}', [\App\Http\Controllers\TripHistoryController::class, 'show']);
});

// Admin auth + routes
Route::get('/admin/signin', [AdminAuthController::class, 'showSignin']);
Route::post('/admin/signin', [AdminAuthController::class, 'signin']);

Route::middleware(['auth', 'is_admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard']);
    Route::post('/admin/users/{userId}/history/delete', [AdminController::class, 'deleteUserHistory']);
    Route::post('/admin/trips/{tripId}/delete', [AdminController::class, 'deleteTripHistory']);
});




Route::get('/trip/{id}', [TravelController::class, 'show']);


Route::post('/trip/generate',
    [TravelController::class, 'generate']);

Route::get('/trip/{id}/pdf',
    [TravelController::class, 'downloadPdf']);

Route::get('/download-livewire-pdf', [PDFController::class, 'downloadLivewire']);
Route::get('/view-itinerary', [PDFController::class, 'viewItinerary']);


// TEST OPENAI ROUTE
Route::get('/test-openai', function () {

    $response = OpenAI::chat()->create([
        'model' => 'gpt-4o-mini',
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Hello'
            ]
        ]
    ]);

    return $response->choices[0]->message->content;
});
