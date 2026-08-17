<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PartnershipProgramController;
use App\Http\Controllers\BusinessOnboardingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductShowController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\Admin\PartnerController as AdminPartnerController;
use App\Http\Controllers\PartnerDashboardController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\PayoutController as AdminPayoutController;
use App\Models\PartnershipProgram;

Route::middleware(['auth'])->group(function () {
    Route::get('/partner/dashboard', [PartnerDashboardController::class, 'index'])->name('partner.dashboard');
    Route::get('/partner/payouts', [PayoutController::class, 'index'])->name('partner.payouts.index');
    Route::post('/partner/payouts', [PayoutController::class, 'store'])->name('partner.payouts.store');

    Route::get('/business/onboarding/{step}', [BusinessOnboardingController::class, 'show'])->name('business.onboarding');
    Route::post('/business/onboarding/{step}', [BusinessOnboardingController::class, 'store'])->name('business.onboarding.store');
});

Route::get('/business/start', [BusinessOnboardingController::class, 'start'])->name('business.start');

Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin');

    Route::get('/partners', [AdminPartnerController::class, 'index'])->name('admin.partners.index');
    Route::patch('/partners/{partner}/approve', [AdminPartnerController::class, 'approve'])->name('admin.partners.approve');
    Route::patch('/partners/{partner}/reject', [AdminPartnerController::class, 'reject'])->name('admin.partners.reject');

    Route::get('/programs', [PartnershipProgramController::class, 'index'])->name('admin.programs.index');
    Route::get('/programs/create', [PartnershipProgramController::class, 'create'])->name('admin.programs.create');
    Route::post('/programs', [PartnershipProgramController::class, 'store'])->name('admin.programs.store');
    Route::get('/programs/{program}/edit', [PartnershipProgramController::class, 'edit'])->name('admin.programs.edit');
    Route::put('/programs/{program}', [PartnershipProgramController::class, 'update'])->name('admin.programs.update');

    Route::get('/products', [ProductController::class, 'index'])->name('admin.products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('admin.products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('admin.products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('admin.products.update');

    Route::get('/orders', [OrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
    Route::patch('/orders/{order}/mark-paid', [OrderController::class, 'markPaid'])->name('admin.orders.mark-paid');
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('admin.orders.cancel');
    Route::patch('/orders/{order}/refund', [OrderController::class, 'refund'])->name('admin.orders.refund');

    Route::get('/commissions', [CommissionController::class, 'index'])->name('admin.commissions.index');
    Route::get('/commissions/{commission}', [CommissionController::class, 'show'])->name('admin.commissions.show');
    Route::patch('/commissions/{commission}/approve', [CommissionController::class, 'approve'])->name('admin.commissions.approve');
    Route::patch('/commissions/{commission}/mark-payable', [CommissionController::class, 'markPayable'])->name('admin.commissions.mark-payable');
    Route::patch('/commissions/{commission}/reverse', [CommissionController::class, 'reverse'])->name('admin.commissions.reverse');
    Route::post('/commissions/bulk-approve', [CommissionController::class, 'bulkApprove'])->name('admin.commissions.bulk-approve');
    Route::post('/commissions/bulk-mark-payable', [CommissionController::class, 'bulkMarkPayable'])->name('admin.commissions.bulk-mark-payable');

    Route::get('/payouts', [AdminPayoutController::class, 'index'])->name('admin.payouts.index');
    Route::get('/payouts/{payout}', [AdminPayoutController::class, 'show'])->name('admin.payouts.show');
    Route::patch('/payouts/{payout}/approve', [AdminPayoutController::class, 'approve'])->name('admin.payouts.approve');
    Route::patch('/payouts/{payout}/reject', [AdminPayoutController::class, 'reject'])->name('admin.payouts.reject');
    Route::patch('/payouts/{payout}/process', [AdminPayoutController::class, 'process'])->name('admin.payouts.process');
});

Route::get('/partner/apply', [PartnerController::class, 'create'])->name('partner.apply');
Route::post('/partner/apply', [PartnerController::class, 'store'])->name('partner.apply.store');

Route::get('/', function () {
    $programs = PartnershipProgram::query()
        ->where('status', 'active')
        ->with(['commissionRules' => fn ($query) => $query->where('status', true)->orderBy('priority')->orderBy('level')])
        ->latest()
        ->limit(6)
        ->get();

    return view('welcome', compact('programs'));
});

Route::get('/dashboard', fn () => view('dashboard'))->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/product/{slug}', [ProductShowController::class, 'show'])->name('product.show');
Route::get('/checkout/paystack/callback', [CheckoutController::class, 'paystackCallback'])->name('checkout.paystack.callback');
Route::post('/webhooks/paystack', [CheckoutController::class, 'paystackWebhook'])->name('webhooks.paystack');

Route::middleware('auth')->group(function () {
    Route::post('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::get('/checkout/{orderId}', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/{orderId}/paystack', [CheckoutController::class, 'paystack'])->name('checkout.paystack');
    Route::post('/checkout/{orderId}/confirm-demo', [CheckoutController::class, 'confirm'])->name('checkout.confirm');
    Route::get('/order/{orderId}/success', [CheckoutController::class, 'success'])->name('order.success');
});

require __DIR__.'/auth.php';
