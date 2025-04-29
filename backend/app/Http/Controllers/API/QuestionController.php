<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Questionnaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class QuestionController extends Controller
{
    public function index()
    {
        $questions = Question::with('questionnaire')->get();
        return response()->json($questions);
    }

    public function show($id)
    {
        $question = Question::with('questionnaire')->findOrFail($id);
        return response()->json($question);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'questionnaire_id' => 'required|exists:questionnaires,id',
            'response_text' => 'required|string',
            'response_score' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $question = Question::create([
            'questionnaire_id' => $request->questionnaire_id,
            'response_text' => $request->response_text,
            'response_score' => $request->response_score,
            'date_response' => Carbon::now(),
        ]);

        // Mettre à jour le nombre de questions du questionnaire
        $questionnaire = Questionnaire::find($request->questionnaire_id);
        $questionnaire->nb_question = $questionnaire->questions()->count();
        $questionnaire->last_modification = Carbon::now();
        $questionnaire->save();

        return response()->json([
            'message' => 'Question créée avec succès',
            'question' => $question
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'questionnaire_id' => 'nullable|exists:questionnaires,id',
            'response_text' => 'required|string',
            'response_score' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $question = Question::findOrFail($id);
        
        $oldQuestionnaireId = $question->questionnaire_id;
        
        $question->update([
            'questionnaire_id' => $request->questionnaire_id ?? $question->questionnaire_id,
            'response_text' => $request->response_text,
            'response_score' => $request->response_score,
            'date_response' => Carbon::now(),
        ]);

        // Mettre à jour les questionnaires concernés
        if ($request->questionnaire_id && $request->questionnaire_id != $oldQuestionnaireId) {
            $oldQuestionnaire = Questionnaire::find($oldQuestionnaireId);
            $newQuestionnaire = Questionnaire::find($request->questionnaire_id);
            
            $oldQuestionnaire->nb_question = $oldQuestionnaire->questions()->count();
            $oldQuestionnaire->last_modification = Carbon::now();
            $oldQuestionnaire->save();
            
            $newQuestionnaire->nb_question = $newQuestionnaire->questions()->count();
            $newQuestionnaire->last_modification = Carbon::now();
            $newQuestionnaire->save();
        } else {
            $questionnaire = Questionnaire::find($question->questionnaire_id);
            $questionnaire->last_modification = Carbon::now();
            $questionnaire->save();
        }

        return response()->json([
            'message' => 'Question mise à jour avec succès',
            'question' => $question
        ]);
    }

    public function destroy($id)
    {
        $question = Question::findOrFail($id);
        $questionnaireId = $question->questionnaire_id;
        
        $question->delete();

        // Mettre à jour le nombre de questions du questionnaire
        $questionnaire = Questionnaire::find($questionnaireId);
        $questionnaire->nb_question = $questionnaire->questions()->count();
        $questionnaire->last_modification = Carbon::now();
        $questionnaire->save();

        return response()->json([
            'message' => 'Question supprimée avec succès'
        ]);
    }
}