<?php
// app/Http/Controllers/Auth/ForgotPasswordController.php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        // Ajouter des logs pour déboguer
        \Log::info('Tentative de réinitialisation de mot de passe pour: ' . $request->email);
        
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        \Log::info('Résultat de l\'envoi: ' . $status);

        return $status === Password::RESET_LINK_SENT
                    ? response()->json(['message' => 'Le lien de réinitialisation a été envoyé à votre adresse email'], 200)
                    : response()->json(['message' => 'Impossible d\'envoyer l\'email de réinitialisation. Vérifiez votre adresse email.'], 400);
    }
}