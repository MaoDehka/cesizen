<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\StressLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StressLevelController extends Controller
{
    /**
     * Récupérer tous les niveaux de stress.
     */
    public function index()
    {
        // Vérifier que l'utilisateur est un administrateur
        if (!$this->isAdmin()) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }
        
        $stressLevels = StressLevel::with('recommendations')->orderBy('min_score', 'asc')->get();
        return response()->json($stressLevels);
    }

    /**
     * Afficher un niveau de stress spécifique.
     */
    public function show($id)
    {
        // Vérifier que l'utilisateur est un administrateur
        if (!$this->isAdmin()) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }
        
        $stressLevel = StressLevel::with('recommendations')->findOrFail($id);
        return response()->json($stressLevel);
    }

    /**
     * Créer un nouveau niveau de stress.
     */
    public function store(Request $request)
    {
        // Vérifier que l'utilisateur est un administrateur
        if (!$this->isAdmin()) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'min_score' => 'required|integer|min:0',
            'max_score' => 'required|integer|min:0|gt:min_score',
            'risk_percentage' => 'required|integer|min:0|max:100',
            'description' => 'nullable|string',
            'consequences' => 'nullable|string',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Créer le niveau de stress
        $stressLevel = StressLevel::create([
            'name' => $request->name,
            'min_score' => $request->min_score,
            'max_score' => $request->max_score,
            'risk_percentage' => $request->risk_percentage,
            'description' => $request->description,
            'consequences' => $request->consequences,
            'active' => $request->has('active') ? $request->active : true,
        ]);

        return response()->json([
            'message' => 'Niveau de stress créé avec succès',
            'stress_level' => $stressLevel
        ], 201);
    }

    /**
     * Mettre à jour un niveau de stress existant.
     */
    public function update(Request $request, $id)
    {
        // Vérifier que l'utilisateur est un administrateur
        if (!$this->isAdmin()) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }
        
        $stressLevel = StressLevel::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'min_score' => 'required|integer|min:0',
            'max_score' => 'required|integer|min:0|gt:min_score',
            'risk_percentage' => 'required|integer|min:0|max:100',
            'description' => 'nullable|string',
            'consequences' => 'nullable|string',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Mettre à jour le niveau de stress
        $stressLevel->update([
            'name' => $request->name,
            'min_score' => $request->min_score,
            'max_score' => $request->max_score,
            'risk_percentage' => $request->risk_percentage,
            'description' => $request->description,
            'consequences' => $request->consequences,
            'active' => $request->has('active') ? $request->active : $stressLevel->active,
        ]);

        return response()->json([
            'message' => 'Niveau de stress mis à jour avec succès',
            'stress_level' => $stressLevel->load('recommendations')
        ]);
    }

    /**
     * Supprimer un niveau de stress.
     */
    public function destroy($id)
    {
        // Vérifier que l'utilisateur est un administrateur
        if (!$this->isAdmin()) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }
        
        $stressLevel = StressLevel::findOrFail($id);
        
        // Vérifier s'il y a des diagnostics liés à ce niveau de stress
        $diagnosticsCount = $stressLevel->diagnostics()->count();
        
        if ($diagnosticsCount > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer ce niveau de stress car il est associé à des diagnostics existants',
                'diagnostics_count' => $diagnosticsCount
            ], 409);
        }
        
        // Supprimer d'abord les recommandations associées
        $stressLevel->recommendations()->delete();
        
        // Puis supprimer le niveau de stress
        $stressLevel->delete();

        return response()->json([
            'message' => 'Niveau de stress supprimé avec succès'
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