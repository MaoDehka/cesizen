<template>
  <div class="app-container">
    <header class="app-header">
      <div class="logo-container">
        <img src="./assets/logo.jpg" alt="CESIZen" class="logo" />
        <h1 class="app-title">CESIZen</h1>
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
    
    <main class="app-content">
      <router-view />
    </main>
    
    <footer class="app-footer">
      <div v-if="footerContent" v-html="footerContent.content.replace('{year}', currentYear.toString())"></div>
      <p v-else>&copy; {{ currentYear }} CESIZen - L'application de votre santé mentale</p>
    </footer>
  </div>
</template>

<script lang="ts">
import { defineComponent, computed, ref, onMounted, watch, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from './stores/auth';
import { useContentStore } from './stores/content';
import type { Content } from './types';

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
    
    const isAuthenticated = computed(() => authStore.isAuthenticated);
    const isAdmin = computed(() => authStore.isAdmin);
    const currentYear = new Date().getFullYear();
    
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
    onMounted(() => {
      if (isAuthenticated.value) {
        fetchContents();
      }
      
      setupContentUpdateListener();
    });
    
    // Observer les changements d'authentification
    watch(isAuthenticated, (newValue) => {
      if (newValue === true) {
        fetchContents();
      } else {
        menuContent.value = null;
        footerContent.value = null;
        parsedMenu.value = null;
      }
    });
    
    return {
      isAuthenticated,
      isAdmin,
      currentYear,
      menuContent,
      footerContent,
      parsedMenu,
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
  padding: 10px;
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
}
</style>