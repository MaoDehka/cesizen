<template>
    <div class="questionnaire-questions-view">
      <div class="header-section">
        <div class="back-button-container">
          <button @click="goBack" class="btn-back">
            <i class="fas fa-arrow-left"></i> Retour
          </button>
        </div>
        <h1>Questions du questionnaire</h1>
        <div v-if="questionnaire" class="questionnaire-info">
          <h2>{{ questionnaire.title }}</h2>
          <p v-if="questionnaire.description" class="description">{{ questionnaire.description }}</p>
        </div>
      </div>
  
      <div v-if="loading" class="loading-spinner">
        <div class="spinner"></div>
        <p>Chargement des questions...</p>
      </div>
  
      <div v-else-if="error" class="error-message">
        <p>{{ error }}</p>
        <button @click="fetchQuestionnaire" class="btn-retry">Réessayer</button>
      </div>
  
      <div v-else class="content-section">
        <div class="action-bar">
          <button @click="openAddQuestionModal" class="btn-add">
            <i class="fas fa-plus"></i> Ajouter une question
          </button>
        </div>
  
        <div v-if="!questionnaire || !questions.length" class="empty-state">
          <p>Aucune question n'a été ajoutée à ce questionnaire.</p>
        </div>
  
        <div v-else class="questions-list">
          <div 
            v-for="(question, index) in questions" 
            :key="question.id" 
            class="question-card"
          >
            <div class="question-header">
              <span class="question-number">Question {{ index + 1 }}</span>
              <span class="question-score">{{ question.response_score }} points</span>
            </div>
            <div class="question-content">
              {{ question.response_text }}
            </div>
            <div class="question-actions">
              <button @click="editQuestion(question)" class="btn-edit">
                <i class="fas fa-edit"></i> Modifier
              </button>
              <button @click="confirmDeleteQuestion(question)" class="btn-delete">
                <i class="fas fa-trash"></i> Supprimer
              </button>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Modal pour ajouter/éditer une question -->
      <div v-if="showQuestionModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <h2>{{ isEditing ? 'Modifier la question' : 'Ajouter une question' }}</h2>
            <button @click="closeQuestionModal" class="close-btn">&times;</button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveQuestion">
              <div class="form-group">
                <label for="response_text">Texte de la question</label>
                <textarea 
                  id="response_text" 
                  v-model="questionForm.response_text" 
                  rows="3" 
                  required
                ></textarea>
              </div>
              <div class="form-group">
                <label for="response_score">Score (points)</label>
                <input 
                  type="number" 
                  id="response_score" 
                  v-model.number="questionForm.response_score" 
                  required
                  min="0"
                />
              </div>
              <div class="form-actions">
                <button type="button" @click="closeQuestionModal" class="btn-secondary">Annuler</button>
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
            <p>Êtes-vous sûr de vouloir supprimer cette question ?</p>
            <div class="form-actions">
              <button @click="cancelDelete" class="btn-secondary">Annuler</button>
              <button @click="deleteQuestion" class="btn-delete">Supprimer</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script lang="ts">
  import { defineComponent, ref, computed, onMounted } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import type { Questionnaire, Question } from '../../types';
  import api from '../../services/api';
  
  export default defineComponent({
    name: 'QuestionnaireQuestionsView',
    setup() {
      const route = useRoute();
      const router = useRouter();
      
      // États
      const loading = ref(false);
      const error = ref<string | null>(null);
      const questionnaire = ref<Questionnaire | null>(null);
      const questions = ref<Question[]>([]);
      
      // État des modals
      const showQuestionModal = ref(false);
      const showDeleteConfirmation = ref(false);
      const isEditing = ref(false);
      const currentQuestionId = ref<number | null>(null);
      
      // Formulaire de question
      const questionForm = ref({
        response_text: '',
        response_score: 0
      });
      
      // Récupérer l'ID du questionnaire depuis l'URL
      const questionnaireId = computed(() => {
        const id = Number(route.params.id);
        return isNaN(id) ? null : id;
      });
      
      // Chargement du questionnaire et de ses questions
      const fetchQuestionnaire = async () => {
        if (!questionnaireId.value) {
          error.value = 'ID de questionnaire invalide';
          return;
        }
        
        loading.value = true;
        error.value = null;
        
        try {
          const response = await api.get<Questionnaire>(`/questionnaires/${questionnaireId.value}`);
          questionnaire.value = response;
          questions.value = response.questions || [];
        } catch (err: any) {
          error.value = err.message || 'Erreur lors du chargement du questionnaire';
          console.error('Erreur lors du chargement du questionnaire:', err);
        } finally {
          loading.value = false;
        }
      };
      
      // Fonctions pour les modals
      const openAddQuestionModal = () => {
        isEditing.value = false;
        currentQuestionId.value = null;
        resetQuestionForm();
        showQuestionModal.value = true;
      };
      
      const editQuestion = (question: Question) => {
        isEditing.value = true;
        currentQuestionId.value = question.id;
        questionForm.value = {
          response_text: question.response_text,
          response_score: question.response_score
        };
        showQuestionModal.value = true;
      };
      
      const closeQuestionModal = () => {
        showQuestionModal.value = false;
        resetQuestionForm();
      };
      
      const resetQuestionForm = () => {
        questionForm.value = {
          response_text: '',
          response_score: 0
        };
      };
      
      // CRUD pour les questions
      const saveQuestion = async () => {
        if (!questionnaireId.value) return;
        
        loading.value = true;
        
        try {
          if (isEditing.value && currentQuestionId.value) {
            // Mise à jour
            await api.put(`/questions/${currentQuestionId.value}`, {
              ...questionForm.value,
              questionnaire_id: questionnaireId.value
            });
          } else {
            // Création
            await api.post('/questions', {
              ...questionForm.value,
              questionnaire_id: questionnaireId.value
            });
          }
          
          await fetchQuestionnaire();
          closeQuestionModal();
        } catch (err: any) {
          console.error('Erreur lors de l\'enregistrement de la question:', err);
          error.value = err.message || 'Une erreur est survenue lors de l\'enregistrement de la question';
        } finally {
          loading.value = false;
        }
      };
      
      const confirmDeleteQuestion = (question: Question) => {
        currentQuestionId.value = question.id;
        showDeleteConfirmation.value = true;
      };
      
      const cancelDelete = () => {
        currentQuestionId.value = null;
        showDeleteConfirmation.value = false;
      };
      
      const deleteQuestion = async () => {
        if (!currentQuestionId.value) return;
        
        loading.value = true;
        
        try {
          await api.delete(`/questions/${currentQuestionId.value}`);
          await fetchQuestionnaire();
          cancelDelete();
        } catch (err: any) {
          console.error('Erreur lors de la suppression de la question:', err);
          error.value = err.message || 'Une erreur est survenue lors de la suppression de la question';
        } finally {
          loading.value = false;
        }
      };
      
      const goBack = () => {
        router.push('/admin');
      };
      
      onMounted(() => {
        fetchQuestionnaire();
      });
      
      return {
        loading,
        error,
        questionnaire,
        questions,
        showQuestionModal,
        showDeleteConfirmation,
        isEditing,
        questionForm,
        fetchQuestionnaire,
        openAddQuestionModal,
        editQuestion,
        closeQuestionModal,
        saveQuestion,
        confirmDeleteQuestion,
        cancelDelete,
        deleteQuestion,
        goBack
      };
    }
  });
  </script>
  
  <style scoped>
  .questionnaire-questions-view {
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
  
  .questionnaire-info {
    background-color: #f9f9f9;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
  }
  
  .questionnaire-info h2 {
    margin-top: 0;
    margin-bottom: 10px;
    color: #4CAF50;
  }
  
  .description {
    color: #666;
    margin: 0;
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
  
  .questions-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
  }
  
  .question-card {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
  }
  
  .question-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 15px;
    background-color: #f5f5f5;
    border-bottom: 1px solid #eee;
  }
  
  .question-number {
    font-weight: 600;
    color: #333;
  }
  
  .question-score {
    padding: 4px 8px;
    background-color: #4CAF50;
    color: white;
    border-radius: 4px;
    font-size: 14px;
  }
  
  .question-content {
    padding: 15px;
    color: #333;
  }
  
  .question-actions {
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
  
  textarea, input[type="number"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
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
    .question-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 5px;
    }
    
    .question-actions {
      flex-direction: column;
      width: 100%;
    }
    
    .btn-edit, .btn-delete {
      width: 100%;
      justify-content: center;
    }
  }
  </style>