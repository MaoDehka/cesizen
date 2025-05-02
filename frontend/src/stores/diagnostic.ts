// src/stores/diagnostic.ts
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { Diagnostic, StressLevel, Recommendation } from '../types';
import api from '../services/api';

interface DiagnosticResponse {
  diagnostic: Diagnostic;
  stress_level_details?: StressLevel;
  recommendations?: Recommendation[];
  message?: string;
}

export const useDiagnosticStore = defineStore('diagnostic', () => {
  const diagnostics = ref<Diagnostic[]>([]);
  const currentDiagnostic = ref<Diagnostic | null>(null);
  const currentStressLevel = ref<StressLevel | null>(null);
  const currentRecommendations = ref<Recommendation[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);

  // Actions
  const fetchDiagnostics = async () => {
    loading.value = true;
    error.value = null;
    
    try {
      diagnostics.value = await api.get<Diagnostic[]>('/diagnostics');
      return diagnostics.value;
    } catch (err: any) {
      error.value = err.message || 'Une erreur est survenue lors du chargement des diagnostics';
      console.error(error.value);
      return [];
    } finally {
      loading.value = false;
    }
  };

  const fetchDiagnosticById = async (id: number) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await api.get<DiagnosticResponse>(`/diagnostics/${id}`);
      
      currentDiagnostic.value = response.diagnostic;
      currentStressLevel.value = response.stress_level_details || null;
      currentRecommendations.value = response.recommendations || [];
      
      return currentDiagnostic.value;
    } catch (err: any) {
      error.value = err.message || `Une erreur est survenue lors du chargement du diagnostic ${id}`;
      console.error(error.value);
      return null;
    } finally {
      loading.value = false;
    }
  };

  const createDiagnostic = async (questionnaireId: number, questions: number[]) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await api.post<DiagnosticResponse>('/diagnostics', { 
        questionnaire_id: questionnaireId,
        questions 
      });
      
      // Ajouter le nouveau diagnostic à la liste
      if (response.diagnostic) {
        diagnostics.value.unshift(response.diagnostic);
        currentDiagnostic.value = response.diagnostic;
        currentStressLevel.value = response.stress_level_details || null;
        currentRecommendations.value = response.recommendations || [];
      }
      
      return response;
    } catch (err: any) {
      error.value = err.message || 'Une erreur est survenue lors de la création du diagnostic';
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  const updateDiagnostic = async (id: number, data: Partial<Diagnostic>) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await api.put<DiagnosticResponse>(`/diagnostics/${id}`, data);
      
      // Mettre à jour le diagnostic courant si c'est celui qui est actuellement affiché
      if (currentDiagnostic.value && currentDiagnostic.value.id === id) {
        currentDiagnostic.value = response.diagnostic;
        currentStressLevel.value = response.stress_level_details || null;
        currentRecommendations.value = response.recommendations || [];
      }
      
      // Mettre à jour la liste des diagnostics
      const index = diagnostics.value.findIndex(d => d.id === id);
      if (index !== -1) {
        diagnostics.value[index] = response.diagnostic;
      }
      
      return response;
    } catch (err: any) {
      error.value = err.message || `Une erreur est survenue lors de la mise à jour du diagnostic ${id}`;
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  const saveDiagnostic = async (id: number) => {
    loading.value = true;
    error.value = null;
    
    try {
      console.log(`Tentative de sauvegarde du diagnostic ${id}`);
      const response = await api.post<DiagnosticResponse>(`/diagnostics/${id}/save`);
      
      // Mettre à jour le diagnostic dans la liste locale
      if (currentDiagnostic.value && currentDiagnostic.value.id === id) {
        currentDiagnostic.value.saved = true;
      }
      
      const index = diagnostics.value.findIndex(d => d.id === id);
      if (index !== -1) {
        diagnostics.value[index].saved = true;
      }
      
      return response;
    } catch (err: any) {
      console.error('Erreur détaillée:', err);
      error.value = `Une erreur est survenue lors de la sauvegarde du diagnostic ${id}`;
      throw error.value;
    } finally {
      loading.value = false;
    }
  };
  
  const deleteDiagnostic = async (id: number) => {
    loading.value = true;
    error.value = null;
    
    try {
      await api.delete<{ message: string }>(`/diagnostics/${id}`);
      
      // Supprimer de la liste des diagnostics
      diagnostics.value = diagnostics.value.filter(d => d.id !== id);
      
      // Réinitialiser le diagnostic courant si c'était celui-ci
      if (currentDiagnostic.value && currentDiagnostic.value.id === id) {
        currentDiagnostic.value = null;
        currentStressLevel.value = null;
        currentRecommendations.value = [];
      }
      
      return true;
    } catch (err: any) {
      error.value = err.message || `Une erreur est survenue lors de la suppression du diagnostic ${id}`;
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  return {
    diagnostics,
    currentDiagnostic,
    currentStressLevel,
    currentRecommendations,
    loading,
    error,
    fetchDiagnostics,
    fetchDiagnosticById,
    createDiagnostic,
    updateDiagnostic,
    saveDiagnostic,  // Exposer la nouvelle méthode
    deleteDiagnostic
  };
});