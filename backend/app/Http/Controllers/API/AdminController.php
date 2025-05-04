<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Diagnostic;
use App\Models\StressLevel;
use App\Models\User;
use App\Models\Questionnaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Récupérer tous les diagnostics (pour l'administrateur)
     */
    public function getAllDiagnostics()
    {
        try {
            // Vérifier que l'utilisateur est un administrateur
            if (!$this->isAdmin()) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }
            
            // Récupérer tous les diagnostics avec leurs relations
            $diagnostics = Diagnostic::with(['user', 'questionnaire'])
                ->orderBy('diagnostic_date', 'desc')
                ->get();
                
            return response()->json($diagnostics);
        } catch (\Exception $e) {
            Log::error('Error in getAllDiagnostics', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Une erreur est survenue lors du chargement des diagnostics', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Récupérer tous les niveaux de stress (pour l'administrateur)
     */
    public function getAllStressLevels()
    {
        try {
            // Vérifier que l'utilisateur est un administrateur
            if (!$this->isAdmin()) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }
            
            // Récupérer tous les niveaux de stress avec leurs recommandations
            $stressLevels = StressLevel::with('recommendations')
                ->orderBy('min_score', 'asc')
                ->get();
                
            return response()->json($stressLevels);
        } catch (\Exception $e) {
            Log::error('Error in getAllStressLevels', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Une erreur est survenue lors du chargement des niveaux de stress', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtenir des statistiques sur l'utilisation de l'application
     */
    public function getStatistics()
    {
        try {
            // Vérifier que l'utilisateur est un administrateur
            if (!$this->isAdmin()) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }
            
            // Nombre d'utilisateurs
            $userCount = User::count();
            
            // Nombre de questionnaires
            $questionnaireCount = Questionnaire::count();
            
            // Nombre de diagnostics
            $diagnosticCount = Diagnostic::count();
            
            // Nombre de diagnostics sauvegardés
            $savedDiagnosticCount = Diagnostic::where('saved', true)->count();
            
            // Distribution des niveaux de stress
            $stressLevelDistribution = DB::table('diagnostics')
                ->select('stress_level', DB::raw('count(*) as count'))
                ->groupBy('stress_level')
                ->get()
                ->pluck('count', 'stress_level')
                ->toArray();
            
            // Score moyen par questionnaire
            $questionnaireScores = DB::table('diagnostics')
                ->join('questionnaires', 'diagnostics.questionnaire_id', '=', 'questionnaires.id')
                ->select(
                    'questionnaires.id',
                    'questionnaires.title',
                    DB::raw('AVG(diagnostics.score_total) as avg_score'),
                    DB::raw('COUNT(diagnostics.id) as count')
                )
                ->whereNotNull('diagnostics.questionnaire_id')
                ->groupBy('questionnaires.id', 'questionnaires.title')
                ->get();
            
            return response()->json([
                'users' => [
                    'total' => $userCount
                ],
                'questionnaires' => [
                    'total' => $questionnaireCount
                ],
                'diagnostics' => [
                    'total' => $diagnosticCount,
                    'saved' => $savedDiagnosticCount
                ],
                'stress_levels' => $stressLevelDistribution,
                'questionnaire_scores' => $questionnaireScores
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getStatistics', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Une erreur est survenue lors du chargement des statistiques', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Vérifier si l'utilisateur actuel est un administrateur
     */
    private function isAdmin()
    {
        try {
            $user = auth()->user();
            return $user && $user->role && $user->role->name === 'admin';
        } catch (\Exception $e) {
            Log::error('Error checking admin status', ['error' => $e->getMessage()]);
            return false;
        }
    }
}