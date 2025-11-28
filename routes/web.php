<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;

Route::get('/', fn () => Inertia::render('Welcome'));

Route::middleware([
    'auth',
    ValidateSessionWithWorkOS::class,
])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
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
