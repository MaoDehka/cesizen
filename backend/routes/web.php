<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Routes de réinitialisation de mot de passe avec HTTPS
Route::get('/reset-password/{token}', function ($token) {
    // Déterminer l'URL en fonction de l'environnement
    $frontendUrl = config('app.frontend_url', 'https://cesizen-prod.chickenkiller.com');
    return redirect()->to($frontendUrl . '/reset-password?token=' . $token);
})->name('password.reset');

Route::get('/password/reset', function () {
    $frontendUrl = config('app.frontend_url', 'https://cesizen-prod.chickenkiller.com');
    return redirect()->to($frontendUrl . '/forgot-password');
})->name('password.request');

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'service' => 'cesizen-backend',
        'ssl' => request()->isSecure() ? 'enabled' : 'disabled',
        'protocol' => request()->isSecure() ? 'https' : 'http'
    ]);
});

// Endpoint pour vérifier la configuration SSL
Route::get('/ssl-check', function () {
    return response()->json([
        'is_secure' => request()->isSecure(),
        'protocol' => request()->getScheme(),
        'host' => request()->getHost(),
        'headers' => [
            'x_forwarded_proto' => request()->header('X-Forwarded-Proto'),
            'x_forwarded_host' => request()->header('X-Forwarded-Host'),
            'x_forwarded_port' => request()->header('X-Forwarded-Port'),
        ]
    ]);
});