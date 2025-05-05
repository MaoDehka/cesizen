<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/reset-password/{token}', function ($token) {
    return redirect()->to('http://localhost:5173/reset-password?token=' . $token);
})->name('password.reset');

Route::get('/password/reset', function () {
    return redirect()->to('http://localhost:5173/forgot-password');
})->name('password.request');