<template>
    <div class="questionnaire-container">
      <h1>Questionnaire de stress</h1>
      <p class="description">
        Ce questionnaire est basé sur l'échelle de Holmes et Rahe. Sélectionnez les événements que vous avez vécus 
        au cours des 12 derniers mois pour évaluer votre niveau de stress.
      </p>
  
      <div v-if="loading" class="loading">
        <p>Chargement du questionnaire...</p>
      </div>
      
      <div v-else-if="error" class="error">
        <p>{{ error }}</p>
      </div>
  
      <div v-else class="questionnaire-form">
        <form @submit.prevent="submitQuestionnaire">
          <div v-for="question in questions" :key="question.id" class="question-item">
            <label class="question-label">
              <input 
                type="checkbox" 
                :value="question.id" 
                v-model="selectedQuestions"
              />
              <span class="question-text">{{ question.response_text }}</span>
              <span class="question-score">({{ question.response_score }} points)</span>
            </label>
          </div>
  
          <div class="form-actions">
            <button type="submit" class="btn-submit" :disabled="isSubmitting || selectedQuestions.length === 0">
              {{ isSubmitting ? 'Traitement en cours...' : 'Voir les résultats' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </template>
  
  <script lang="ts">
import { defineComponent, ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useQuestionnaireStore } from '../../stores/questionnaire';
import { useDiagnosticStore } from '../../stores/diagnostic';
import type { Question } from '../../types';

export default defineComponent({
 name: 'QuestionnaireView',
 setup() {
   const router = useRouter();
   const questionnaireStore = useQuestionnaireStore();
   const diagnosticStore = useDiagnosticStore();
   
   const selectedQuestions = ref<number[]>([]);
   const isSubmitting = ref(false);
   
   const questions = computed<Question[]>(() => {
     return questionnaireStore.currentQuestionnaire?.questions || [];
   });
   
   const loading = computed(() => questionnaireStore.loading);
   const error = computed(() => questionnaireStore.error);
   
   const fetchQuestionnaire = async () => {
     try {
       // Récupérer d'abord la liste des questionnaires
       await questionnaireStore.fetchQuestionnaires();
       
       // Puis récupérer le premier questionnaire (celui sur le stress)
       if (questionnaireStore.questionnaires.length > 0) {
         await questionnaireStore.fetchQuestionnaireById(questionnaireStore.questionnaires[0].id);
       }
     } catch (err) {
       console.error('Erreur lors du chargement du questionnaire:', err);
     }
   };
   
   const submitQuestionnaire = async () => {
     if (selectedQuestions.value.length === 0) {
       return;
     }
     
     isSubmitting.value = true;
     
     try {
       const response = await diagnosticStore.createDiagnostic(selectedQuestions.value);
       
       if (response.diagnostic) {
         router.push(`/diagnostic/${response.diagnostic.id}`);
       }
     } catch (err) {
       console.error('Erreur lors de la soumission du questionnaire:', err);
     } finally {
       isSubmitting.value = false;
     }
   };
   
   onMounted(() => {
     fetchQuestionnaire();
   });
   
   return {
     questions,
     selectedQuestions,
     loading,
     error,
     isSubmitting,
     submitQuestionnaire
   };
 }
});
</script>

<style scoped>
.questionnaire-container {
 max-width: 800px;
 margin: 0 auto;
 padding: 20px;
}

h1 {
 color: #333;
 margin-bottom: 20px;
}

.description {
 margin-bottom: 30px;
 line-height: 1.6;
 color: #666;
}

.loading, .error {
 text-align: center;
 padding: 30px;
 background-color: #f9f9f9;
 border-radius: 8px;
 margin-bottom: 20px;
}

.error {
 color: #e53935;
}

.questionnaire-form {
 background-color: #f9f9f9;
 padding: 20px;
 border-radius: 8px;
}

.question-item {
 margin-bottom: 15px;
 padding: 10px;
 background-color: white;
 border-radius: 4px;
 box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.question-label {
 display: flex;
 align-items: center;
 cursor: pointer;
}

.question-text {
 flex: 1;
 margin-left: 10px;
}

.question-score {
 margin-left: 10px;
 color: #777;
 font-size: 0.9em;
}

.form-actions {
 margin-top: 30px;
 text-align: center;
}

.btn-submit {
 padding: 12px 24px;
 background-color: #4CAF50;
 color: white;
 border: none;
 border-radius: 4px;
 font-size: 16px;
 cursor: pointer;
 transition: background-color 0.3s;
}

.btn-submit:hover {
 background-color: #45a049;
}

.btn-submit:disabled {
 background-color: #cccccc;
 cursor: not-allowed;
}
</style>