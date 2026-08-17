<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PartnershipProgramController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\Admin\PartnerController as AdminPartnerController;
use App\Http\Controllers\PartnerDashboardController;


Route::middleware(['auth'])->group(function () {

    Route::get('/partner/dashboard', [PartnerDashboardController::class, 'index'])
        ->name('partner.dashboard');

});


Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/partners', [AdminPartnerController::class, 'index'])
        ->name('admin.partners.index');

    Route::patch('/partners/{partner}/approve', [AdminPartnerController::class, 'approve'])
        ->name('admin.partners.approve');

    Route::patch('/partners/{partner}/reject', [AdminPartnerController::class, 'reject'])
        ->name('admin.partners.reject');

});

Route::get('/partner/apply', [PartnerController::class, 'create'])
    ->name('partner.apply');

Route::post('/partner/apply', [PartnerController::class, 'store'])
    ->name('partner.apply.store');

Route::get('/admin/programs/{program}/edit', [
    PartnershipProgramController::class,
    'edit'
])->name('admin.programs.edit');
Route::put('/admin/programs/{program}', [
    PartnershipProgramController::class,
    'update'
])->name('admin.programs.update');

Route::get('/admin/products', [ProductController::class, 'index'])
    ->name('admin.products.index');

Route::get('/admin/products/create', [ProductController::class, 'create'])
    ->name('admin.products.create');

Route::post('/admin/products', [ProductController::class, 'store'])
    ->name('admin.products.store');

Route::get('/admin/products/{product}/edit', [ProductController::class, 'edit'])
    ->name('admin.products.edit');

Route::put('/admin/products/{product}', [ProductController::class, 'update'])
    ->name('admin.products.update');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::middleware(['auth', 'role:super_admin'])->group(function () {

    Route::get('/admin', function () {
        return view('admin.dashboard');
    });

});

Route::get('/admin/programs', [
    PartnershipProgramController::class,
    'index'
])->name('admin.programs.index');

Route::get('/admin/programs/create', [
    PartnershipProgramController::class,
    'create'
])->name('admin.programs.create');

Route::post('/admin/programs', [
    PartnershipProgramController::class,
    'store'
])->name('admin.programs.store');


require __DIR__.'/auth.php';
