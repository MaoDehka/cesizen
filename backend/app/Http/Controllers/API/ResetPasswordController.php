<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class ResetPasswordController extends Controller
{
    public function reset(Request $request)
    {
        Log::info('Tentative de réinitialisation simplifiée pour: ' . $request->email);
        
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => [
                'required',
                'confirmed',
                Password::min(12)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
            ],
        ], [
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères',
            'password.mixed_case' => 'Le mot de passe doit contenir au moins une lettre majuscule et une lettre minuscule',
            'password.letters' => 'Le mot de passe doit contenir au moins une lettre',
            'password.numbers' => 'Le mot de passe doit contenir au moins un chiffre',
            'password.symbols' => 'Le mot de passe doit contenir au moins un caractère spécial',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                return response()->json([
                    'message' => 'Aucun utilisateur trouvé avec cette adresse email'
                ], 404);
            }
            
            // Modifier directement le mot de passe
            $user->password = Hash::make($request->password);
            $user->save();
            
            Log::info('Mot de passe modifié avec succès pour: ' . $user->email);
            
            // Supprimer tout token existant
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();
            
            return response()->json([
                'message' => 'Mot de passe réinitialisé avec succès'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Exception: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Une erreur est survenue lors de la réinitialisation du mot de passe',
                'debug_message' => $e->getMessage()
            ], 500);
        }
    }
}