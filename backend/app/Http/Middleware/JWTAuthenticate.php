<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use App\Services\JWTService;

class JWTAuthenticate
{
    protected $jwtService;

    public function __construct(JWTService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Récupérer le token
            if (!$token = JWTAuth::parseToken()) {
                return response()->json(['message' => 'Token absent'], 401);
            }

            // Authentifier l'utilisateur
            $user = JWTAuth::authenticate();
            if (!$user) {
                return response()->json(['message' => 'Utilisateur non trouvé'], 404);
            }

            // Vérifier si l'utilisateur est actif
            if (!$user->active) {
                return response()->json(['message' => 'Votre compte a été désactivé'], 403);
            }

        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Token expiré'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'Token invalide'], 401);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token absent'], 401);
        }

        return $next($request);
    }
}