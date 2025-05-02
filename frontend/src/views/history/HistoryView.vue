<template>
    <div class="history-container">
      <h1 class="page-title">Historique des diagnostics</h1>
      
      <div v-if="loading" class="loading-spinner">
        <div class="spinner"></div>
        <p>Chargement de l'historique...</p>
      </div>
      
      <div v-else-if="error" class="error-container">
        <p>{{ error }}</p>
        <button @click="fetchDiagnostics" class="btn-retry">Réessayer</button>
      </div>
      
      <div v-else>
        <div v-if="savedDiagnostics.length === 0" class="empty-history">
          <p>Vous n'avez pas encore de diagnostics sauvegardés.</p>
          <button @click="goToQuestionnaire" class="btn-primary">Faire un diagnostic</button>
        </div>
        
        <div v-else class="history-list">
          <div v-for="diagnostic in savedDiagnostics" :key="diagnostic.id" class="history-item">
            <div class="item-header" :class="getStressLevelClass(diagnostic.stress_level)">
              <div class="date-info">
                {{ formatDate(diagnostic.diagnostic_date) }}
              </div>
              <div class="score-info">
                <div class="score-badge">{{ diagnostic.score_total }}</div>
                <div class="level-badge">{{ diagnostic.stress_level }}</div>
              </div>
            </div>
            
            <div class="item-content">
              <div class="questionnaire-info">
                <strong>Questionnaire:</strong> {{ diagnostic.questionnaire?.title || 'Non spécifié' }}
              </div>
              
              <div class="consequences-info">
                <p class="consequences-title">Conséquences possibles:</p>
                <p class="consequences-text">{{ truncateText(diagnostic.consequences, 100) }}</p>
              </div>
            </div>
            
            <div class="item-actions">
              <button @click="viewDiagnostic(diagnostic)" class="btn-view">
                <i class="fas fa-eye"></i> Voir détails
              </button>
              <button @click="confirmDelete(diagnostic)" class="btn-delete">
                <i class="fas fa-trash"></i> Supprimer
              </button>
            </div>
          </div>
        </div>
        
        <div class="pagination" v-if="totalPages > 1">
          <button 
            class="page-btn" 
            :class="{ disabled: currentPage === 1 }"
            @click="changePage(currentPage - 1)" 
            :disabled="currentPage === 1"
          >
            Précédent
          </button>
          
          <div class="page-info">
            Page {{ currentPage }} sur {{ totalPages }}
          </div>
          
          <button 
            class="page-btn" 
            :class="{ disabled: currentPage === totalPages }"
            @click="changePage(currentPage + 1)" 
            :disabled="currentPage === totalPages"
          >
            Suivant
          </button>
        </div>
      </div>
      
      <!-- Modal de confirmation de suppression -->
      <div v-if="showDeleteConfirmation" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <h2>Confirmer la suppression</h2>
            <button @click="cancelDelete" class="close-btn">&times;</button>
          </div>
          
          <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer ce diagnostic du {{ formatDate(diagnosticToDelete?.diagnostic_date) }} ?</p>
            <p class="warning">Cette action est irréversible.</p>
            
            <div class="modal-actions">
              <button @click="cancelDelete" class="btn-secondary">Annuler</button>
              <button @click="deleteDiagnostic" class="btn-delete">Supprimer</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script lang="ts">
  import { defineComponent, ref, computed, onMounted } from 'vue';
  import { useRouter } from 'vue-router';
  import { useDiagnosticStore } from '../../stores/diagnostic';
  import type { Diagnostic } from '../../types';
  
  export default defineComponent({
    name: 'HistoryView',
    setup() {
      const router = useRouter();
      const diagnosticStore = useDiagnosticStore();
      
      const loading = computed(() => diagnosticStore.loading);
      const error = computed(() => diagnosticStore.error);
      const allDiagnostics = computed(() => diagnosticStore.diagnostics);
      
      // Filtrer pour n'afficher que les diagnostics sauvegardés
      const savedDiagnostics = computed(() => {
        return allDiagnostics.value.filter(d => d.saved);
      });
      
      // Pagination
      const itemsPerPage = 5;
      const currentPage = ref(1);
      const totalPages = computed(() => {
        return Math.ceil(savedDiagnostics.value.length / itemsPerPage);
      });
      
      const paginatedDiagnostics = computed(() => {
        const start = (currentPage.value - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        return savedDiagnostics.value.slice(start, end);
      });
      
      const changePage = (page: number) => {
        if (page >= 1 && page <= totalPages.value) {
          currentPage.value = page;
        }
      };
      
      // Modal de suppression
      const showDeleteConfirmation = ref(false);
      const diagnosticToDelete = ref<Diagnostic | null>(null);
      
      const confirmDelete = (diagnostic: Diagnostic) => {
        diagnosticToDelete.value = diagnostic;
        showDeleteConfirmation.value = true;
      };
      
      const cancelDelete = () => {
        diagnosticToDelete.value = null;
        showDeleteConfirmation.value = false;
      };
      
      const deleteDiagnostic = async () => {
        if (!diagnosticToDelete.value) return;
        
        try {
          await diagnosticStore.deleteDiagnostic(diagnosticToDelete.value.id);
          cancelDelete();
        } catch (err) {
          console.error('Erreur lors de la suppression du diagnostic:', err);
        }
      };
      
      // Actions
      const viewDiagnostic = (diagnostic: Diagnostic) => {
        router.push(`/diagnostics/${diagnostic.id}`);
      };
      
      const goToQuestionnaire = () => {
        router.push('/questionnaires');
      };
      
      const fetchDiagnostics = async () => {
        try {
          await diagnosticStore.fetchDiagnostics();
        } catch (err) {
          console.error('Erreur lors du chargement des diagnostics:', err);
        }
      };
      
      // Helpers
      const formatDate = (dateString?: string) => {
        if (!dateString) return 'Date inconnue';
        
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('fr-FR', {
          day: 'numeric',
          month: 'long',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        }).format(date);
      };
      
      const truncateText = (text?: string, maxLength: number = 100) => {
        if (!text) return 'Non spécifié';
        if (text.length <= maxLength) return text;
        
        return text.substring(0, maxLength) + '...';
      };
      
      const getStressLevelClass = (level?: string) => {
        if (!level) return '';
        
        switch (level.toLowerCase()) {
          case 'faible':
            return 'level-low';
          case 'modéré':
            return 'level-medium';
          case 'élevé':
            return 'level-high';
          default:
            return '';
        }
      };
      
      onMounted(() => {
        fetchDiagnostics();
      });
      
      return {
        savedDiagnostics,
        paginatedDiagnostics,
        currentPage,
        totalPages,
        loading,
        error,
        showDeleteConfirmation,
        diagnosticToDelete,
        fetchDiagnostics,
        changePage,
        confirmDelete,
        cancelDelete,
        deleteDiagnostic,
        viewDiagnostic,
        goToQuestionnaire,
        formatDate,
        truncateText,
        getStressLevelClass
      };
    }
  });
  </script>
  
  <style scoped>
  .history-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
  }
  
  .page-title {
    text-align: center;
    margin-bottom: 30px;
    color: #333;
  }
  
  .loading-spinner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px;
    border-radius: 12px;
    background: white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  }
  
  .spinner {
    width: 48px;
    height: 48px;
    border: 5px solid #c8e6c9;
    border-top: 5px solid #4caf50;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 16px;
  }
  
  @keyframes spin {
    to {
      transform: rotate(360deg);
    }
  }
  
  .error-container, .empty-history {
    text-align: center;
    padding: 40px;
    background-color: #f9f9f9;
    border-radius: 8px;
    margin: 30px 0;
  }
  
  .btn-retry, .btn-primary {
    margin-top: 15px;
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
  }
  
  .btn-retry:hover, .btn-primary:hover {
    background-color: #388E3C;
  }
  
  .history-list {
    margin: 20px 0;
  }
  
  .history-item {
    margin-bottom: 20px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    background-color: white;
  }
  
  .item-header {
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: white;
  }
  
  .level-low {
    background-color: #81C784;
  }
  
  .level-medium {
    background-color: #FFB74D;
  }
  
  .level-high {
    background-color: #E57373;
  }
  
  .date-info {
    font-size: 14px;
    opacity: 0.9;
  }
  
  .score-info {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  
  .score-badge {
    background-color: rgba(255, 255, 255, 0.2);
    padding: 8px;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
    font-size: 16px;
  }
  
  .level-badge {
    font-size: 14px;
    font-weight: bold;
  }
  
  .item-content {
    padding: 15px;
  }
  
  .questionnaire-info {
    margin-bottom: 10px;
    font-size: 14px;
  }
  
  .consequences-title {
    font-weight: 500;
    margin-bottom: 5px;
    font-size: 14px;
  }
  
  .consequences-text {
    font-size: 14px;
    color: #666;
    margin-top: 0;
  }
  
  .item-actions {
    padding: 15px;
    display: flex;
    justify-content: space-between;
    border-top: 1px solid #eee;
  }
  
  .btn-view, .btn-delete {
    padding: 8px 12px;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
    transition: background-color 0.2s;
  }
  
  .btn-view {
    background-color: #2196F3;
    color: white;
  }
  
  .btn-view:hover {
    background-color: #1976D2;
  }
  
  .btn-delete {
    background-color: #F44336;
    color: white;
  }
  
  .btn-delete:hover {
    background-color: #D32F2F;
  }
  
  .pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 30px;
    gap: 15px;
  }
  
  .page-btn {
    padding: 8px 15px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
  }
  
  .page-btn:hover:not(.disabled) {
    background-color: #388E3C;
  }
  
  .page-btn.disabled {
    background-color: #CCCCCC;
    cursor: not-allowed;
  }
  
  .page-info {
    font-size: 14px;
    color: #666;
  }
  
  /* Modal Styles */
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
    max-width: 500px;
    width: 90%;
    overflow: hidden;
  }
  
  .modal-header {
    padding: 15px;
    background-color: #F5F5F5;
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
  
  .warning {
    color: #F44336;
    font-weight: 500;
  }
  
  .modal-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
    gap: 10px;
  }
  
  .btn-secondary {
    padding: 8px 15px;
    background-color: #F5F5F5;
    color: #333;
    border: 1px solid #DDD;
    border-radius: 4px;
    cursor: pointer;
  }
  
  @media (max-width: 600px) {
    .item-actions, .pagination {
      flex-direction: column;
      gap: 10px;
    }
    
    .btn-view, .btn-delete, .page-btn {
      width: 100%;
      justify-content: center;
    }
  }
  </style>