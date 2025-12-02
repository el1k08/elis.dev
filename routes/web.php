<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;
use App\Http\Controllers\AccountController;

Route::get('/', fn () => Inertia::render('Home'));

Route::middleware([
    'auth',
    ValidateSessionWithWorkOS::class,
])->group(function () {

    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('accounts', function () {
        return Inertia::render('Accounts');
    })->name('accounts');

});

Route::get('/env-debug', function () {

    return [
        'env' => env('WORKOS_API_KEY'),
        'cfg1' => config('workos.api_key'),
        'cfg2' => config('services.workos.api_key'),
    ];

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
