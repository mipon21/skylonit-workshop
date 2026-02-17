<?php

use App\Http\Controllers\Api\PublicAllProjectsController;
use App\Http\Controllers\Api\PublicContactController;
use App\Http\Controllers\Api\PublicFeaturedProjectsController;
use App\Http\Controllers\Api\PublicStatsController;
use App\Http\Controllers\Api\UddoktaPayWebhookController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// UddoktaPay webhook (no auth; validated via RT-UDDOKTAPAY-API-KEY header)
Route::post('uddoktapay/webhook', UddoktaPayWebhookController::class)->name('api.uddoktapay.webhook');

// Public API for marketing site (skf): featured projects – same as Guest Portal featured carousel
Route::get('public/featured-projects', PublicFeaturedProjectsController::class)->name('api.public.featured-projects');
// Public API for marketing site (skf): all public projects – same as Guest Portal project list
Route::get('public/all-projects', PublicAllProjectsController::class)->name('api.public.all-projects');
// Public API for marketing site (skf): contact/lead form – same as Guest Portal contact form
Route::post('public/contact', [PublicContactController::class, 'store'])->name('api.public.contact');
// Public API for marketing site (skf): company stats (projects, clients, years – dynamic from mini erp)
Route::get('public/stats', PublicStatsController::class)->name('api.public.stats');
