import jwtConfig from '../config/jwt';
import { Capacitor } from '@capacitor/core';

interface ApiOptions {
  method?: string;
  headers?: Record<string, string>;
  body?: any;
}

// Function to get the appropriate base URL depending on the platform
function getBaseUrl() {
  if (Capacitor.isNativePlatform()) {
    // L'adresse IP 
    return 'http://192.168.1.154:8000/api';
  } else {
    // Pour le navigateur web
    return 'http://localhost:8000/api';
  }
}

class ApiService {
  private async request<T>(endpoint: string, options: ApiOptions = {}): Promise<T> {
    const url = `${getBaseUrl()}${endpoint}`;
    console.log(`Envoi de la requête à: ${url}`, { method: options.method || 'GET' });

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
      headers
    };
    
    // Ajouter le body si nécessaire
    if (options.body) {
      fetchOptions.body = JSON.stringify(options.body);
      console.log('Corps de la requête:', options.body);
    }
    
    try {
      // Effectuer la requête
      const response = await fetch(url, fetchOptions);
      console.log(`Statut de la réponse: ${response.status}`);

      // Gérer les erreurs d'authentification
      if (response.status === 401) {
        // Vérifier si c'est un token expiré et tenter de le rafraîchir si endpoint n'est pas déjà refresh-token
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
      
      // Extraire le texte de la réponse pour un meilleur débogage
      const responseText = await response.text();
      console.log(`Réponse (premiers 200 caractères):`, responseText.substring(0, 200));
      
      // Vérifier si la réponse est OK
      if (!response.ok) {
        let errorMessage = 'Une erreur inconnue est survenue';
        let errorDetails = '';
        
        try {
          // Essayer de parser le texte comme JSON pour extraire un message d'erreur
          const errorData = JSON.parse(responseText);
          console.log('Données d\'erreur:', errorData);
          
          if (errorData.message) {
            errorMessage = errorData.message;
          }
          
          if (errorData.error) {
            errorDetails = errorData.error;
          }
          
          // Si nous avons des erreurs de validation
          if (errorData.errors) {
            console.log('Erreurs de validation:', errorData.errors);
            const validationErrors = Object.values(errorData.errors).flat();
            if (validationErrors.length > 0) {
              errorMessage = validationErrors.join(', ');
            }
          }
        } catch (e) {
          // Si le texte n'est pas du JSON valide, utiliser le texte brut comme message d'erreur
          if (responseText && responseText.length < 500) {
            errorDetails = responseText;
          }
        }
        
        console.error('Erreur API:', errorMessage, errorDetails ? `(${errorDetails})` : '');
        throw new Error(errorMessage);
      }
      
      // Retourner les données
      try {
        return responseText ? JSON.parse(responseText) : {} as T;
      } catch (e) {
        console.error('Erreur lors du parsing de la réponse JSON:', e, 'Texte brut:', responseText);
        throw new Error('La réponse du serveur n\'est pas au format JSON valide');
      }
    } catch (error) {
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
      console.error('Erreur lors du rafraîchissement du token:', error);
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
    try {
      console.log('PUT request to:', `${getBaseUrl()}${endpoint}`, 'with data:', data);
      
      const response = await this.request<T>(endpoint, {
        method: 'PUT',
        body: data,
        headers
      });
      
      console.log('PUT response:', response);
      return response;
    } catch (error) {
      console.error('Error in PUT request:', error);
      throw error;
    }
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