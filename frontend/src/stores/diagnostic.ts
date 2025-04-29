// src/stores/diagnostic.ts
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { Diagnostic } from '../types';
import api from '../services/api';

interface DiagnosticResponse {
  diagnostic: Diagnostic;
  message?: string;
}

export const useDiagnosticStore = defineStore('diagnostic', () => {
  const diagnostics = ref<Diagnostic[]>([]);
  const currentDiagnostic = ref<Diagnostic | null>(null);
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
      currentDiagnostic.value = await api.get<Diagnostic>(`/diagnostics/${id}`);
      return currentDiagnostic.value;
    } catch (err: any) {
      error.value = err.message || `Une erreur est survenue lors du chargement du diagnostic ${id}`;
      console.error(error.value);
      return null;
    } finally {
      loading.value = false;
    }
  };

  const createDiagnostic = async (questions: number[]) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await api.post<DiagnosticResponse>('/diagnostics', { questions });
      
      // Ajouter le nouveau diagnostic à la liste
      if (response.diagnostic) {
        diagnostics.value.unshift(response.diagnostic);
        currentDiagnostic.value = response.diagnostic;
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
    loading,
    error,
    fetchDiagnostics,
    fetchDiagnosticById,
    createDiagnostic,
    updateDiagnostic,
    deleteDiagnostic
  };
});