<!-- src/views/ResetPasswordView.vue -->
<template>
    <div class="reset-password-container">
      <h1>Réinitialisation du mot de passe</h1>
      
      <div v-if="message" :class="['alert', success ? 'alert-success' : 'alert-error']">
        {{ message }}
      </div>
      
      <form @submit.prevent="resetPassword" v-if="!success">
        <input type="hidden" name="token" v-model="token">
        
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
        
        <div class="form-group">
          <label for="password">Nouveau mot de passe</label>
          <input 
            type="password" 
            id="password" 
            v-model="password" 
            required 
            placeholder="Entrez votre nouveau mot de passe"
          />
          <div v-if="errors.password" class="error-message">{{ errors.password }}</div>
        </div>
        
        <div class="form-group">
          <label for="password_confirmation">Confirmation du mot de passe</label>
          <input 
            type="password" 
            id="password_confirmation" 
            v-model="passwordConfirmation" 
            required 
            placeholder="Confirmez votre nouveau mot de passe"
          />
        </div>
        
        <div class="form-actions">
          <button type="submit" class="btn-primary" :disabled="loading">
            {{ loading ? 'Réinitialisation en cours...' : 'Réinitialiser le mot de passe' }}
          </button>
          <router-link to="/login" class="btn-link">Retour à la connexion</router-link>
        </div>
      </form>
    </div>
  </template>
  
  <script lang="ts">
  import { defineComponent, ref, onMounted } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import api from '../services/api';
  
  interface PasswordResetResponse {
    message: string;
  }

  export default defineComponent({
    name: 'ResetPasswordView',
    setup() {
      const route = useRoute();
      const router = useRouter();
      
      const token = ref('');
      const email = ref('');
      const password = ref('');
      const passwordConfirmation = ref('');
      const loading = ref(false);
      const success = ref(false);
      const message = ref('');
      const errors = ref<Record<string, string>>({});
      
      onMounted(() => {
  // Récupérer le token et l'email depuis l'URL
  token.value = route.query.token as string || '';
  
  console.log('Token récupéré:', token.value); // Pour vérifier le token
  
  if (!token.value) {
    message.value = 'Token de réinitialisation invalide.';
  }
});
      
      const resetPassword = async () => {
        if (!token.value) {
          message.value = 'Token de réinitialisation invalide.';
          return;
        }
        
        loading.value = true;
        errors.value = {};
        
        try {
            console.log('Envoi des données de réinitialisation:', {
      token: token.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value
    });
    
            const response = await api.post<PasswordResetResponse>('/reset-password-token', {
          token: token.value,
          email: email.value,
          password: password.value,
          password_confirmation: passwordConfirmation.value
        });
          
          message.value = response.message || 'Votre mot de passe a été réinitialisé avec succès.';
          success.value = true;
          
          // Rediriger vers la page de connexion après 3 secondes
          setTimeout(() => {
            router.push('/login');
          }, 3000);
        } catch (err: any) {
          success.value = false;
          if (err.response && err.response.data) {
            if (err.response.data.errors) {
              errors.value = err.response.data.errors;
            } else {
              message.value = err.response.data.message || 'Une erreur est survenue lors de la réinitialisation du mot de passe.';
            }
          } else {
            message.value = 'Une erreur est survenue lors de la réinitialisation du mot de passe.';
          }
        } finally {
          loading.value = false;
        }
      };
      
      return {
        token,
        email,
        password,
        passwordConfirmation,
        loading,
        message,
        success,
        errors,
        resetPassword
      };
    }
  });
  </script>
  
  <style scoped>
  /* Mêmes styles que pour ForgotPasswordView */
  .reset-password-container {
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