<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ContentController extends Controller
{
    /**
     * Récupérer tous les contenus (pour l'admin)
     */
    public function index()
    {
        try {
            // Vérifier si l'utilisateur est admin
            if (!$this->isAdmin()) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }
            
            $contents = Content::orderBy('page')->get();
            return response()->json($contents);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des contenus', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Une erreur est survenue lors du chargement des contenus', 'error' => $e->getMessage()], 500);
        }
    }

/**
 * Récupérer un contenu spécifique par identifiant de page (public)
 */
public function getByPage($page)
{
    try {
        $content = Content::where('page', $page)
            ->where('active', true)
            ->firstOrFail();
            
        return response()->json($content);
    } catch (\Exception $e) {
        Log::error('Erreur lors du chargement du contenu', ['page' => $page, 'error' => $e->getMessage()]);
        return response()->json(['message' => 'Contenu non trouvé', 'error' => $e->getMessage()], 404);
    }
}

    /**
     * Récupérer un contenu spécifique par ID (pour l'admin)
     */
    public function show($id)
    {
        try {
            // Vérifier si l'utilisateur est admin
            if (!$this->isAdmin()) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }
            
            $content = Content::findOrFail($id);
            return response()->json($content);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement du contenu', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Une erreur est survenue lors du chargement du contenu', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mettre à jour un contenu existant
     */
    public function update(Request $request, $id)
    {
        try {
            // Vérifier si l'utilisateur est admin
            if (!$this->isAdmin()) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }
            
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $content = Content::findOrFail($id);
            
            $content->update([
                'title' => $request->title,
                'content' => $request->content,
                'active' => $request->has('active') ? $request->active : $content->active,
            ]);

            return response()->json([
                'message' => 'Contenu mis à jour avec succès',
                'content' => $content
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du contenu', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Une erreur est survenue lors de la mise à jour du contenu', 'error' => $e->getMessage()], 500);
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