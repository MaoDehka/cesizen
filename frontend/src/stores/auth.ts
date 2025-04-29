// src/stores/auth.ts
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { User, LoginForm, RegisterForm } from '../types'
import api from '../services/api'

interface AuthResponse {
  user: User;
  token: string;
  message?: string;
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Chargement initial des données d'authentification depuis le localStorage
  const initAuth = () => {
    const storedToken = localStorage.getItem('token')
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
      
      localStorage.setItem('token', response.token)
      localStorage.setItem('user', JSON.stringify(response.user))
      
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
      
      localStorage.setItem('token', response.token)
      localStorage.setItem('user', JSON.stringify(response.user))
      
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
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      loading.value = false
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
    fetchUser
  }
})