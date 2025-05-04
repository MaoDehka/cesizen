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

interface ContentResponse {
  content: Content;
  message?: string;
}

export const useContentStore = defineStore('content', () => {
  const contents = ref<Content[]>([]);
  const currentContent = ref<Content | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);

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
    loading.value = true;
    error.value = null;
    
    try {
      currentContent.value = await api.get<Content>(`/contents/${page}`);
      return currentContent.value;
    } catch (err: any) {
      error.value = err.message || `Une erreur est survenue lors du chargement du contenu de la page ${page}`;
      console.error(error.value);
      return null;
    } finally {
      loading.value = false;
    }
  };

  const updateContent = async (id: number, data: Partial<Content>) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await api.put<ContentResponse>(`/admin/contents/${id}`, data);
      
      // Mettre à jour le contenu courant si c'est celui qui est actuellement affiché
      if (currentContent.value && currentContent.value.id === id) {
        currentContent.value = response.content;
      }
      
      // Mettre à jour la liste des contenus
      const index = contents.value.findIndex(c => c.id === id);
      if (index !== -1) {
        contents.value[index] = response.content;
      }
      
      return response;
    } catch (err: any) {
      error.value = err.message || `Une erreur est survenue lors de la mise à jour du contenu ${id}`;
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  return {
    contents,
    currentContent,
    loading,
    error,
    fetchContents,
    fetchContentById,
    fetchContentByPage,
    updateContent
  };
});