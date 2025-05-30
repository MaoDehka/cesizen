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
            @input="validatePasswordInput"
          />
          <div v-if="errors.password" class="error-message">{{ errors.password }}</div>
          
          <div class="password-requirements" :class="{ 'requirements-visible': showPasswordRequirements }">
            <h4>Votre mot de passe doit contenir :</h4>
            <ul>
              <li :class="{ valid: passwordValidation.minLength }">Au moins 12 caractères</li>
              <li :class="{ valid: passwordValidation.hasUppercase }">Au moins une lettre majuscule</li>
              <li :class="{ valid: passwordValidation.hasLowercase }">Au moins une lettre minuscule</li>
              <li :class="{ valid: passwordValidation.hasDigit }">Au moins un chiffre</li>
              <li :class="{ valid: passwordValidation.hasSpecialChar }">Au moins un caractère spécial (!@#$%^&*(),.?":{}|<>)</li>
            </ul>
          </div>
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
          <div v-if="passwordsDoNotMatch" class="error-message">
            Les mots de passe ne correspondent pas
          </div>
        </div>
        
        <div class="form-actions">
          <button type="submit" class="btn-primary" :disabled="loading || !isFormValid">
            {{ loading ? 'Réinitialisation en cours...' : 'Réinitialiser le mot de passe' }}
          </button>
          <router-link to="/login" class="btn-link">Retour à la connexion</router-link>
        </div>
      </form>

      <div v-if="success" class="success-message">
        <p>Votre mot de passe a été réinitialisé avec succès.</p>
        <p>Vous allez être redirigé vers la page de connexion dans quelques secondes...</p>
        <router-link to="/login" class="btn-link">Aller à la connexion</router-link>
      </div>
    </div>
  </template>
  
  <script lang="ts">
  import { defineComponent, ref, computed, onMounted, watch } from 'vue';
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
      const showPasswordRequirements = ref(false);
      
      // Validation du mot de passe
      const passwordValidation = ref({
        minLength: false,
        hasUppercase: false,
        hasLowercase: false,
        hasDigit: false,
        hasSpecialChar: false
      });
      
      const validatePasswordInput = () => {
        // Afficher les exigences de mot de passe dès que l'utilisateur commence à taper
        showPasswordRequirements.value = true;
        
        const pwd = password.value;
        
        // Valider chaque critère individuellement
        passwordValidation.value.minLength = pwd.length >= 12;
        passwordValidation.value.hasUppercase = /[A-Z]/.test(pwd);
        passwordValidation.value.hasLowercase = /[a-z]/.test(pwd);
        passwordValidation.value.hasDigit = /\d/.test(pwd);
        passwordValidation.value.hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(pwd);
      };
      
      // Vérifier si le mot de passe est valide
      const isPasswordValid = computed(() => {
        return (
          passwordValidation.value.minLength &&
          passwordValidation.value.hasUppercase &&
          passwordValidation.value.hasLowercase &&
          passwordValidation.value.hasDigit &&
          passwordValidation.value.hasSpecialChar
        );
      });
      
      // Vérifier si les mots de passe correspondent
      const passwordsDoNotMatch = computed(() => {
        return (
          password.value !== '' &&
          passwordConfirmation.value !== '' &&
          password.value !== passwordConfirmation.value
        );
      });
      
      // Vérifier si le formulaire est valide dans son ensemble
      const isFormValid = computed(() => {
        return (
          email.value !== '' &&
          isPasswordValid.value &&
          !passwordsDoNotMatch.value
        );
      });
      
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
        
        if (!isFormValid.value) {
          if (!isPasswordValid.value) {
            message.value = 'Le mot de passe ne répond pas aux exigences de sécurité';
          } else if (passwordsDoNotMatch.value) {
            message.value = 'Les mots de passe ne correspondent pas';
          }
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
        showPasswordRequirements,
        passwordValidation,
        passwordsDoNotMatch,
        isFormValid,
        validatePasswordInput,
        resetPassword
      };
    }
  });
  </script>
  
  <style scoped>
  /* Mêmes styles que pour ForgotPasswordView avec ajouts pour les exigences de mot de passe */
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
  
  .password-requirements {
    display: none;
    margin-top: 10px;
    padding: 10px;
    background-color: #f9f9f9;
    border-radius: 4px;
    font-size: 14px;
  }
  
  .requirements-visible {
    display: block;
  }
  
  .password-requirements h4 {
    margin-top: 0;
    margin-bottom: 8px;
    font-size: 14px;
  }
  
  .password-requirements ul {
    margin: 0;
    padding-left: 20px;
  }
  
  .password-requirements li {
    margin-bottom: 5px;
    color: #757575;
  }
  
  .password-requirements li.valid {
    color: #4CAF50;
  }
  
  .password-requirements li.valid::before {
    content: '✓ ';
  }
  
  .success-message {
    background-color: #d4edda;
    color: #155724;
    padding: 15px;
    border-radius: 4px;
    text-align: center;
    margin-bottom: 20px;
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