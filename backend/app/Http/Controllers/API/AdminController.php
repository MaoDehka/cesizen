<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Diagnostic;
use App\Models\StressLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Récupérer tous les diagnostics (pour l'administrateur)
     */
    public function getAllDiagnostics()
    {
        // Vérifier que l'utilisateur est un administrateur
        if (!$this->isAdmin()) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }
        
        // Récupérer tous les diagnostics avec leurs relations
        $diagnostics = Diagnostic::with(['user', 'questionnaire'])
            ->orderBy('diagnostic_date', 'desc')
            ->get();
            
        return response()->json($diagnostics);
    }

    /**
     * Récupérer tous les niveaux de stress (pour l'administrateur)
     */
    public function getAllStressLevels()
    {
        // Vérifier que l'utilisateur est un administrateur
        if (!$this->isAdmin()) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }
        
        // Récupérer tous les niveaux de stress avec leurs recommandations
        $stressLevels = StressLevel::with('recommendations')
            ->orderBy('min_score', 'asc')
            ->get();
            
        return response()->json($stressLevels);
    }

    /**
     * Obtenir des statistiques sur l'utilisation de l'application
     */
    public function getStatistics()
    {
        // Vérifier que l'utilisateur est un administrateur
        if (!$this->isAdmin()) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }
        
        // Nombre d'utilisateurs
        $userCount = DB::table('users')->count();
        
        // Nombre de questionnaires
        $questionnaireCount = DB::table('questionnaires')->count();
        
        // Nombre de diagnostics
        $diagnosticCount = DB::table('diagnostics')->count();
        
        // Nombre de diagnostics sauvegardés
        $savedDiagnosticCount = DB::table('diagnostics')
            ->where('saved', true)
            ->count();
        
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
    }

    /**
     * Vérifier si l'utilisateur actuel est un administrateur
     */
    private function isAdmin()
    {
        $user = auth()->user();
        return $user && $user->role && $user->role->name === 'admin';
    }
}