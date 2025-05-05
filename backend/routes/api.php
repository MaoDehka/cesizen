<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DiagnosticController;
use App\Http\Controllers\API\QuestionController;
use App\Http\Controllers\API\QuestionnaireController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\RecommendationController;
use App\Http\Controllers\API\StressLevelController;
use App\Http\Controllers\API\ContentController;
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
    Route::get('/diagnostics', [DiagnosticController::class, 'index']);
    Route::get('/diagnostics/{diagnostic}', [DiagnosticController::class, 'show']);
    Route::post('/diagnostics', [DiagnosticController::class, 'store']);
    Route::put('/diagnostics/{diagnostic}', [DiagnosticController::class, 'update']);
    Route::post('/diagnostics/{diagnostic}/save', [DiagnosticController::class, 'saveToHistory']);
    Route::delete('/diagnostics/{diagnostic}', [DiagnosticController::class, 'destroy']);
    
    // Administration
    Route::prefix('admin')->group(function () {
        // Statistiques et données globales
        Route::get('/diagnostics', [AdminController::class, 'getAllDiagnostics']);
        Route::get('/statistics', [AdminController::class, 'getStatistics']);
        Route::get('/stress-levels', [AdminController::class, 'getAllStressLevels']);
        
        // Gestion des niveaux de stress
        Route::get('/stress-levels/{id}', [StressLevelController::class, 'show']);
        Route::post('/stress-levels', [StressLevelController::class, 'store']);
        Route::put('/stress-levels/{id}', [StressLevelController::class, 'update']);
        Route::delete('/stress-levels/{id}', [StressLevelController::class, 'destroy']);
        
        // Gestion des recommandations
        Route::get('/stress-levels/{id}/recommendations', [RecommendationController::class, 'indexByStressLevel']);
        Route::post('/recommendations', [RecommendationController::class, 'store']);
        Route::put('/recommendations/{id}', [RecommendationController::class, 'update']);
        Route::delete('/recommendations/{id}', [RecommendationController::class, 'destroy']);
    });
    
    // Gestion des utilisateurs
    Route::apiResource('/users', UserController::class);
    
    // Gestion des questionnaires (sauf index et show)
    Route::apiResource('/questionnaires', QuestionnaireController::class)
        ->except(['index', 'show']);
    
    // Gestion des questions
    Route::apiResource('/questions', QuestionController::class);

// Routes pour les contenus accessibles à tous
Route::get('/contents/{page}', [ContentController::class, 'getByPage']);

// Routes pour l'administration des contenus (protégées par auth:sanctum)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/admin/contents', [ContentController::class, 'index']);
    Route::get('/admin/contents/{id}', [ContentController::class, 'show']);
    Route::put('/admin/contents/{id}', [ContentController::class, 'update']);
});
});