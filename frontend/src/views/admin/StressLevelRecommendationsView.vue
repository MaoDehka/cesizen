<template>
    <div class="recommendations-view">
      <div class="header-section">
        <div class="back-button-container">
          <button @click="goBack" class="btn-back">
            <i class="fas fa-arrow-left"></i> Retour
          </button>
        </div>
        <h1>Recommandations</h1>
        <div v-if="stressLevel" class="stress-level-info" :class="getLevelClass">
          <h2>{{ stressLevel.name }}</h2>
          <div class="stress-level-details">
            <div class="detail-item">
              <span class="detail-label">Score :</span>
              <span class="detail-value">{{ stressLevel.min_score }} - {{ stressLevel.max_score }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Risque :</span>
              <span class="detail-value">{{ stressLevel.risk_percentage }}%</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Statut :</span>
              <span class="status-badge" :class="{ active: stressLevel.active, inactive: !stressLevel.active }">
                {{ stressLevel.active ? 'Actif' : 'Inactif' }}
              </span>
            </div>
          </div>
          <div v-if="stressLevel.description" class="stress-level-section">
            <h3>Description</h3>
            <p>{{ stressLevel.description }}</p>
          </div>
          <div v-if="stressLevel.consequences" class="stress-level-section">
            <h3>Conséquences</h3>
            <p>{{ stressLevel.consequences }}</p>
          </div>
        </div>
      </div>
  
      <div v-if="loading" class="loading-spinner">
        <div class="spinner"></div>
        <p>Chargement des recommandations...</p>
      </div>
  
      <div v-else-if="error" class="error-message">
        <p>{{ error }}</p>
        <button @click="fetchStressLevel" class="btn-retry">Réessayer</button>
      </div>
  
      <div v-else class="content-section">
        <div class="action-bar">
          <button @click="openAddRecommendationModal" class="btn-add">
            <i class="fas fa-plus"></i> Ajouter une recommandation
          </button>
        </div>
  
        <div v-if="!stressLevel || !recommendations.length" class="empty-state">
          <p>Aucune recommandation n'a été ajoutée à ce niveau de stress.</p>
        </div>
  
        <div v-else class="recommendations-list">
          <div 
            v-for="(recommendation, index) in sortedRecommendations" 
            :key="recommendation.id" 
            class="recommendation-card"
          >
            <div class="recommendation-header">
              <span class="recommendation-order">Priorité {{ recommendation.order }}</span>
              <span 
                class="status-badge" 
                :class="{ active: recommendation.active, inactive: !recommendation.active }"
              >
                {{ recommendation.active ? 'Active' : 'Inactive' }}
              </span>
            </div>
            <div class="recommendation-content">
              <h3>{{ recommendation.description }}</h3>
              <p v-if="recommendation.details">{{ recommendation.details }}</p>
            </div>
            <div class="recommendation-actions">
              <button @click="editRecommendation(recommendation)" class="btn-edit">
                <i class="fas fa-edit"></i> Modifier
              </button>
              <button @click="confirmDeleteRecommendation(recommendation)" class="btn-delete">
                <i class="fas fa-trash"></i> Supprimer
              </button>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Modal pour ajouter/éditer une recommandation -->
      <div v-if="showRecommendationModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <h2>{{ isEditing ? 'Modifier la recommandation' : 'Ajouter une recommandation' }}</h2>
            <button @click="closeRecommendationModal" class="close-btn">&times;</button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveRecommendation">
              <div class="form-group">
                <label for="description">Description</label>
                <input 
                  type="text" 
                  id="description" 
                  v-model="recommendationForm.description" 
                  required
                />
              </div>
              <div class="form-group">
                <label for="details">Détails (optionnel)</label>
                <textarea 
                  id="details" 
                  v-model="recommendationForm.details" 
                  rows="3"
                ></textarea>
              </div>
              <div class="form-group">
                <label for="order">Ordre de priorité</label>
                <input 
                  type="number" 
                  id="order" 
                  v-model.number="recommendationForm.order" 
                  required
                  min="1"
                />
              </div>
              <div class="form-group">
                <label class="checkbox-label">
                  <input type="checkbox" v-model="recommendationForm.active">
                  Actif
                </label>
              </div>
              <div class="form-actions">
                <button type="button" @click="closeRecommendationModal" class="btn-secondary">Annuler</button>
                <button type="submit" class="btn-primary">{{ isEditing ? 'Enregistrer' : 'Ajouter' }}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
  
      <!-- Modal de confirmation de suppression -->
      <div v-if="showDeleteConfirmation" class="modal">
        <div class="modal-content confirmation-modal">
          <div class="modal-header">
            <h2>Confirmer la suppression</h2>
            <button @click="cancelDelete" class="close-btn">&times;</button>
          </div>
          <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer cette recommandation ?</p>
            <div class="form-actions">
              <button @click="cancelDelete" class="btn-secondary">Annuler</button>
              <button @click="deleteRecommendation" class="btn-delete">Supprimer</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script lang="ts">
  import { defineComponent, ref, computed, onMounted } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import type { StressLevel, Recommendation } from '../../types';
  import api from '../../services/api';
  
  export default defineComponent({
    name: 'StressLevelRecommendationsView',
    setup() {
      const route = useRoute();
      const router = useRouter();
      
      // États
      const loading = ref(false);
      const error = ref<string | null>(null);
      const stressLevel = ref<StressLevel | null>(null);
      const recommendations = ref<Recommendation[]>([]);
      
      // État des modals
      const showRecommendationModal = ref(false);
      const showDeleteConfirmation = ref(false);
      const isEditing = ref(false);
      const currentRecommendationId = ref<number | null>(null);
      
      // Formulaire de recommandation
      const recommendationForm = ref({
        description: '',
        details: '',
        order: 1,
        active: true
      });
      
      // Récupérer l'ID du niveau de stress depuis l'URL
      const stressLevelId = computed(() => {
        const id = Number(route.params.id);
        return isNaN(id) ? null : id;
      });
      
      // Trier les recommandations par ordre
      const sortedRecommendations = computed(() => {
        return [...recommendations.value].sort((a, b) => a.order - b.order);
      });
      
      // Classe CSS selon le niveau de stress
      const getLevelClass = computed(() => {
        if (!stressLevel.value) return '';
        
        switch (stressLevel.value.name.toLowerCase()) {
          case 'faible':
            return 'level-low';
          case 'modéré':
            return 'level-medium';
          case 'élevé':
            return 'level-high';
          default:
            return '';
        }
      });
      
      // Chargement du niveau de stress et de ses recommandations
      const fetchStressLevel = async () => {
        if (!stressLevelId.value) {
          error.value = 'ID de niveau de stress invalide';
          return;
        }
        
        loading.value = true;
        error.value = null;
        
        try {
          // On utilise l'API admin pour récupérer le niveau de stress
          const response = await api.get<any>(`/admin/stress-levels/${stressLevelId.value}`);
          stressLevel.value = response;
          // Vérifier si les recommandations sont incluses dans la réponse
          if (response && Array.isArray(response.recommendations)) {
            recommendations.value = response.recommendations;
          } else {
            // Si les recommandations ne sont pas incluses, faire une requête séparée pour les obtenir
            try {
              const recommendationsResponse = await api.get<Recommendation[]>(`/admin/stress-levels/${stressLevelId.value}/recommendations`);
              recommendations.value = recommendationsResponse;
            } catch (recError) {
              console.error('Erreur lors du chargement des recommandations:', recError);
              recommendations.value = [];
            }
          }
        } catch (err: any) {
          error.value = err.message || 'Erreur lors du chargement du niveau de stress';
          console.error('Erreur lors du chargement du niveau de stress:', err);
        } finally {
          loading.value = false;
        }
      };
      
      // Fonctions pour les modals
      const openAddRecommendationModal = () => {
        isEditing.value = false;
        currentRecommendationId.value = null;
        resetRecommendationForm();
        
        // Définir l'ordre par défaut comme le prochain disponible
        if (recommendations.value.length > 0) {
          const maxOrder = Math.max(...recommendations.value.map(r => r.order));
          recommendationForm.value.order = maxOrder + 1;
        } else {
          recommendationForm.value.order = 1;
        }
        
        showRecommendationModal.value = true;
      };
      
      const editRecommendation = (recommendation: Recommendation) => {
        isEditing.value = true;
        currentRecommendationId.value = recommendation.id;
        recommendationForm.value = {
          description: recommendation.description,
          details: recommendation.details || '',
          order: recommendation.order,
          active: recommendation.active
        };
        showRecommendationModal.value = true;
      };
      
      const closeRecommendationModal = () => {
        showRecommendationModal.value = false;
        resetRecommendationForm();
      };
      
      const resetRecommendationForm = () => {
        recommendationForm.value = {
          description: '',
          details: '',
          order: 1,
          active: true
        };
      };
      
      // CRUD pour les recommandations
      const saveRecommendation = async () => {
        if (!stressLevelId.value) return;
        
        loading.value = true;
        
        try {
          if (isEditing.value && currentRecommendationId.value) {
            // Mise à jour
            await api.put(`/admin/recommendations/${currentRecommendationId.value}`, {
              ...recommendationForm.value,
              stress_level_id: stressLevelId.value
            });
          } else {
            // Création
            await api.post('/admin/recommendations', {
              ...recommendationForm.value,
              stress_level_id: stressLevelId.value
            });
          }
          
          await fetchStressLevel();
          closeRecommendationModal();
        } catch (err: any) {
          console.error('Erreur lors de l\'enregistrement de la recommandation:', err);
          error.value = err.message || 'Une erreur est survenue lors de l\'enregistrement de la recommandation';
        } finally {
          loading.value = false;
        }
      };
      
      const confirmDeleteRecommendation = (recommendation: Recommendation) => {
        currentRecommendationId.value = recommendation.id;
        showDeleteConfirmation.value = true;
      };
      
      const cancelDelete = () => {
        currentRecommendationId.value = null;
        showDeleteConfirmation.value = false;
      };
      
      const deleteRecommendation = async () => {
        if (!currentRecommendationId.value) return;
        
        loading.value = true;
        
        try {
          await api.delete(`/admin/recommendations/${currentRecommendationId.value}`);
          await fetchStressLevel();
          cancelDelete();
        } catch (err: any) {
          console.error('Erreur lors de la suppression de la recommandation:', err);
          error.value = err.message || 'Une erreur est survenue lors de la suppression de la recommandation';
        } finally {
          loading.value = false;
        }
      };
      
      const goBack = () => {
        router.push('/admin');
      };
      
      onMounted(() => {
        fetchStressLevel();
      });
      
      return {
        loading,
        error,
        stressLevel,
        recommendations,
        sortedRecommendations,
        getLevelClass,
        showRecommendationModal,
        showDeleteConfirmation,
        isEditing,
        recommendationForm,
        fetchStressLevel,
        openAddRecommendationModal,
        editRecommendation,
        closeRecommendationModal,
        saveRecommendation,
        confirmDeleteRecommendation,
        cancelDelete,
        deleteRecommendation,
        goBack
      };
    }
  });
  </script>
  
  <style scoped>
  .recommendations-view {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
  }
  
  .header-section {
    margin-bottom: 30px;
  }
  
  .back-button-container {
    margin-bottom: 15px;
  }
  
  .btn-back {
    display: flex;
    align-items: center;
    gap: 5px;
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    font-size: 16px;
    padding: 5px 0;
  }
  
  .btn-back:hover {
    color: #4CAF50;
  }
  
  h1 {
    margin-bottom: 20px;
    color: #333;
  }
  
  .stress-level-info {
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #ccc;
  }
  
  .level-low {
    border-left-color: #4CAF50;
  }
  
  .level-medium {
    border-left-color: #FFA000;
  }
  
  .level-high {
    border-left-color: #F44336;
  }
  
  .stress-level-info h2 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #333;
  }
  
  .stress-level-details {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
  }
  
  .detail-item {
    display: flex;
    align-items: center;
    gap: 5px;
  }
  
  .detail-label {
    font-weight: 600;
    color: #555;
  }
  
  .status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
  }
  
  .status-badge.active {
    background-color: #c8e6c9;
    color: #2e7d32;
  }
  
  .status-badge.inactive {
    background-color: #ffcdd2;
    color: #c62828;
  }
  
  .stress-level-section {
    margin-top: 15px;
  }
  
  .stress-level-section h3 {
    margin-top: 0;
    margin-bottom: 8px;
    font-size: 16px;
    color: #333;
  }
  
  .stress-level-section p {
    margin: 0;
    color: #666;
  }
  
  .loading-spinner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px;
    background-color: #f9f9f9;
    border-radius: 8px;
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
    background-color: #ffebee;
    color: #c62828;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
  }
  
  .btn-retry {
    background-color: #f44336;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 8px 16px;
    cursor: pointer;
    margin-top: 10px;
  }
  
  .btn-retry:hover {
    background-color: #d32f2f;
  }
  
  .action-bar {
    margin-bottom: 20px;
    display: flex;
    justify-content: flex-end;
  }
  
  .btn-add {
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 8px 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
  }
  
  .btn-add:hover {
    background-color: #388E3C;
  }
  
  .empty-state {
    background-color: #f9f9f9;
    padding: 30px;
    border-radius: 8px;
    text-align: center;
    color: #666;
  }
  
  .recommendations-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
  }
  
  .recommendation-card {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
  }
  
  .recommendation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 15px;
    background-color: #f5f5f5;
    border-bottom: 1px solid #eee;
  }
  
  .recommendation-order {
    font-weight: 600;
    color: #333;
  }
  
  .recommendation-content {
    padding: 15px;
  }
  
  .recommendation-content h3 {
    margin-top: 0;
    margin-bottom: 10px;
    font-size: 18px;
    color: #333;
  }
  
  .recommendation-content p {
    margin: 0;
    color: #666;
  }
  
  .recommendation-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 12px 15px;
    border-top: 1px solid #eee;
  }
  
  .btn-edit, .btn-delete {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 14px;
  }
  
  .btn-edit {
    background-color: #FFC107;
    color: #333;
  }
  
  .btn-edit:hover {
    background-color: #FFA000;
  }
  
  .btn-delete {
    background-color: #F44336;
    color: white;
  }
  
  .btn-delete:hover {
    background-color: #D32F2F;
  }
  
  /* Modal styles */
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
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  }
  
  .confirmation-modal {
    max-width: 400px;
  }
  
  .modal-header {
    padding: 15px;
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
    color: #999;
  }
  
  .modal-body {
    padding: 20px;
  }
  
  .form-group {
    margin-bottom: 20px;
  }
  
  .form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
  }
  
  .checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
  }
  
  input[type="text"],
  input[type="number"],
  textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
  }
  
  input[type="checkbox"] {
    cursor: pointer;
  }
  
  .form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
  }
  
  .btn-primary, .btn-secondary {
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
  }
  
  .btn-primary {
    background-color: #4CAF50;
    color: white;
    border: none;
  }
  
  .btn-primary:hover {
    background-color: #388E3C;
  }
  
  .btn-secondary {
    background-color: #f5f5f5;
    color: #333;
    border: 1px solid #ddd;
  }
  
  .btn-secondary:hover {
    background-color: #e0e0e0;
  }
  
  @media (max-width: 768px) {
    .stress-level-details {
      flex-direction: column;
      gap: 10px;
    }
    
    .recommendation-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 8px;
    }
    
    .recommendation-actions {
      flex-direction: column;
      width: 100%;
    }
    
    .btn-edit, .btn-delete {
      width: 100%;
      justify-content: center;
    }
  }
  </style>