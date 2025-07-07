<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/reset-password/{token}', function ($token) {
    return redirect()->to('https://cesizen-prod.duckdns.org/reset-password?token=' . $token);
})->name('password.reset');

Route::get('/password/reset', function () {
    return redirect()->to('https://cesizen-prod.duckdns.org/forgot-password');
})->name('password.request');