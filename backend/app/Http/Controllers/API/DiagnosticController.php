<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Diagnostic;
use App\Models\Question;
use App\Models\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DiagnosticController extends Controller
{
    public function index(Request $request)
    {
        $diagnostics = Diagnostic::where('user_id', $request->user()->id)
            ->orderBy('diagnostic_date', 'desc')
            ->get();
            
        return response()->json($diagnostics);
    }

    public function show(Request $request, $id)
    {
        $diagnostic = Diagnostic::findOrFail($id);
        
        // Vérifier si l'utilisateur a accès à ce diagnostic
        if ($diagnostic->user_id !== $request->user()->id && !$request->user()->role->name === 'admin') {
            return response()->json([
                'message' => 'Vous n\'êtes pas autorisé à accéder à ce diagnostic'
            ], 403);
        }
        
        return response()->json($diagnostic);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'questions' => 'required|array',
            'questions.*' => 'required|exists:questions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Calculer le score total
        $totalScore = 0;
        foreach ($request->questions as $questionId) {
            $question = Question::find($questionId);
            if ($question) {
                $totalScore += $question->response_score;
                
                // Enregistrer la réponse
                Response::create([
                    'user_id' => $request->user()->id,
                    'question_id' => $questionId,
                    'reponse' => 'Oui',
                    'date' => Carbon::now(),
                ]);
            }
        }

        // Déterminer le niveau de stress
        $stressLevel = $this->determineStressLevel($totalScore);
        
        // Générer les conséquences et conseils
        list($consequences, $advices) = $this->generateFeedback($stressLevel);

        // Créer le diagnostic
        $diagnostic = Diagnostic::create([
            'user_id' => $request->user()->id,
            'score_total' => $totalScore,
            'stress_level' => $stressLevel,
            'diagnostic_date' => Carbon::now(),
            'consequences' => $consequences,
            'advices' => $advices,
        ]);

        return response()->json([
            'message' => 'Diagnostic créé avec succès',
            'diagnostic' => $diagnostic
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'consequences' => 'nullable|string',
            'advices' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $diagnostic = Diagnostic::findOrFail($id);
        
        $diagnostic->update([
            'consequences' => $request->consequences ?? $diagnostic->consequences,
            'advices' => $request->advices ?? $diagnostic->advices,
        ]);

        return response()->json([
            'message' => 'Diagnostic mis à jour avec succès',
            'diagnostic' => $diagnostic
        ]);
    }

    public function destroy($id)
    {
        $diagnostic = Diagnostic::findOrFail($id);
        $diagnostic->delete();

        return response()->json([
            'message' => 'Diagnostic supprimé avec succès'
        ]);
    }

    private function determineStressLevel($score)
    {
        if ($score < 150) {
            return 'Faible';
        } elseif ($score < 300) {
            return 'Modéré';
        } else {
            return 'Élevé';
        }
    }

    private function generateFeedback($stressLevel)
    {
        $consequences = '';
        $advices = '';

        switch ($stressLevel) {
            case 'Faible':
                $consequences = 'Votre niveau de stress est faible. Cela indique que vous êtes dans une période relativement stable de votre vie.';
                $advices = 'Continuez à maintenir de bonnes habitudes de vie. Pratiquez régulièrement une activité physique et maintenez un sommeil de qualité.';
                break;
            case 'Modéré':
                $consequences = 'Votre niveau de stress est modéré. Le risque de problèmes de santé liés au stress est accru, avec environ 50% de probabilité de développer des troubles dans les deux prochaines années.';
                $advices = 'Prenez le temps de vous détendre régulièrement. Pratiquez des exercices de relaxation comme la méditation ou le yoga. Identifiez les sources de stress dans votre vie et cherchez des moyens de les réduire.';
                break;
            case 'Élevé':
                $consequences = 'Votre niveau de stress est élevé. Le risque de développer des problèmes de santé liés au stress est très important, avec environ 80% de probabilité de développer des troubles dans l\'année à venir.';
                $advices = 'Il est recommandé de consulter un professionnel de la santé pour vous aider à gérer votre stress. Prenez des mesures immédiates pour réduire les sources de stress dans votre vie. Accordez une attention particulière à votre sommeil, votre alimentation et pratiquez régulièrement des exercices de relaxation.';
                break;
        }

        return [$consequences, $advices];
    }
}