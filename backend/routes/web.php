<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Routes de réinitialisation de mot de passe
Route::get('/reset-password/{token}', function ($token) {
    // Déterminer l'URL en fonction de l'environnement (HTTP seulement)
    $frontendUrl = config('app.frontend_url', 'http://cesizen-prod.chickenkiller.com');
    return redirect()->to($frontendUrl . '/reset-password?token=' . $token);
})->name('password.reset');

Route::get('/password/reset', function () {
    $frontendUrl = config('app.frontend_url', 'http://cesizen-prod.chickenkiller.com');
    return redirect()->to($frontendUrl . '/forgot-password');
})->name('password.request');

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'service' => 'cesizen-backend',
        'protocol' => request()->getScheme()
    ]);
});

// Endpoint pour vérifier la configuration (suppression des références SSL)
Route::get('/config-check', function () {
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