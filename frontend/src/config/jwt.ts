// src/config/jwt.ts
export default {
    // Clé utilisée pour stocker le token JWT dans le localStorage
    storageTokenKey: 'token',
    
    // Clé utilisée pour stocker la date d'expiration du token
    storageExpirationKey: 'token_expires_at',
    
    // Temps en minutes avant l'expiration pour tenter un rafraîchissement 
    refreshBeforeExpiry: 5,
    
    // En-tête HTTP utilisé pour l'authentification
    authHeader: 'Authorization',
    
    // Préfixe pour le token dans l'en-tête (Bearer est standard pour JWT)
    tokenPrefix: 'Bearer',
    
    // Route API pour rafraîchir le token
    refreshEndpoint: '/refresh-token',
    
    // Intervalle (en ms) pour vérifier l'expiration du token
    tokenCheckInterval: 60000, // 1 minute
  };