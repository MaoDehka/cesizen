// src/stores/questionnaire.ts
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { Questionnaire } from '../types';
import api from '../services/api';

interface QuestionnaireResponse {
  questionnaire: Questionnaire;
  message?: string;
}

export const useQuestionnaireStore = defineStore('questionnaire', () => {
  const questionnaires = ref<Questionnaire[]>([]);
  const currentQuestionnaire = ref<Questionnaire | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);

  // Actions
  const fetchQuestionnaires = async () => {
    loading.value = true;
    error.value = null;
    
    try {
      questionnaires.value = await api.get<Questionnaire[]>('/questionnaires');
      return questionnaires.value;
    } catch (err: any) {
      error.value = err.message || 'Une erreur est survenue lors du chargement des questionnaires';
      console.error(error.value);
      return [];
    } finally {
      loading.value = false;
    }
  };

  const fetchQuestionnaireById = async (id: number) => {
    loading.value = true;
    error.value = null;
    
    try {
      currentQuestionnaire.value = await api.get<Questionnaire>(`/questionnaires/${id}`);
      return currentQuestionnaire.value;
    } catch (err: any) {
      error.value = err.message || `Une erreur est survenue lors du chargement du questionnaire ${id}`;
      console.error(error.value);
      return null;
    } finally {
      loading.value = false;
    }
  };

  const createQuestionnaire = async (data: Partial<Questionnaire>) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await api.post<QuestionnaireResponse>('/questionnaires', data);
      await fetchQuestionnaires(); // Rafraîchir la liste après création
      return response;
    } catch (err: any) {
      error.value = err.message || 'Une erreur est survenue lors de la création du questionnaire';
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  const updateQuestionnaire = async (id: number, data: Partial<Questionnaire>) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await api.put<QuestionnaireResponse>(`/questionnaires/${id}`, data);
      
      // Mettre à jour le questionnaire courant si c'est celui qui est actuellement affiché
      if (currentQuestionnaire.value && currentQuestionnaire.value.id === id) {
        currentQuestionnaire.value = response.questionnaire;
      }
      
      // Mettre à jour la liste des questionnaires
      const index = questionnaires.value.findIndex(q => q.id === id);
      if (index !== -1) {
        questionnaires.value[index] = response.questionnaire;
      }
      
      return response;
    } catch (err: any) {
      error.value = err.message || `Une erreur est survenue lors de la mise à jour du questionnaire ${id}`;
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  const deleteQuestionnaire = async (id: number) => {
    loading.value = true;
    error.value = null;
    
    try {
      await api.delete<{ message: string }>(`/questionnaires/${id}`);
      
      // Supprimer de la liste des questionnaires
      questionnaires.value = questionnaires.value.filter(q => q.id !== id);
      
      // Réinitialiser le questionnaire courant si c'était celui-ci
      if (currentQuestionnaire.value && currentQuestionnaire.value.id === id) {
        currentQuestionnaire.value = null;
      }
      
      return true;
    } catch (err: any) {
      error.value = err.message || `Une erreur est survenue lors de la suppression du questionnaire ${id}`;
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  return {
    questionnaires,
    currentQuestionnaire,
    loading,
    error,
    fetchQuestionnaires,
    fetchQuestionnaireById,
    createQuestionnaire,
    updateQuestionnaire,
    deleteQuestionnaire
  };
});