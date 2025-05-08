<template>
    <div class="register-container">
      <div class="register-form-card">
        <div class="logo-header">
          <img src="../../assets/logo.jpg" alt="CESIZen Logo" class="logo" />
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
              @input="validatePasswordInput"
            />
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
            <label for="password_confirmation">Confirmer le mot de passe</label>
            <input 
              type="password" 
              id="password_confirmation" 
              v-model="registerForm.password_confirmation" 
              placeholder="Confirmez votre mot de passe"
              required
            />
            <div v-if="passwordsDoNotMatch" class="error-text">
              Les mots de passe ne correspondent pas
            </div>
          </div>
  
          <div class="form-group">
            <label class="checkbox-label">
              <input 
                type="checkbox" 
                v-model="acceptDataPolicy" 
                required
              />
              <span>J'accepte la politique de protection des données</span>
            </label>
            <div class="data-policy-info">
              Vos données sont stockées de façon sécurisée et ne seront jamais partagées avec des tiers.
              <a href="#" @click.prevent="showDataPolicy">En savoir plus</a>
            </div>
          </div>
  
          <div class="form-actions">
            <button type="submit" class="btn-submit" :disabled="loading || !isFormValid">
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

    <!-- Modal pour la politique de données -->
    <div v-if="showDataPolicyModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Politique de protection des données</h2>
          <button @click="closeDataPolicy" class="close-btn">&times;</button>
        </div>
        <div class="modal-body">
          <h3>Traitement de vos données personnelles</h3>
          <p>
            Conformément au Règlement Général sur la Protection des Données (RGPD), 
            nous recueillons et traitons vos données personnelles avec votre consentement 
            et dans le but de vous fournir nos services.
          </p>
          
          <h3>Données collectées</h3>
          <p>
            Nous collectons les informations suivantes : nom, adresse email, et les données 
            relatives à vos diagnostics de stress.
          </p>
          
          <h3>Durée de conservation</h3>
          <p>
            Vos données sont conservées pendant la durée de votre utilisation du service, 
            et jusqu'à 12 mois après votre dernière connexion.
          </p>
          
          <h3>Vos droits</h3>
          <p>
            Vous disposez des droits d'accès, de rectification, d'effacement, de limitation, 
            de portabilité et d'opposition concernant vos données. Pour exercer ces droits, 
            contactez-nous à privacy@cesizen.com.
          </p>
          
          <h3>Sécurité</h3>
          <p>
            Nous mettons en œuvre des mesures de sécurité appropriées pour protéger vos données 
            contre tout accès, modification, divulgation ou destruction non autorisés.
          </p>
        </div>
        <div class="modal-footer">
          <button @click="closeDataPolicy" class="btn-secondary">Fermer</button>
        </div>
      </div>
    </div>
  </template>
  
  <script lang="ts">
  import { defineComponent, ref, computed } from 'vue';
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
      const acceptDataPolicy = ref(false);
      const showDataPolicyModal = ref(false);
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
        
        const password = registerForm.value.password;
        
        // Valider chaque critère individuellement
        passwordValidation.value.minLength = password.length >= 12;
        passwordValidation.value.hasUppercase = /[A-Z]/.test(password);
        passwordValidation.value.hasLowercase = /[a-z]/.test(password);
        passwordValidation.value.hasDigit = /\d/.test(password);
        passwordValidation.value.hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
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
          registerForm.value.password !== '' &&
          registerForm.value.password_confirmation !== '' &&
          registerForm.value.password !== registerForm.value.password_confirmation
        );
      });
      
      // Vérifier si le formulaire est valide dans son ensemble
      const isFormValid = computed(() => {
        return (
          registerForm.value.name !== '' &&
          registerForm.value.email !== '' &&
          isPasswordValid.value &&
          !passwordsDoNotMatch.value &&
          acceptDataPolicy.value
        );
      });
      
      const showDataPolicy = () => {
        showDataPolicyModal.value = true;
      };
      
      const closeDataPolicy = () => {
        showDataPolicyModal.value = false;
      };
      
      const handleRegister = async () => {
        if (!isFormValid.value) {
          // Afficher un message d'erreur approprié
          if (!isPasswordValid.value) {
            error.value = 'Le mot de passe ne répond pas aux exigences de sécurité';
          } else if (passwordsDoNotMatch.value) {
            error.value = 'Les mots de passe ne correspondent pas';
          } else if (!acceptDataPolicy.value) {
            error.value = 'Vous devez accepter la politique de protection des données';
          }
          return;
        }
        
        loading.value = true;
        error.value = null;
        
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
        acceptDataPolicy,
        showPasswordRequirements,
        passwordValidation,
        passwordsDoNotMatch,
        isFormValid,
        showDataPolicyModal,
        validatePasswordInput,
        showDataPolicy,
        closeDataPolicy,
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
    max-width: 500px;
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
  
  .checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
  }
  
  input[type="text"],
  input[type="email"],
  input[type="password"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
  }
  
  input[type="checkbox"] {
    margin-right: 8px;
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
  
  .data-policy-info {
    font-size: 12px;
    color: #757575;
    margin-top: 5px;
  }
  
  .data-policy-info a {
    color: #4CAF50;
    text-decoration: none;
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
  
  .error-message, .error-text {
    margin-top: 5px;
    color: #e53935;
    font-size: 14px;
  }
  
  .error-message {
    margin-top: 15px;
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
  
  /* Modal styles */
  .modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
  }
  
  .modal-content {
    background-color: white;
    border-radius: 8px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  }
  
  .modal-header {
    padding: 15px;
    background-color: #f5f5f5;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #eee;
  }
  
  .modal-header h2 {
    margin: 0;
    font-size: 20px;
    color: #333;
  }
  
  .close-btn {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
  }
  
  .modal-body {
    padding: 20px;
  }
  
  .modal-body h3 {
    margin-top: 20px;
    margin-bottom: 10px;
    font-size: 18px;
    color: #333;
  }
  
  .modal-body p {
    margin: 0 0 15px;
    line-height: 1.5;
    color: #555;
  }
  
  .modal-footer {
    padding: 15px;
    background-color: #f5f5f5;
    display: flex;
    justify-content: flex-end;
    border-top: 1px solid #eee;
  }
  
  .btn-secondary {
    padding: 8px 15px;
    background-color: #f1f1f1;
    color: #333;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
  }
  
  @media (max-width: 768px) {
    .register-form-card {
      padding: 20px;
    }
  }
  </style>
