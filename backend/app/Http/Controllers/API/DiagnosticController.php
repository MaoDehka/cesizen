<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Diagnostic;
use App\Models\Question;
use App\Models\Response;
use App\Models\StressLevel;
use App\Models\Questionnaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DiagnosticController extends Controller
{
    public function index(Request $request)
    {
        $diagnostics = Diagnostic::where('user_id', $request->user()->id)
            ->with('questionnaire')
            ->orderBy('diagnostic_date', 'desc')
            ->get();
            
        return response()->json($diagnostics);
    }

    public function show(Request $request, $id)
    {
        $diagnostic = Diagnostic::with(['questionnaire', 'user'])->findOrFail($id);
        
        // Vérifier si l'utilisateur a accès à ce diagnostic
        if ($diagnostic->user_id !== $request->user()->id && !$request->user()->role->name === 'admin') {
            return response()->json([
                'message' => 'Vous n\'êtes pas autorisé à accéder à ce diagnostic'
            ], 403);
        }
        
        // Enrichir le diagnostic avec les données du niveau de stress
        $stressLevel = $diagnostic->stress_level_model;
        $recommendations = $diagnostic->recommendations;
        
        $result = [
            'diagnostic' => $diagnostic,
            'stress_level_details' => $stressLevel,
            'recommendations' => $recommendations
        ];
        
        return response()->json($result);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'questionnaire_id' => 'required|exists:questionnaires,id',
            'questions' => 'required|array',
            'questions.*' => 'required|exists:questions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Récupérer le questionnaire
        $questionnaire = Questionnaire::findOrFail($request->questionnaire_id);

        // Calculer le score total
        $totalScore = 0;
        $selectedQuestions = [];
        
        foreach ($request->questions as $questionId) {
            $question = Question::find($questionId);
            if ($question && $question->questionnaire_id === (int) $request->questionnaire_id) {
                $totalScore += $question->response_score;
                $selectedQuestions[] = $questionId;
                
                // Enregistrer la réponse
                Response::create([
                    'user_id' => $request->user()->id,
                    'question_id' => $questionId,
                    'reponse' => 'Oui',
                    'date' => Carbon::now(),
                ]);
            }
        }

        // Déterminer le niveau de stress en fonction du score
        $stressLevel = StressLevel::determineFromScore($totalScore);
        
        if (!$stressLevel) {
            // Fallback basé sur l'échelle originale de Holmes et Rahe si aucun niveau configuré
            if ($totalScore < 150) {
                $stressLevelName = 'Faible';
                $consequences = 'Votre niveau de stress est faible. Le risque de développer des problèmes de santé liés au stress est limité.';
                $advices = 'Continuez à maintenir de bonnes habitudes de vie pour préserver votre équilibre.';
            } elseif ($totalScore <= 300) {
                $stressLevelName = 'Modéré';
                $consequences = 'Votre niveau de stress est modéré. Le risque de problèmes de santé liés au stress est accru, avec environ 50% de probabilité de développer des troubles dans les deux prochaines années.';
                $advices = 'Prenez des mesures pour réduire votre stress quotidien et adoptez des techniques de relaxation régulières.';
            } else {
                $stressLevelName = 'Élevé';
                $consequences = 'Votre niveau de stress est élevé. Le risque de développer des problèmes de santé liés au stress est très important, avec environ 80% de probabilité de développer des troubles dans l\'année à venir.';
                $advices = 'Il est recommandé de consulter un professionnel de la santé et de prendre des mesures immédiates pour gérer votre stress.';
            }
        } else {
            $stressLevelName = $stressLevel->name;
            $consequences = $stressLevel->consequences;
            $advices = $stressLevel->recommendations->pluck('description')->implode(', ');
        }

        // Créer le diagnostic
        $diagnostic = Diagnostic::create([
            'user_id' => $request->user()->id,
            'questionnaire_id' => $request->questionnaire_id,
            'score_total' => $totalScore,
            'stress_level' => $stressLevelName,
            'diagnostic_date' => Carbon::now(),
            'consequences' => $consequences,
            'advices' => $advices,
        ]);

        // Enrichir la réponse avec les détails du niveau de stress et les recommandations
        $result = [
            'message' => 'Diagnostic créé avec succès',
            'diagnostic' => $diagnostic,
            'stress_level_details' => $stressLevel,
            'recommendations' => $stressLevel ? $stressLevel->recommendations : []
        ];
        
        return response()->json($result, 201);
    }

    public function update(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'consequences' => 'nullable|string',
        'advices' => 'nullable|string',
        'saved' => 'nullable|boolean',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $diagnostic = Diagnostic::findOrFail($id);
    
    $diagnostic->update([
        'consequences' => $request->consequences ?? $diagnostic->consequences,
        'advices' => $request->advices ?? $diagnostic->advices,
        'saved' => $request->has('saved') ? $request->saved : $diagnostic->saved,
    ]);

    return response()->json([
        'message' => 'Diagnostic mis à jour avec succès',
        'diagnostic' => $diagnostic
    ]);
}

public function saveDiagnostic(Request $request, $id)
{
    try {
        $diagnostic = Diagnostic::findOrFail($id);
        
        $diagnostic->saved = true;
        $diagnostic->save();
        
        return response()->json([
            'message' => 'Diagnostic sauvegardé avec succès',
            'diagnostic' => $diagnostic
        ]);
    } catch (\Exception $e) {
        // Log détaillé de l'erreur
        \Log::error('Erreur lors de la sauvegarde du diagnostic: ' . $e->getMessage());
        
        return response()->json([
            'message' => 'Une erreur est survenue lors de la sauvegarde du diagnostic',
            'error' => $e->getMessage()
        ], 500);
    }
}

// Dans DiagnosticController.php
public function saveToHistory(Request $request, $id)
{
    try {
        $diagnostic = Diagnostic::findOrFail($id);
        
        // Vérification des autorisations
        if ($diagnostic->user_id !== $request->user()->id && $request->user()->role->name !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }
        
        $diagnostic->saved = true;
        $diagnostic->save();
        
        return response()->json([
            'message' => 'Diagnostic sauvegardé avec succès',
            'diagnostic' => $diagnostic
        ]);
    } catch (\Exception $e) {
        Log::error('Error saving diagnostic', [
            'id' => $id,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'message' => 'Une erreur est survenue lors de la sauvegarde du diagnostic',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function destroy($id)
    {
        $diagnostic = Diagnostic::findOrFail($id);
        $diagnostic->delete();

        return response()->json([
            'message' => 'Diagnostic supprimé avec succès'
        ]);
    }
}