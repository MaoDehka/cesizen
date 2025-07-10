import jwtConfig from '../config/jwt';
import { Capacitor } from '@capacitor/core';

interface ApiOptions {
  method?: string;
  headers?: Record<string, string>;
  body?: any;
}

function getBaseUrl() {
  const isNative = Capacitor.isNativePlatform();
  
  if (isNative) {
    return 'http://cesizen-prod.chickenkiller.com:8080/api';
  } else {
    // Déterminer l'environnement selon l'URL actuelle (HTTP seulement)
    const hostname = window.location.hostname;
    const protocol = 'http:'; // Force HTTP
    
    // Configuration pour la production
    if (hostname.includes('cesizen-prod.chickenkiller.com')) {
      return `${protocol}//cesizen-prod.chickenkiller.com:8080/api`;
    } else if (hostname.includes('cesizen-dev')) {
      return `${protocol}//cesizen-dev.chickenkiller.com/api`;
    } else if (hostname.includes('cesizen-test')) {
      return `${protocol}//cesizen-test.chickenkiller.com/api`;
    } else if (hostname.includes('localhost') || hostname.includes('127.0.0.1')) {
      return 'http://localhost:8000/api';
    } else {
      // Pour la production, pointer vers le port 8080
      return 'http://cesizen-prod.chickenkiller.com:8080/api';
    }
  }
}

class ApiService {
  private async request<T>(endpoint: string, options: ApiOptions = {}): Promise<T> {
    const baseUrl = getBaseUrl();
    const url = `${baseUrl}${endpoint}`;
    console.log(`🌐 Requête vers: ${url}`, { method: options.method || 'GET' });

    // Ajouter les headers par défaut
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...options.headers
    };
    
    // Ajouter le token d'authentification JWT s'il existe
    const token = localStorage.getItem(jwtConfig.storageTokenKey);
    if (token) {
      headers[jwtConfig.authHeader] = `${jwtConfig.tokenPrefix} ${token}`;
    }
    
    // Préparer les options de la requête
    const fetchOptions: RequestInit = {
      method: options.method || 'GET',
      headers,
      // Credentials pour les cookies de session si nécessaire
      credentials: 'same-origin',
      // Mode CORS pour les requêtes cross-origin
      mode: 'cors',
    };
    
    // Ajouter le body si nécessaire
    if (options.body) {
      fetchOptions.body = JSON.stringify(options.body);
    }
    
    try {
      // Effectuer la requête
      const response = await fetch(url, fetchOptions);
      console.log(`📡 Réponse: ${response.status} ${response.statusText}`);

      // Gérer les erreurs d'authentification
      if (response.status === 401) {
        // Vérifier si c'est un token expiré et tenter de le rafraîchir
        if (endpoint !== jwtConfig.refreshEndpoint) {
          try {
            const refreshResult = await this.refreshToken();
            if (refreshResult) {
              // Relancer la requête originale avec le nouveau token
              return this.request<T>(endpoint, options);
            }
          } catch (refreshError) {
            // Si le rafraîchissement échoue, déconnexion
            this.handleAuthError();
            throw new Error('Session expirée, veuillez vous reconnecter');
          }
        } else {
          // Si c'est déjà une tentative de rafraîchissement, déconnexion
          this.handleAuthError();
          throw new Error('Session expirée, veuillez vous reconnecter');
        }
      }
      
      // Gérer les erreurs serveur
      if (response.status >= 500) {
        console.error('❌ Erreur serveur:', response.status);
        throw new Error(`Erreur du serveur (${response.status}). Veuillez réessayer plus tard.`);
      }
      
      // Extraire le texte de la réponse
      const responseText = await response.text();
      
      // Vérifier si la réponse est OK
      if (!response.ok) {
        let errorMessage = 'Une erreur inconnue est survenue';
        
        try {
          // Essayer de parser le texte comme JSON pour extraire un message d'erreur
          const errorData = JSON.parse(responseText);
          
          if (errorData.message) {
            errorMessage = errorData.message;
          }
          
          // Si nous avons des erreurs de validation
          if (errorData.errors) {
            const validationErrors = Object.values(errorData.errors).flat();
            if (validationErrors.length > 0) {
              errorMessage = validationErrors.join(', ');
            }
          }
        } catch (e) {
          // Si le texte n'est pas du JSON valide, utiliser le statut HTTP
          errorMessage = `Erreur ${response.status}: ${response.statusText}`;
        }
        
        console.error('❌ Erreur API:', errorMessage);
        throw new Error(errorMessage);
      }
      
      // Retourner les données
      try {
        return responseText ? JSON.parse(responseText) : {} as T;
      } catch (e) {
        console.error('❌ Erreur parsing JSON:', e);
        throw new Error('Réponse du serveur invalide');
      }
    } catch (error) {
      // Gérer les erreurs de réseau
      if (error instanceof TypeError && error.message.includes('Failed to fetch')) {
        console.error('❌ Erreur réseau:', error);
        throw new Error('Erreur de connexion. Vérifiez votre connexion internet.');
      }
      
      if (error instanceof Error) {
        throw error;
      }
      throw new Error('Une erreur de réseau est survenue');
    }
  }
  
  // Rafraîchir le token JWT
  private async refreshToken(): Promise<boolean> {
    try {
      const response = await this.post<{token: string, expires_in: number}>(jwtConfig.refreshEndpoint);
      
      if (response.token) {
        // Mettre à jour le token dans le localStorage
        localStorage.setItem(jwtConfig.storageTokenKey, response.token);
        
        // Mettre à jour la date d'expiration
        if (response.expires_in) {
          const expiresAt = new Date().getTime() + response.expires_in * 1000;
          localStorage.setItem(jwtConfig.storageExpirationKey, expiresAt.toString());
        }
        
        return true;
      }
      
      return false;
    } catch (error) {
      console.error('❌ Erreur rafraîchissement token:', error);
      return false;
    }
  }
  
  // Gestion des erreurs d'authentification
  private handleAuthError() {
    localStorage.removeItem(jwtConfig.storageTokenKey);
    localStorage.removeItem(jwtConfig.storageExpirationKey);
    localStorage.removeItem('user');
    window.location.href = '/login';
  }
  
  // Méthodes HTTP
  async get<T>(endpoint: string, headers?: Record<string, string>): Promise<T> {
    return this.request<T>(endpoint, { headers });
  }
  
  async post<T>(endpoint: string, data?: any, headers?: Record<string, string>): Promise<T> {
    return this.request<T>(endpoint, {
      method: 'POST',
      body: data,
      headers
    });
  }
  
  async put<T>(endpoint: string, data?: any, headers?: Record<string, string>): Promise<T> {
    return this.request<T>(endpoint, {
      method: 'PUT',
      body: data,
      headers
    });
  }
  
  async delete<T>(endpoint: string, headers?: Record<string, string>): Promise<T> {
    return this.request<T>(endpoint, {
      method: 'DELETE',
      headers
    });
  }
}

// Créer et exporter une instance de la classe ApiService
const api = new ApiService();
export default api;