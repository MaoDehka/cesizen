<template>
  <div class="app-container">
    <header class="app-header">
      <div class="logo-container">
        <img src="../assets/logo.jpg" alt="CESIZen" class="logo" />
        <h1 class="app-title">CESIZen</h1>
      </div>
      <nav class="nav-menu" v-if="isAuthenticated">
        <router-link to="/" class="nav-link">Accueil</router-link>
        <router-link to="/diagnostics" class="nav-link">Diagnostics</router-link>
        <a href="#" @click.prevent="logout" class="nav-link">Déconnexion</a>
        <router-link v-if="isAdmin" to="/admin" class="nav-link admin-link">Admin</router-link>
      </nav>
      <div class="auth-buttons" v-else>
        <router-link to="/login" class="btn btn-login">Connexion</router-link>
        <router-link to="/register" class="btn btn-register">Inscription</router-link>
      </div>
    </header>
    
    <main class="app-content">
      <router-view />
    </main>
    
    <footer class="app-footer">
      <p>&copy; {{ currentYear }} CESIZen - L'application de votre santé mentale</p>
    </footer>
  </div>
</template>

<script lang="ts">
import { defineComponent, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from './stores/auth';

export default defineComponent({
  name: 'App',
  setup() {
    const authStore = useAuthStore();
    const router = useRouter();
    
    const isAuthenticated = computed(() => authStore.isAuthenticated);
    const isAdmin = computed(() => authStore.isAdmin);
    const currentYear = new Date().getFullYear();
    
    const logout = async () => {
      await authStore.logout();
      router.push('/login');
    };
    
    return {
      isAuthenticated,
      isAdmin,
      currentYear,
      logout
    };
  }
});
</script>

<style>
/* Styles globaux */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: Arial, sans-serif;
  line-height: 1.6;
  color: #333;
  background-color: #f5f5f5;
}

.app-container {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.app-header {
  background-color: white;
  padding: 15px 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
}

.logo-container {
  display: flex;
  align-items: center;
}

.logo {
  height: 40px;
  width: auto;
  margin-right: 10px;
}

.app-title {
  font-size: 1.5rem;
  color: #4CAF50;
}

.nav-menu {
  display: flex;
  gap: 15px;
}

.nav-link {
  color: #333;
  text-decoration: none;
  padding: 5px;
}

.nav-link:hover,
.nav-link.router-link-active {
  color: #4CAF50;
  border-bottom: 2px solid #4CAF50;
}

.admin-link {
  background-color: #4CAF50;
  color: white !important;
  padding: 5px 10px;
  border-radius: 4px;
}

.admin-link:hover {
  background-color: #45a049;
  border-bottom: none;
}

.auth-buttons {
  display: flex;
  gap: 10px;
}

.btn {
  padding: 8px 16px;
  border-radius: 4px;
  text-decoration: none;
  font-weight: bold;
}

.btn-login {
  background-color: #f5f5f5;
  color: #333;
}

.btn-register {
  background-color: #4CAF50;
  color: white;
}

.app-content {
  flex: 1;
  padding: 20px;
}

.app-footer {
  background-color: #333;
  color: white;
  text-align: center;
  padding: 10px;
  margin-top: auto;
}

/* Mobile-first media queries */
@media (max-width: 768px) {
  .app-header {
    flex-direction: column;
    gap: 15px;
  }
  
  .nav-menu,
  .auth-buttons {
    width: 100%;
    justify-content: center;
  }
}
</style>