<?php

namespace App\Services;

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class JWTService
{
    /**
     * Générer un token JWT pour un utilisateur
     *
     * @param \App\Models\User $user L'utilisateur pour lequel générer le token
     * @return string Le token JWT
     */
    public function generateToken(User $user)
    {
        return JWTAuth::fromUser($user);
    }

    /**
     * Valider un token JWT
     *
     * @param string $token Le token à valider
     * @return array|boolean Les données de l'utilisateur ou false si invalide
     */
    public function validateToken(string $token)
    {
        try {
            $user = JWTAuth::authenticate($token);
            
            if (!$user) {
                return false;
            }
            
            return $user;
        } catch (TokenExpiredException $e) {
            return ['error' => 'token_expired', 'message' => 'Token expiré'];
        } catch (TokenInvalidException $e) {
            return ['error' => 'token_invalid', 'message' => 'Token invalide'];
        } catch (JWTException $e) {
            return ['error' => 'token_absent', 'message' => 'Token absent'];
        }
    }

    /**
     * Rafraîchir un token JWT
     *
     * @param string $token Le token à rafraîchir
     * @return string|array Le nouveau token ou une erreur
     */
    public function refreshToken(string $token)
    {
        try {
            return JWTAuth::refresh($token);
        } catch (TokenExpiredException $e) {
            return ['error' => 'token_expired', 'message' => 'Le token ne peut plus être rafraîchi'];
        } catch (JWTException $e) {
            return ['error' => 'token_invalid', 'message' => 'Token invalide'];
        }
    }

    /**
     * Invalider un token JWT
     *
     * @param string $token Le token à invalider
     * @return boolean Succès de l'opération
     */
    public function invalidateToken(string $token)
    {
        try {
            JWTAuth::invalidate($token);
            return true;
        } catch (JWTException $e) {
            return false;
        }
    }

    /**
     * Récupérer l'utilisateur à partir du token
     *
     * @param string $token Le token JWT
     * @return \App\Models\User|null L'utilisateur ou null
     */
    public function getUserFromToken(string $token)
    {
        try {
            return JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return null;
        }
    }
}