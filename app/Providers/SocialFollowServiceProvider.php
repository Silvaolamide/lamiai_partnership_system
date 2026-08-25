<?php

namespace App\Providers;

use App\Http\Controllers\SocialFollowController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SocialFollowServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware(['auth','verified','role:program_manager','business.approved'])->group(function () {
            Route::get('/business/social-follow/accounts', [SocialFollowController::class, 'accounts'])->name('business.social-follow.accounts');
            Route::post('/business/social-follow/accounts', [SocialFollowController::class, 'saveAccounts'])->name('business.social-follow.accounts.save');
            Route::get('/business/social-follow/campaigns', [SocialFollowController::class, 'campaigns'])->name('business.social-follow.campaigns.index');
            Route::get('/business/social-follow/campaigns/create', [SocialFollowController::class, 'create'])->name('business.social-follow.campaigns.create');
            Route::post('/business/social-follow/campaigns', [SocialFollowController::class, 'store'])->name('business.social-follow.campaigns.store');
            Route::get('/business/social-follow/campaigns/{campaign}/edit', [SocialFollowController::class, 'edit'])->name('business.social-follow.campaigns.edit');
            Route::put('/business/social-follow/campaigns/{campaign}', [SocialFollowController::class, 'update'])->name('business.social-follow.campaigns.update');
            Route::patch('/business/social-follow/campaigns/{campaign}/toggle', [SocialFollowController::class, 'toggle'])->name('business.social-follow.campaigns.toggle');
        });

        Route::get('/social-follow/{slug}', [SocialFollowController::class, 'show'])->name('social-follow.show');
        Route::post('/social-follow/{slug}/claim/{socialAccount}', [SocialFollowController::class, 'claim'])->name('social-follow.claim');
        Route::post('/social-follow/{slug}/unlock', [SocialFollowController::class, 'unlock'])->name('social-follow.unlock');
    }
}
