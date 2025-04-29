<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Questionnaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class QuestionnaireController extends Controller
{
    public function index()
    {
        $questionnaires = Questionnaire::where('active', true)
            ->with('questions')
            ->get();
            
        return response()->json($questionnaires);
    }

    public function show($id)
    {
        $questionnaire = Questionnaire::with('questions')
            ->findOrFail($id);
            
        return response()->json($questionnaire);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'nb_question' => 'nullable|integer',
            'active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $questionnaire = Questionnaire::create([
            'title' => $request->title,
            'description' => $request->description,
            'nb_question' => $request->nb_question ?? 0,
            'creation_date' => Carbon::now(),
            'last_modification' => Carbon::now(),
            'active' => $request->active ?? true,
        ]);

        return response()->json([
            'message' => 'Questionnaire créé avec succès',
            'questionnaire' => $questionnaire
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'nb_question' => 'nullable|integer',
            'active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $questionnaire = Questionnaire::findOrFail($id);
        
        $questionnaire->update([
            'title' => $request->title,
            'description' => $request->description,
            'nb_question' => $request->nb_question ?? $questionnaire->nb_question,
            'last_modification' => Carbon::now(),
            'active' => $request->has('active') ? $request->active : $questionnaire->active,
        ]);

        return response()->json([
            'message' => 'Questionnaire mis à jour avec succès',
            'questionnaire' => $questionnaire
        ]);
    }

    public function destroy($id)
    {
        $questionnaire = Questionnaire::findOrFail($id);
        $questionnaire->delete();

        return response()->json([
            'message' => 'Questionnaire supprimé avec succès'
        ]);
    }
}