<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Recommendation;
use App\Models\StressLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RecommendationController extends Controller
{
    /**
     * Récupérer toutes les recommandations pour un niveau de stress spécifique.
     */
    public function indexByStressLevel($stressLevelId)
    {
        // Vérifier que l'utilisateur est un administrateur
        if (!$this->isAdmin()) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }
        
        // Vérifier si le niveau de stress existe
        $stressLevel = StressLevel::findOrFail($stressLevelId);
        
        // Récupérer les recommandations
        $recommendations = $stressLevel->recommendations()->orderBy('order', 'asc')->get();
        
        return response()->json($recommendations);
    }

    /**
     * Créer une nouvelle recommandation.
     */
    public function store(Request $request)
    {
        // Vérifier que l'utilisateur est un administrateur
        if (!$this->isAdmin()) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'stress_level_id' => 'required|exists:stress_levels,id',
            'description' => 'required|string',
            'details' => 'nullable|string',
            'order' => 'required|integer|min:1',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Créer la recommandation
        $recommendation = Recommendation::create([
            'stress_level_id' => $request->stress_level_id,
            'description' => $request->description,
            'details' => $request->details,
            'order' => $request->order,
            'active' => $request->has('active') ? $request->active : true,
        ]);

        return response()->json([
            'message' => 'Recommandation créée avec succès',
            'recommendation' => $recommendation
        ], 201);
    }

    /**
     * Mettre à jour une recommandation existante.
     */
    public function update(Request $request, $id)
    {
        // Vérifier que l'utilisateur est un administrateur
        if (!$this->isAdmin()) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }
        
        $recommendation = Recommendation::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'stress_level_id' => 'required|exists:stress_levels,id',
            'description' => 'required|string',
            'details' => 'nullable|string',
            'order' => 'required|integer|min:1',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Mettre à jour la recommandation
        $recommendation->update([
            'stress_level_id' => $request->stress_level_id,
            'description' => $request->description,
            'details' => $request->details,
            'order' => $request->order,
            'active' => $request->has('active') ? $request->active : $recommendation->active,
        ]);

        return response()->json([
            'message' => 'Recommandation mise à jour avec succès',
            'recommendation' => $recommendation
        ]);
    }

    /**
     * Supprimer une recommandation.
     */
    public function destroy($id)
    {
        // Vérifier que l'utilisateur est un administrateur
        if (!$this->isAdmin()) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }
        
        $recommendation = Recommendation::findOrFail($id);
        $recommendation->delete();

        return response()->json([
            'message' => 'Recommandation supprimée avec succès'
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