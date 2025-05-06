// src/stores/auth.ts
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { User, LoginForm, RegisterForm } from '../types'
import api from '../services/api'
import jwtConfig from '../config/jwt'

interface AuthResponse {
  user: User;
  token: string;
  token_type: string;
  expires_in: number;
  message?: string;
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Chargement initial des données d'authentification depuis le localStorage
  const initAuth = () => {
    const storedToken = localStorage.getItem(jwtConfig.storageTokenKey)
    const storedUser = localStorage.getItem('user')
    
    if (storedToken && storedUser) {
      token.value = storedToken
      user.value = JSON.parse(storedUser)
    }
  }

  // Propriétés calculées
  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.role?.name === 'admin')

  // Actions
  const login = async (credentials: LoginForm) => {
    loading.value = true
    error.value = null
    
    try {
      // Utiliser le typage avec notre API Fetch
      const response = await api.post<AuthResponse>('/login', credentials)
      
      // Avec notre API Fetch, la réponse est directement l'objet JSON retourné
      token.value = response.token
      user.value = response.user
      
      // Stocker le token JWT et les informations utilisateur
      localStorage.setItem(jwtConfig.storageTokenKey, response.token)
      localStorage.setItem('user', JSON.stringify(response.user))
      
      // Calculer le moment d'expiration du token
      if (response.expires_in) {
        const expiresAt = new Date().getTime() + response.expires_in * 1000
        localStorage.setItem(jwtConfig.storageExpirationKey, expiresAt.toString())
      }
      
      return response
    } catch (err: any) {
      error.value = err.message || 'Une erreur est survenue'
      throw error.value
    } finally {
      loading.value = false
    }
  }

  const register = async (userData: RegisterForm) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await api.post<AuthResponse>('/register', userData)
      
      token.value = response.token
      user.value = response.user
      
      // Stocker le token JWT et les informations utilisateur
      localStorage.setItem(jwtConfig.storageTokenKey, response.token)
      localStorage.setItem('user', JSON.stringify(response.user))
      
      // Calculer le moment d'expiration du token
      if (response.expires_in) {
        const expiresAt = new Date().getTime() + response.expires_in * 1000
        localStorage.setItem(jwtConfig.storageExpirationKey, expiresAt.toString())
      }
      
      return response
    } catch (err: any) {
      error.value = err.message || 'Une erreur est survenue'
      throw error.value
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    loading.value = true
    
    try {
      await api.post('/logout')
    } catch (err) {
      console.error('Erreur lors de la déconnexion:', err)
    } finally {
      user.value = null
      token.value = null
      localStorage.removeItem(jwtConfig.storageTokenKey)
      localStorage.removeItem(jwtConfig.storageExpirationKey)
      localStorage.removeItem('user')
      loading.value = false
    }
  }

  const refreshToken = async () => {
    try {
      const response = await api.post<AuthResponse>(jwtConfig.refreshEndpoint)
      
      if (response.token) {
        token.value = response.token
        localStorage.setItem(jwtConfig.storageTokenKey, response.token)
        
        // Mettre à jour la date d'expiration
        if (response.expires_in) {
          const expiresAt = new Date().getTime() + response.expires_in * 1000
          localStorage.setItem(jwtConfig.storageExpirationKey, expiresAt.toString())
        }
        
        return true
      }
      return false
    } catch (err) {
      console.error('Erreur lors du rafraîchissement du token:', err)
      return false
    }
  }

  const checkTokenExpiration = () => {
    const expiresAtStr = localStorage.getItem(jwtConfig.storageExpirationKey)
    
    if (expiresAtStr) {
      const expiresAt = parseInt(expiresAtStr)
      const now = new Date().getTime()
      
      // Si le token expire dans moins de X minutes (défini dans jwtConfig), le rafraîchir
      if (expiresAt - now < jwtConfig.refreshBeforeExpiry * 60 * 1000) {
        refreshToken()
      }
    }
  }

  const fetchUser = async () => {
    if (!token.value) return null
    
    try {
      interface UserResponse {
        user: User;
      }
      
      const response = await api.get<UserResponse>('/user')
      user.value = response.user
      return user.value
    } catch (err: any) {
      // Si l'erreur est due à un token invalide, déconnecter l'utilisateur
      if (err.message.includes('Session expirée')) {
        logout()
      }
      return null
    }
  }

  // Initialiser le store
  initAuth()

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    isAdmin,
    login,
    register,
    logout,
    refreshToken,
    checkTokenExpiration,
    fetchUser
  }
})