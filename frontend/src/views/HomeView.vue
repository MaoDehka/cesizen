<template>
  <div class="home-container">
    <div v-if="loading" class="loading-spinner">
      <div class="spinner"></div>
      <p>Chargement du contenu...</p>
    </div>
    
    <div v-else-if="error" class="error-message">
      <p>{{ error }}</p>
    </div>
    
    <div v-else-if="pageContent" class="dynamic-content" @click="handleLinkClick">
      <div v-html="pageContent.content"></div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useContentStore } from '../stores/content';
import type { Content } from '../types';

export default defineComponent({
  name: 'HomeView',
  setup() {
    const router = useRouter();
    const contentStore = useContentStore();
    const pageContent = ref<Content | null>(null);
    const loading = ref<boolean>(true);
    const error = ref<string | null>(null);
    
    const fetchHomeContent = async () => {
      loading.value = true;
      error.value = null;
      
      try {
        pageContent.value = await contentStore.fetchContentByPage('home');
      } catch (err: any) {
        error.value = err.message || 'Une erreur est survenue lors du chargement du contenu';
        console.error('Erreur lors du chargement du contenu:', err);
      } finally {
        loading.value = false;
      }
    };
    
    // Intercepter les clics sur les liens pour utiliser Vue Router
    const handleLinkClick = (event: MouseEvent) => {
      const target = event.target as HTMLElement;
      const link = target.closest('a');
      
      if (link && link.getAttribute('href')) {
        const href = link.getAttribute('href');
        
        // Si c'est un lien interne, utiliser Vue Router
        if (href && !href.startsWith('http') && !href.startsWith('mailto:')) {
          event.preventDefault();
          router.push(href);
        }
      }
    };
    
    onMounted(() => {
      fetchHomeContent();
    });
    
    return {
      pageContent,
      loading,
      error,
      handleLinkClick
    };
  }
});
</script>

<style scoped>
.home-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

.loading-spinner {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 30px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(76, 175, 80, 0.2);
  border-top: 4px solid #4CAF50;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 10px;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.error-message {
  padding: 15px;
  background-color: #ffebee;
  color: #c62828;
  border-radius: 4px;
  margin-bottom: 20px;
}

/* Styles pour le contenu dynamique */
:deep(.app-header) {
  text-align: center;
  margin-bottom: 30px;
}

:deep(.logo) {
  width: 80px;
  height: auto;
  color: #4CAF50;
}

:deep(.intro-section), :deep(.features-section), :deep(.info-section) {
  margin-bottom: 40px;
}

:deep(.feature-card) {
  background-color: #f9f9f9;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

:deep(.feature-link) {
  display: inline-block;
  margin-top: 10px;
  padding: 8px 15px;
  background-color: #4CAF50;
  color: white;
  text-decoration: none;
  border-radius: 4px;
}

:deep(h1), :deep(h2), :deep(h3) {
  color: #333;
}

:deep(p) {
  line-height: 1.6;
  color: #666;
}
</style>