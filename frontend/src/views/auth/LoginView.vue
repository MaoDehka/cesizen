<template>
    <div class="login-container">
      <div class="login-form-card">
        <div class="logo-header">
          <img src="../../assets/logo.jpg" alt="CESIZen Logo" class="logo" />
          <h1>CESIZen</h1>
        </div>
        
        <form @submit.prevent="handleLogin" class="login-form">
          <div class="form-group">
            <label for="email">Email</label>
            <input 
              type="email" 
              id="email" 
              v-model="loginForm.email" 
              placeholder="Email"
              required
            />
          </div>
  
          <div class="form-group">
            <label for="password">Mot de passe</label>
            <input 
              type="password" 
              id="password" 
              v-model="loginForm.password" 
              placeholder="Mot de passe"
              required
            />
          </div>
  
          <div class="form-actions">
            <button type="submit" class="btn-submit" :disabled="loading">
              {{ loading ? 'Connexion en cours...' : 'Se connecter' }}
            </button>
          </div>
  
          <div v-if="error" class="error-message">{{ error }}</div>
  
          <div class="form-footer">
            <!-- Ajoutez ceci dans votre formulaire de connexion -->
<div class="form-group">
  <div class="forgot-password">
    <router-link to="/forgot-password">Mot de passe oublié ?</router-link>
  </div>
</div>
            <router-link to="/register" class="register-link">Créer un compte</router-link>
          </div>
        </form>
      </div>
    </div>
  </template>
  
  <script lang="ts">
  import { defineComponent, ref } from 'vue';
  import { useRouter } from 'vue-router';
  import { useAuthStore } from '../../stores/auth';
  import type { LoginForm } from '../../types';
  
  export default defineComponent({
    name: 'LoginView',
    setup() {
      const router = useRouter();
      const authStore = useAuthStore();
      
      const loginForm = ref<LoginForm>({
        email: '',
        password: ''
      });
      
      const loading = ref(false);
      const error = ref<string | null>(null);
      
      const handleLogin = async () => {
        loading.value = true;
        error.value = null;
        
        try {
          await authStore.login(loginForm.value);
          router.push('/');
        } catch (err) {
          error.value = err as string;
        } finally {
          loading.value = false;
        }
      };
      
      return {
        loginForm,
        loading,
        error,
        handleLogin
      };
    }
  });
  </script>
  
  <style scoped>
  .login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
    background-color: #f5f5f5;
  }
  
  .login-form-card {
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
  
  .register-link {
    display: block;
    margin-top: 10px;
    color: #4CAF50;
    text-decoration: none;
  }
  </style>