// src/services/api.ts

interface ApiOptions {
  method?: string;
  headers?: Record<string, string>;
  body?: any;
}

const BASE_URL = 'http://localhost:8000/api';

class ApiService {
  private async request<T>(endpoint: string, options: ApiOptions = {}): Promise<T> {
    const url = `${BASE_URL}${endpoint}`;
    console.log(`Envoi de la requête à: ${url}`, { method: options.method || 'GET' });

    // Ajouter les headers par défaut
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...options.headers
    };
    
    // Ajouter le token d'authentification s'il existe
    const token = localStorage.getItem('token');
    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }
    
    // Préparer les options de la requête
    const fetchOptions: RequestInit = {
      method: options.method || 'GET',
      headers,
      credentials: 'include' // Pour les cookies CSRF avec Sanctum
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
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        window.location.href = '/login';
        throw new Error('Session expirée, veuillez vous reconnecter');
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
      console.log('PUT request to:', `${BASE_URL}${endpoint}`, 'with data:', data);
      
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