<template>
    <div class="questionnaire-list-container">
      <div class="content">
        <div v-if="loading" class="loading-spinner">
          <div class="spinner"></div>
          <p>Chargement des questionnaires...</p>
        </div>
  
        <div v-else-if="error" class="error">
          <p>{{ error }}</p>
        </div>
  
        <div v-else class="questionnaire-list">
          <div
            v-for="questionnaire in questionnaires"
            :key="questionnaire.id"
            class="questionnaire-card"
            @click="selectQuestionnaire(questionnaire.id)"
          >
            <div class="card-body">
              <h3>Questionnaire {{ questionnaire.id }} - {{ questionnaire.title }}</h3>
              <p>{{ questionnaire.description || 'Pas de description disponible.' }}</p>
            </div>
            <div class="badge">
              {{ questionnaire.nb_question }} Q
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>  
  
  <script lang="ts">
  import { defineComponent, ref, onMounted, computed } from 'vue';
  import { useRouter } from 'vue-router';
  import { useQuestionnaireStore } from '../../stores/questionnaire';
  import type { Questionnaire } from '../../types';
  
  export default defineComponent({
    name: 'QuestionnaireListView',
    setup() {
      const router = useRouter();
      const questionnaireStore = useQuestionnaireStore();
      
      const questionnaires = computed(() => questionnaireStore.questionnaires);
      const loading = computed(() => questionnaireStore.loading);
      const error = computed(() => questionnaireStore.error);
      
      const fetchQuestionnaires = async () => {
        try {
          await questionnaireStore.fetchQuestionnaires();
        } catch (err) {
          console.error('Erreur lors du chargement des questionnaires:', err);
        }
      };
      
      const selectQuestionnaire = (id: number) => {
        router.push(`/questionnaires/${id}`);
      };
      
      onMounted(() => {
        fetchQuestionnaires();
      });
      
      return {
        questionnaires,
        loading,
        error,
        selectQuestionnaire
      };
    }
  });
  </script>
  
  <style scoped>
.questionnaire-list-container {
  min-height: 100vh;
  background: linear-gradient(to bottom, #e8f5e9, #ffffff);
  display: flex;
  justify-content: center;
  padding: 30px 20px;
}

.content {
  width: 100%;
  max-width: 800px;
}

.loading,
.error {
  background: white;
  padding: 40px;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.error {
  color: #d32f2f;
}

.questionnaire-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.questionnaire-card {
  background: white;
  border-radius: 16px;
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.questionnaire-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.card-body {
  flex: 1;
}

.card-body h3 {
  margin: 0 0 8px;
  font-size: 20px;
  color: #2e7d32;
}

.card-body p {
  margin: 0;
  font-size: 14px;
  color: #555;
}

.badge {
  width: 48px;
  height: 48px;
  background-color: #4caf50;
  color: white;
  border-radius: 50%;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
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
</style>