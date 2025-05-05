<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        // Detailed logging
        \Log::info('Attempting password reset for: ' . $request->email);
        
        try {
            $request->validate(['email' => 'required|email']);
            
            // Check if the user exists
            $user = \App\Models\User::where('email', $request->email)->first();
            if (!$user) {
                \Log::info('User not found: ' . $request->email);
                return response()->json(['message' => 'Si votre email existe dans notre système, vous recevrez un lien de réinitialisation de mot de passe.'], 200);
            }
            
            // Send the password reset link
            $status = Password::sendResetLink(
                $request->only('email')
            );
            
            \Log::info('Reset link status for ' . $request->email . ': ' . $status);
            
            return response()->json([
                'message' => 'Si votre email existe dans notre système, vous recevrez un lien de réinitialisation de mot de passe.',
                'status' => $status
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in password reset process: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Une erreur est survenue lors de l\'envoi du lien de réinitialisation.',
                'debug_message' => $e->getMessage()
            ], 500);
        }
    }
}