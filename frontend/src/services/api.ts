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
      }
      
      // Effectuer la requête
      const response = await fetch(url, fetchOptions);
      
      // Gérer les erreurs d'authentification
      if (response.status === 401) {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        window.location.href = '/login';
        throw new Error('Session expirée, veuillez vous reconnecter');
      }
      
      // Vérifier si la réponse est OK
      if (!response.ok) {
        const error = await response.json().catch(() => ({ message: 'Une erreur inconnue est survenue' }));
        throw new Error(error.message || 'Une erreur est survenue');
      }
      
      // Retourner les données
      return await response.json();
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