<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/reset-password/{token}', function ($token) {
    return redirect()->to('http://cesizen-prod.chickenkiller.com/reset-password?token=' . $token);
})->name('password.reset');

Route::get('/password/reset', function () {
    return redirect()->to('http://cesizen-prod.chickenkiller.com/forgot-password');
})->name('password.request');

Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'service' => 'cesizen-backend'
    ]);
});