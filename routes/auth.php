<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\PayoutController as AdminPayoutController;
use App\Http\Controllers\AdminMarketingCampaignController;
use App\Http\Controllers\BusinessMarketingCampaignController;
use App\Http\Controllers\MarketerRecruitmentController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('customer/login', [CustomerAuthController::class, 'createLogin'])->name('customer.login');
    Route::post('customer/login', [CustomerAuthController::class, 'login']);
    Route::get('customer/register', [CustomerAuthController::class, 'createRegister'])->name('customer.register');
    Route::post('customer/register', [CustomerAuthController::class, 'register']);
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::get('/marketer-recruitment', [MarketerRecruitmentController::class, 'show'])->name('marketer.recruitment');
Route::post('/marketer-recruitment', [MarketerRecruitmentController::class, 'store'])->middleware('throttle:10,1')->name('marketer.recruitment.store');
Route::get('/marketer-recruitment/thank-you', [MarketerRecruitmentController::class, 'thankYou'])->name('marketer.recruitment.thank-you');

Route::get('/campaign/{campaign:slug}', [BusinessMarketingCampaignController::class, 'show'])->name('marketing.campaign.show');
Route::post('/campaign/{campaign:slug}', [BusinessMarketingCampaignController::class, 'submit'])->middleware('throttle:10,1')->name('marketing.campaign.submit');
Route::get('/campaign/{campaign:slug}/success', [BusinessMarketingCampaignController::class, 'success'])->name('marketing.campaign.success');
Route::get('/resource-gate', fn () => view('resource-gate.landing'))->name('resource-gate.landing');

Route::middleware(['auth', 'business.approved'])->prefix('business')->group(function () {
    Route::get('/campaigns', [BusinessMarketingCampaignController::class, 'index'])->name('business.campaigns.index');
    Route::get('/campaigns/create', [BusinessMarketingCampaignController::class, 'create'])->name('business.campaigns.create');
    Route::post('/campaigns', [BusinessMarketingCampaignController::class, 'store'])->name('business.campaigns.store');
    Route::get('/campaigns/{campaign}/edit', [BusinessMarketingCampaignController::class, 'edit'])->name('business.campaigns.edit');
    Route::put('/campaigns/{campaign}', [BusinessMarketingCampaignController::class, 'update'])->name('business.campaigns.update');
    Route::patch('/campaigns/{campaign}/toggle', [BusinessMarketingCampaignController::class, 'toggle'])->name('business.campaigns.toggle');
    Route::get('/campaigns/{campaign}/leads', [BusinessMarketingCampaignController::class, 'leads'])->name('business.campaigns.leads');
});

Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->group(function () {
    Route::get('/marketer-recruitment', [MarketerRecruitmentController::class, 'admin'])->name('admin.marketer-recruitment');
    Route::put('/marketer-recruitment/settings', [MarketerRecruitmentController::class, 'updateSettings'])->name('admin.marketer-recruitment.settings');
    Route::get('/marketing-campaigns', [AdminMarketingCampaignController::class, 'index'])->name('admin.marketing-campaigns.index');
    Route::get('/marketing-campaigns/{campaign}/leads', [AdminMarketingCampaignController::class, 'leads'])->name('admin.marketing-campaigns.leads');
    Route::get('/marketing-campaigns/{campaign}/download', [AdminMarketingCampaignController::class, 'download'])->name('admin.marketing-campaigns.download');

    // Commission management. These routes are intentionally kept in the admin
    // route group so they are protected by auth + super_admin middleware.
    Route::get('/commissions', [CommissionController::class, 'index'])->name('admin.commissions.index');
    Route::get('/commissions/{commission}', [CommissionController::class, 'show'])->name('admin.commissions.show');
    Route::patch('/commissions/{commission}/approve', [CommissionController::class, 'approve'])->name('admin.commissions.approve');
    Route::patch('/commissions/{commission}/mark-payable', [CommissionController::class, 'markPayable'])->name('admin.commissions.mark-payable');
    Route::patch('/commissions/{commission}/reverse', [CommissionController::class, 'reverse'])->name('admin.commissions.reverse');
    Route::post('/commissions/bulk-approve', [CommissionController::class, 'bulkApprove'])->name('admin.commissions.bulk-approve');
    Route::post('/commissions/bulk-mark-payable', [CommissionController::class, 'bulkMarkPayable'])->name('admin.commissions.bulk-mark-payable');

    // Admin payout management.
    Route::get('/payouts', [AdminPayoutController::class, 'index'])->name('admin.payouts.index');
    Route::get('/payouts/{payout}', [AdminPayoutController::class, 'show'])->name('admin.payouts.show');
    Route::patch('/payouts/{payout}/approve', [AdminPayoutController::class, 'approve'])->name('admin.payouts.approve');
    Route::patch('/payouts/{payout}/reject', [AdminPayoutController::class, 'reject'])->name('admin.payouts.reject');
    Route::patch('/payouts/{payout}/process', [AdminPayoutController::class, 'process'])->name('admin.payouts.process');
});
