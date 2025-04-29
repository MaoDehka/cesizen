<template>
    <div class="register-container">
      <div class="register-form-card">
        <div class="logo-header">
          <img src="@/assets/logo.png" alt="CESIZen Logo" class="logo" />
          <h1>CESIZen</h1>
        </div>
        
        <form @submit.prevent="handleRegister" class="register-form">
          <div class="form-group">
            <label for="name">Nom</label>
            <input 
              type="text" 
              id="name" 
              v-model="registerForm.name" 
              placeholder="Votre nom"
              required
            />
          </div>
  
          <div class="form-group">
            <label for="email">Email</label>
            <input 
              type="email" 
              id="email" 
              v-model="registerForm.email" 
              placeholder="Votre email"
              required
            />
          </div>
  
          <div class="form-group">
            <label for="password">Mot de passe</label>
            <input 
              type="password" 
              id="password" 
              v-model="registerForm.password" 
              placeholder="Votre mot de passe"
              required
            />
          </div>
  
          <div class="form-group">
            <label for="password_confirmation">Confirmer le mot de passe</label>
            <input 
              type="password" 
              id="password_confirmation" 
              v-model="registerForm.password_confirmation" 
              placeholder="Confirmez votre mot de passe"
              required
            />
          </div>
  
          <div class="form-actions">
            <button type="submit" class="btn-submit" :disabled="loading">
              {{ loading ? 'Inscription en cours...' : 'S\'inscrire' }}
            </button>
          </div>
  
          <div v-if="error" class="error-message">{{ error }}</div>
  
          <div class="form-footer">
            <p>Déjà inscrit ?</p>
            <router-link to="/login" class="login-link">Se connecter</router-link>
          </div>
        </form>
      </div>
    </div>
  </template>
  
  <script lang="ts">
  import { defineComponent, ref } from 'vue';
  import { useRouter } from 'vue-router';
  import { useAuthStore } from '../../stores/auth';
  import type { RegisterForm } from '../../types';
  
  export default defineComponent({
    name: 'RegisterView',
    setup() {
      const router = useRouter();
      const authStore = useAuthStore();
      
      const registerForm = ref<RegisterForm>({
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
      });
      
      const loading = ref(false);
      const error = ref<string | null>(null);
      
      const handleRegister = async () => {
        loading.value = true;
        error.value = null;
        
        // Validation côté client
        if (registerForm.value.password !== registerForm.value.password_confirmation) {
          error.value = 'Les mots de passe ne correspondent pas';
          loading.value = false;
          return;
        }
        
        try {
          await authStore.register(registerForm.value);
          router.push('/');
        } catch (err) {
          error.value = err as string;
        } finally {
          loading.value = false;
        }
      };
      
      return {
        registerForm,
        loading,
        error,
        handleRegister
      };
    }
  });
  </script>
  
  <style scoped>
  .register-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
    background-color: #f5f5f5;
  }
  
  .register-form-card {
    width: 100%;
    max-width: 400px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 30px;
  }
  
  .logo-header {
    text-align: center;
    margin-bottom: 30px;
  }
  
  .logo {
    width: 60px;
    height: auto;
  }
  
  h1 {
    margin-top: 10px;
    color: #4CAF50;
  }
  
  .form-group {
    margin-bottom: 20px;
  }
  
  label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
  }
  
  input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
  }
  
  .form-actions {
    margin-top: 30px;
  }
  
  .btn-submit {
    width: 100%;
    padding: 12px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s;
  }
  
  .btn-submit:hover {
    background-color: #45a049;
  }
  
  .btn-submit:disabled {
    background-color: #cccccc;
    cursor: not-allowed;
  }
  
  .error-message {
    margin-top: 15px;
    color: #e53935;
    text-align: center;
  }
  
  .form-footer {
    margin-top: 20px;
    text-align: center;
  }
  
  .login-link {
    display: block;
    margin-top: 10px;
    color: #4CAF50;
    text-decoration: none;
  }
  </style>