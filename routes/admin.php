<?php

use App\Http\Controllers\Admin\BusinessPayoutController;
use Illuminate\Support\Facades\Route;

// Additional admin routes kept separate so they are always registered with the admin workspace.
Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->group(function () {
    Route::get('/business-payouts', [BusinessPayoutController::class, 'index'])->name('admin.business-payouts.index');
    Route::get('/business-payouts/{businessPayout}', [BusinessPayoutController::class, 'show'])->name('admin.business-payouts.show');
    Route::patch('/business-payouts/{businessPayout}/approve', [BusinessPayoutController::class, 'approve'])->name('admin.business-payouts.approve');
    Route::patch('/business-payouts/{businessPayout}/reject', [BusinessPayoutController::class, 'reject'])->name('admin.business-payouts.reject');
    Route::patch('/business-payouts/{businessPayout}/process', [BusinessPayoutController::class, 'process'])->name('admin.business-payouts.process');
});
