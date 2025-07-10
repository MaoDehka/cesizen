<template>
  <div class="app-container">
    <header class="app-header">
      <div class="logo-container">
        <img src="./assets/logo.jpg" alt="CESIZen" class="logo" />
        <h1 class="app-title">CESIZen</h1>
        <!-- Affichage de la version - Discret -->
        <span class="version-info" @click="toggleVersionDetails">
          v{{ appVersion }}
        </span>
      </div>
      
      <!-- Menu pour utilisateurs connectés -->
      <nav v-if="isAuthenticated" class="nav-menu">
        <!-- Menu statique par défaut -->
        <template v-if="!parsedMenu">
          <router-link to="/" class="nav-link">Accueil</router-link>
          <router-link to="/questionnaires" class="nav-link">Diagnostics</router-link>
          <router-link to="/history" class="nav-link">Historique</router-link>
          <a href="#" @click.prevent="logout" class="nav-link">Déconnexion</a>
          <router-link v-if="isAdmin" to="/admin" class="nav-link admin-link">Admin</router-link>
        </template>
        
        <!-- Menu dynamique -->
        <template v-else>
          <template v-for="(item, index) in parsedMenu" :key="index">
            <!-- N'afficher les liens admin que pour les admin -->
            <template v-if="!item.adminOnly || (item.adminOnly && isAdmin)">
              <!-- Liens de routage -->
              <router-link 
                v-if="item.type === 'router-link'" 
                :to="item.route" 
                class="nav-link"
                :class="{ 'admin-link': item.adminOnly }"
              >
                {{ item.text }}
              </router-link>
              
              <!-- Lien de déconnexion -->
              <a 
                v-else-if="item.type === 'logout'" 
                href="#" 
                @click.prevent="logout" 
                class="nav-link"
              >
                {{ item.text }}
              </a>
              
              <!-- Autres types de liens -->
              <a 
                v-else 
                :href="item.route" 
                class="nav-link" 
                target="_blank"
              >
                {{ item.text }}
              </a>
            </template>
          </template>
        </template>
      </nav>
      
      <!-- Boutons d'authentification pour utilisateurs non connectés -->
      <div class="auth-buttons" v-else>
        <router-link to="/login" class="btn btn-login">Connexion</router-link>
        <router-link to="/register" class="btn btn-register">Inscription</router-link>
      </div>
    </header>

    <!-- Modal détails de version (pour la démo) -->
    <div v-if="showVersionDetails" class="version-modal" @click="showVersionDetails = false">
      <div class="version-modal-content" @click.stop>
        <h3>🚀 Informations de déploiement</h3>
        <div class="version-details">
          <p><strong>Version:</strong> {{ appVersion }}</p>
          <p><strong>Déployé le:</strong> {{ formatDate(buildTime) }}</p>
          <p><strong>Commit:</strong> <code>{{ gitCommit }}</code></p>
          <p><strong>Branche:</strong> <code>{{ gitBranch }}</code></p>
          <p><strong>Environnement:</strong> Production</p>
        </div>
        <button @click="showVersionDetails = false" class="btn-close">Fermer</button>
      </div>
    </div>
    
    <main class="app-content">
      <router-view />
    </main>
    
    <footer class="app-footer">
      <div v-if="footerContent" v-html="footerContent.content.replace('{year}', currentYear.toString())"></div>
      <p v-else>&copy; {{ currentYear }} CESIZen - L'application de votre santé mentale</p>
      
      <!-- Version dans le footer (alternative plus discrète) -->
      <div class="footer-version">
        Dernière mise à jour: {{ formatDate(buildTime) }} | 
        <span class="commit-info" @click="toggleVersionDetails">{{ gitCommit }}</span>
      </div>
    </footer>
  </div>
</template>

<script lang="ts">
import { defineComponent, computed, ref, onMounted, watch, onUnmounted, onBeforeMount } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from './stores/auth';
import { useContentStore } from './stores/content';
import type { Content } from './types';
import jwtConfig from './config/jwt';

interface MenuItem {
  text: string;
  route: string;
  type: 'router-link' | 'logout' | 'external';
  adminOnly?: boolean;
}

