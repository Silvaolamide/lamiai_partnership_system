<?php

use App\Http\Controllers\Admin\BusinessPayoutController;
use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\PayoutController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->group(function () {
    // Commission management
    Route::get('/commissions', [CommissionController::class, 'index'])->name('admin.commissions.index');
    Route::get('/commissions/{commission}', [CommissionController::class, 'show'])->name('admin.commissions.show');
    Route::patch('/commissions/{commission}/approve', [CommissionController::class, 'approve'])->name('admin.commissions.approve');
    Route::patch('/commissions/{commission}/mark-payable', [CommissionController::class, 'markPayable'])->name('admin.commissions.mark-payable');
    Route::post('/commissions/bulk-approve', [CommissionController::class, 'bulkApprove'])->name('admin.commissions.bulk-approve');
    Route::post('/commissions/bulk-mark-payable', [CommissionController::class, 'bulkMarkPayable'])->name('admin.commissions.bulk-mark-payable');
    Route::patch('/commissions/{commission}/reverse', [CommissionController::class, 'reverse'])->name('admin.commissions.reverse');

    // Partner payout management
    Route::get('/payouts', [PayoutController::class, 'index'])->name('admin.payouts.index');
    Route::get('/payouts/{payout}', [PayoutController::class, 'show'])->name('admin.payouts.show');
    Route::patch('/payouts/{payout}/approve', [PayoutController::class, 'approve'])->name('admin.payouts.approve');
    Route::patch('/payouts/{payout}/reject', [PayoutController::class, 'reject'])->name('admin.payouts.reject');
    Route::patch('/payouts/{payout}/process', [PayoutController::class, 'process'])->name('admin.payouts.process');

    // Business payout management
    Route::get('/business-payouts', [BusinessPayoutController::class, 'index'])->name('admin.business-payouts.index');
    Route::get('/business-payouts/{businessPayout}', [BusinessPayoutController::class, 'show'])->name('admin.business-payouts.show');
    Route::patch('/business-payouts/{businessPayout}/approve', [BusinessPayoutController::class, 'approve'])->name('admin.business-payouts.approve');
    Route::patch('/business-payouts/{businessPayout}/reject', [BusinessPayoutController::class, 'reject'])->name('admin.business-payouts.reject');
    Route::patch('/business-payouts/{businessPayout}/process', [BusinessPayoutController::class, 'process'])->name('admin.business-payouts.process');
});
