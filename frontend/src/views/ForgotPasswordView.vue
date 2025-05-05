<template>
    <div class="forgot-password-container">
      <h1>Réinitialisation du mot de passe</h1>
      
      <div v-if="message" :class="['alert', success ? 'alert-success' : 'alert-error']">
        {{ message }}
      </div>
      
      <form @submit.prevent="sendResetLink" v-if="!success">
        <div class="form-group">
          <label for="email">Email</label>
          <input 
            type="email" 
            id="email" 
            v-model="email" 
            required 
            placeholder="Entrez votre adresse email"
          />
          <div v-if="errors.email" class="error-message">{{ errors.email }}</div>
        </div>
        
        <div class="form-actions">
          <button type="submit" class="btn-primary" :disabled="loading">
            {{ loading ? 'Envoi en cours...' : 'Envoyer le lien de réinitialisation' }}
          </button>
          <router-link to="/login" class="btn-link">Retour à la connexion</router-link>
        </div>
      </form>
    </div>
  </template>
  
  <script lang="ts">
  import { defineComponent, ref } from 'vue';
  import api from '../services/api';
  
  interface PasswordResetResponse {
  message: string;
 }

  export default defineComponent({
    name: 'ForgotPasswordView',
    setup() {
      const email = ref('');
      const loading = ref(false);
      const success = ref(false);
      const message = ref('');
      const errors = ref<Record<string, string>>({});
      
      const sendResetLink = async () => {
        loading.value = true;
        errors.value = {};
        
        try {
            const response = await api.post<PasswordResetResponse>('/forgot-password', { email: email.value });
                message.value = response.message || 'Un lien de réinitialisation a été envoyé à votre adresse email.';
          success.value = true;
        } catch (err: any) {
          success.value = false;
          if (err.response && err.response.data) {
            if (err.response.data.errors) {
              errors.value = err.response.data.errors;
            } else {
              message.value = err.response.data.message || 'Une erreur est survenue lors de l\'envoi du lien de réinitialisation.';
            }
          } else {
            message.value = 'Une erreur est survenue lors de l\'envoi du lien de réinitialisation.';
          }
        } finally {
          loading.value = false;
        }
      };
      
      return {
        email,
        loading,
        message,
        success,
        errors,
        sendResetLink
      };
    }
  });
  </script>
  
  <style scoped>
  .forgot-password-container {
    max-width: 400px;
    margin: 0 auto;
    padding: 20px;
  }
  
  h1 {
    margin-bottom: 20px;
    text-align: center;
  }
  
  .form-group {
    margin-bottom: 15px;
  }
  
  label {
    display: block;
    margin-bottom: 5px;
  }
  
  input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
  }
  
  .error-message {
    color: #dc3545;
    font-size: 0.85rem;
    margin-top: 5px;
  }
  
  .alert {
    padding: 12px;
    border-radius: 4px;
    margin-bottom: 20px;
  }
  
  .alert-success {
    background-color: #d4edda;
    color: #155724;
  }
  
  .alert-error {
    background-color: #f8d7da;
    color: #721c24;
  }
  
  .form-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }
  
  .btn-primary {
    background-color: #4CAF50;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 4px;
    cursor: pointer;
  }
  
  .btn-primary:disabled {
    background-color: #9eca9f;
    cursor: not-allowed;
  }
  
  .btn-link {
    text-align: center;
    color: #4CAF50;
    text-decoration: none;
  }
  
  @media (min-width: 576px) {
    .form-actions {
      flex-direction: row;
      justify-content: space-between;
    }
  }
  </style>