export default defineComponent({
  name: 'App',
  setup() {
    const authStore = useAuthStore();
    const contentStore = useContentStore();
    const router = useRouter();
    
    const menuContent = ref<Content | null>(null);
    const footerContent = ref<Content | null>(null);
    const parsedMenu = ref<MenuItem[] | null>(null);
    const showVersionDetails = ref(false);
    
    // Informations de version injectées au build
    const appVersion = __APP_VERSION__;
    const buildTime = __BUILD_TIME__;
    const gitCommit = __GIT_COMMIT__;
    const gitBranch = __GIT_BRANCH__;
    
    const isAuthenticated = computed(() => authStore.isAuthenticated);
    const isAdmin = computed(() => authStore.isAdmin);
    const currentYear = new Date().getFullYear();
    
    // Formatage de date pour l'affichage
    const formatDate = (isoString: string) => {
      const date = new Date(isoString);
      return date.toLocaleString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    };
    
    const toggleVersionDetails = () => {
      showVersionDetails.value = !showVersionDetails.value;
    };
    
    // Vérification périodique de l'expiration du token
    let tokenCheckInterval: number | null = null;
    
    const setupTokenCheck = () => {
      if (isAuthenticated.value) {
        // Nettoyer l'intervalle existant si présent
        if (tokenCheckInterval !== null) {
          clearInterval(tokenCheckInterval);
        }
        
        // Vérifier l'expiration du token selon l'intervalle configuré
        tokenCheckInterval = window.setInterval(() => {
          authStore.checkTokenExpiration();
        }, jwtConfig.tokenCheckInterval);
      }
    };
    
    const fetchContents = async () => {
      try {
        // Charger le contenu du menu
        menuContent.value = await contentStore.fetchContentByPage('menu');
        
        if (menuContent.value) {
          try {
            // Essayer de parser le contenu comme JSON
            parsedMenu.value = JSON.parse(menuContent.value.content);
          } catch (e) {
            console.error('Erreur lors du parsing du menu:', e);
            // Si échec du parsing, conserver le menu par défaut
            parsedMenu.value = null;
          }
        }
        
        // Charger le contenu du pied de page
        footerContent.value = await contentStore.fetchContentByPage('footer');
      } catch (err) {
        console.error('Erreur lors du chargement des contenus:', err);
      }
    };
    
    const logout = async () => {
      await authStore.logout();
      router.push('/login');
    };
    
    // Configurer l'écouteur pour les mises à jour de contenu
    const setupContentUpdateListener = () => {
      if (!contentStore.onContentUpdated) return;
      
      const removeListener = contentStore.onContentUpdated((event: CustomEvent) => {
        const { page } = event.detail;
        
        if (page === 'menu') {
          contentStore.fetchContentByPage('menu')
            .then(content => {
              if (content) {
                menuContent.value = content;
                try {
                  parsedMenu.value = JSON.parse(content.content);
                } catch (e) {
                  console.error('Erreur lors du parsing du menu mis à jour:', e);
                  parsedMenu.value = null;
                }
              }
            });
        } else if (page === 'footer') {
          contentStore.fetchContentByPage('footer')
            .then(content => {
              if (content) {
                footerContent.value = content;
              }
            });
        }
      });
      
      onUnmounted(() => {
        if (removeListener) removeListener();
      });
    };
    
    // Charger les contenus au montage et configurer l'écouteur
    onBeforeMount(() => {
      // Vérifier si l'utilisateur est déjà authentifié
      if (isAuthenticated.value) {
        authStore.fetchUser();
        setupTokenCheck();
      }
    });
    
    onMounted(() => {
      if (isAuthenticated.value) {
        fetchContents();
      }
      
      setupContentUpdateListener();
      setupTokenCheck();
    });
    
    // Observer les changements d'authentification
    watch(isAuthenticated, (newValue) => {
      if (newValue === true) {
        fetchContents();
        setupTokenCheck();
      } else {
        menuContent.value = null;
        footerContent.value = null;
        parsedMenu.value = null;
        
        // Nettoyer l'intervalle de vérification du token
        if (tokenCheckInterval !== null) {
          clearInterval(tokenCheckInterval);
          tokenCheckInterval = null;
        }
      }
    });
    
    // Nettoyer les ressources lors de la destruction du composant
    onUnmounted(() => {
      if (tokenCheckInterval !== null) {
        clearInterval(tokenCheckInterval);
      }
    });
    
    return {
      isAuthenticated,
      isAdmin,
      currentYear,
      menuContent,
      footerContent,
      parsedMenu,
      showVersionDetails,
      appVersion,
      buildTime,
      gitCommit,
      gitBranch,
      formatDate,
      toggleVersionDetails,
      logout
    };
  }
});
</script>

<style>
/* Styles existants... */
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
  position: relative;
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

/* Styles pour l'affichage de version */
.version-info {
  background-color: #4CAF50;
  color: white;
  font-size: 0.75rem;
  padding: 2px 6px;
  border-radius: 12px;
  margin-left: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.version-info:hover {
  background-color: #45a049;
  transform: scale(1.05);
}

/* Modal de version pour la démo */
.version-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.version-modal-content {
  background: white;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  max-width: 500px;
  width: 90%;
}

.version-modal-content h3 {
  color: #4CAF50;
  margin-bottom: 20px;
  text-align: center;
}

.version-details p {
  margin: 10px 0;
  display: flex;
  justify-content: space-between;
}

.version-details code {
  background-color: #f4f4f4;
  padding: 2px 6px;
  border-radius: 4px;
  font-family: monospace;
}

.btn-close {
  background-color: #4CAF50;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 20px;
  width: 100%;
}

.btn-close:hover {
  background-color: #45a049;
}

/* Version dans le footer */
.footer-version {
  font-size: 0.8rem;
  opacity: 0.7;
  margin-top: 10px;
  text-align: center;
}

.commit-info {
  cursor: pointer;
  text-decoration: underline;
}

.commit-info:hover {
  color: #4CAF50;
}

/* Styles existants pour le reste... */
.nav-menu {
  display: flex;
  gap: 15px;
}

.nav-link {
  color: #333;
  text-decoration: none;
  padding: 5px;
}

.nav-link:hover, .nav-link.router-link-active {
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
  padding: 15px;
  margin-top: auto;
}

/* Transition entre les pages */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
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
  
  .version-modal-content {
    margin: 20px;
    padding: 20px;
  }
}
</style> 