// src/stores/content.ts
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '../services/api';

export interface Content {
  id: number;
  page: string;
  title: string;
  content: string;
  active: boolean;
  created_at?: string;
  updated_at?: string;
}

// Bus d'événements simple pour les notifications de mise à jour
const contentUpdatedEvent = new EventTarget();

export const useContentStore = defineStore('content', () => {
  const contents = ref<Content[]>([]);
  const currentContent = ref<Content | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);
  
  // Cache pour les contenus par page
  const contentCache = ref<Record<string, Content>>({});

  // Actions
  const fetchContents = async () => {
    loading.value = true;
    error.value = null;
    
    try {
      contents.value = await api.get<Content[]>('/admin/contents');
      return contents.value;
    } catch (err: any) {
      error.value = err.message || 'Une erreur est survenue lors du chargement des contenus';
      console.error(error.value);
      return [];
    } finally {
      loading.value = false;
    }
  };

  const fetchContentById = async (id: number) => {
    loading.value = true;
    error.value = null;
    
    try {
      currentContent.value = await api.get<Content>(`/admin/contents/${id}`);
      return currentContent.value;
    } catch (err: any) {
      error.value = err.message || `Une erreur est survenue lors du chargement du contenu ${id}`;
      console.error(error.value);
      return null;
    } finally {
      loading.value = false;
    }
  };

  const fetchContentByPage = async (page: string) => {
    // Si le contenu est déjà en cache, le retourner
    if (contentCache.value[page]) {
      return contentCache.value[page];
    }
    
    loading.value = true;
    error.value = null;
    
    try {
      const content = await api.get<Content>(`/contents/${page}`);
      currentContent.value = content;
      
      // Mettre en cache pour les prochaines demandes
      contentCache.value[page] = content;
      
      return content;
    } catch (err: any) {
      error.value = err.message || `Une erreur est survenue lors du chargement du contenu de la page ${page}`;
      console.error(error.value);
      return null;
    } finally {
      loading.value = false;
    }
  };

  // Réinitialiser le cache pour une page spécifique
  const invalidateCache = (page: string) => {
    if (contentCache.value[page]) {
      delete contentCache.value[page];
    }
  };

  const updateContent = async (id: number, data: Partial<Content>) => {
    loading.value = true;
    error.value = null;
    
    try {
      // Adapter en fonction de la structure de réponse réelle de votre API
      const updatedContent = await api.put<Content>(`/admin/contents/${id}`, data);
      
      // Mettre à jour le contenu courant si c'est celui qui est actuellement affiché
      if (currentContent.value && currentContent.value.id === id) {
        currentContent.value = updatedContent;
      }
      
      // Mettre à jour la liste des contenus
      const index = contents.value.findIndex(c => c.id === id);
      if (index !== -1) {
        contents.value[index] = updatedContent;
      }
      
      // Si le contenu a une page, invalider le cache pour cette page
      if (updatedContent.page) {
        invalidateCache(updatedContent.page);
        
        // Émettre un événement pour notifier les autres composants
        const event = new CustomEvent('content-updated', { 
          detail: { page: updatedContent.page, id }
        });
        contentUpdatedEvent.dispatchEvent(event);
      }
      
      return updatedContent;
    } catch (err: any) {
      error.value = err.message || `Une erreur est survenue lors de la mise à jour du contenu ${id}`;
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  // Écouter les événements de mise à jour du contenu
  const onContentUpdated = (callback: (event: CustomEvent) => void) => {
    contentUpdatedEvent.addEventListener('content-updated', callback as EventListener);
    
    // Fonction pour supprimer l'écouteur
    return () => {
      contentUpdatedEvent.removeEventListener('content-updated', callback as EventListener);
    };
  };

  return {
    contents,
    currentContent,
    loading,
    error,
    fetchContents,
    fetchContentById,
    fetchContentByPage,
    updateContent,
    invalidateCache,
    onContentUpdated
  };
});