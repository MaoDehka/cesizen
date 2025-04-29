<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DiagnosticController;
use App\Http\Controllers\API\QuestionController;
use App\Http\Controllers\API\QuestionnaireController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Routes publiques
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées par authentification
Route::middleware('auth:sanctum')->group(function () {
    // Utilisateur
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    
    // Questionnaires (accessible à tous les utilisateurs authentifiés)
    Route::get('/questionnaires', [QuestionnaireController::class, 'index']);
    Route::get('/questionnaires/{questionnaire}', [QuestionnaireController::class, 'show']);
    
    // Diagnostics (accessibles aux utilisateurs authentifiés)
    Route::post('/diagnostics', [DiagnosticController::class, 'store']);
    Route::get('/diagnostics', [DiagnosticController::class, 'index']);
    Route::get('/diagnostics/{diagnostic}', [DiagnosticController::class, 'show']);
    
    // Routes réservées aux administrateurs
    Route::middleware('ability:admin')->group(function () {
        // Gestion des utilisateurs
        Route::apiResource('/users', UserController::class);
        
        // Gestion des questionnaires (sauf index et show)
        Route::apiResource('/questionnaires', QuestionnaireController::class)
            ->except(['index', 'show']);
        
        // Gestion des questions
        Route::apiResource('/questions', QuestionController::class);
        
        // Gestion des diagnostics (sauf index, show et store)
        Route::apiResource('/diagnostics', DiagnosticController::class)
            ->except(['index', 'show', 'store']);
    });
});