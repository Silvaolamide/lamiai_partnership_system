<?php

use App\Http\Controllers\SocialFollowController;
use Illuminate\Support\Facades\Route;

// Public social-follow campaign routes.
// These routes must be loaded with the `web` middleware so campaign
// participants can use the session-based participant token.
Route::get('/social-follow/{slug}', [SocialFollowController::class, 'show'])
    ->name('social-follow.show');

Route::post('/social-follow/{slug}/claim/{socialAccount}', [SocialFollowController::class, 'claim'])
    ->name('social-follow.claim');

Route::get('/social-follow/{slug}/unlock', [SocialFollowController::class, 'unlock'])
    ->name('social-follow.unlock');
