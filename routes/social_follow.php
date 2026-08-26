<?php

use App\Http\Controllers\SocialFollowController;
use Illuminate\Support\Facades\Route;

// Public customer-facing Social Follow campaign routes.
// These must remain outside the authenticated business-management group because
// campaign visitors are not required to have an AIPM account.
Route::get('/social-follow/{slug}', [SocialFollowController::class, 'show'])
    ->name('social-follow.show');

Route::post('/social-follow/{slug}/claim/{socialAccount}', [SocialFollowController::class, 'claim'])
    ->name('social-follow.claim');

Route::post('/social-follow/{slug}/unlock', [SocialFollowController::class, 'unlock'])
    ->name('social-follow.unlock');
