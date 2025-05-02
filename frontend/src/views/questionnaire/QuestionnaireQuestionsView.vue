<template>
  <div class="questionnaire-container">
    <div class="content">
      <div v-if="loading" class="loading-spinner">
        <div class="spinner"></div>
        <p>Chargement des questions...</p>
      </div>

      <div v-else-if="error" class="error">
        <p>{{ error }}</p>
      </div>

      <div v-else-if="currentQuestion" class="question-content">
        <div class="question-card">
          <div class="question-text">
            {{ currentQuestion.response_text }}
          </div>

          <div class="answers">
            <button class="btn btn-yes" @click="answerQuestion(true)">OUI</button>
            <button class="btn btn-no" @click="answerQuestion(false)">NON</button>
          </div>

          <div class="progress">
            Question {{ currentQuestionIndex + 1 }} sur {{ questions.length }}
          </div>
        </div>
      </div>

      <div v-else class="no-questions">
        <p>Aucune question disponible pour ce questionnaire.</p>
        <button @click="goBack" class="back-button">Retour</button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useQuestionnaireStore } from '../../stores/questionnaire';
import { useDiagnosticStore } from '../../stores/diagnostic';
import type { Question } from '../../types';

export default defineComponent({
  name: 'QuestionnaireQuestionView',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const questionnaireStore = useQuestionnaireStore();
    const diagnosticStore = useDiagnosticStore();
    
    const currentQuestionIndex = ref(0);
    const answeredQuestions = ref<number[]>([]);
    
    const loading = computed(() => questionnaireStore.loading);
    const error = computed(() => questionnaireStore.error);
    
    const questions = computed<Question[]>(() => {
      return questionnaireStore.currentQuestionnaire?.questions || [];
    });
    
    const currentQuestion = computed<Question | null>(() => {
      if (questions.value.length === 0) return null;
      return questions.value[currentQuestionIndex.value];
    });
    
    const fetchQuestionnaire = async () => {
      const questionnaireId = Number(route.params.id);
      
      if (!isNaN(questionnaireId)) {
        try {
          await questionnaireStore.fetchQuestionnaireById(questionnaireId);
        } catch (err) {
          console.error('Erreur lors du chargement du questionnaire:', err);
        }
      }
    };
    
    const answerQuestion = async (answer: boolean) => {
      if (!currentQuestion.value) return;
      
      // Si la réponse est OUI, on ajoute l'ID de la question aux questions répondues
      if (answer) {
        answeredQuestions.value.push(currentQuestion.value.id);
      }
      
      // On passe à la question suivante
      if (currentQuestionIndex.value < questions.value.length - 1) {
        currentQuestionIndex.value++;
      } else {
        // Fin du questionnaire, on soumet les réponses
        await submitQuestionnaire();
      }
    };
    
    const submitQuestionnaire = async () => {
      if (!questionnaireStore.currentQuestionnaire) {
        console.error('Aucun questionnaire actif');
        return;
      }
      
      const questionnaireId = questionnaireStore.currentQuestionnaire.id;
      
      try {
        const response = await diagnosticStore.createDiagnostic(
          questionnaireId,
          answeredQuestions.value
        );
        
        if (response.diagnostic) {
          router.push(`/diagnostics/${response.diagnostic.id}`);
        }
      } catch (err) {
        console.error('Erreur lors de la soumission du questionnaire:', err);
      }
    };
    
    const goBack = () => {
      router.push('/questionnaires');
    };
    
    onMounted(() => {
      fetchQuestionnaire();
    });
    
    return {
      questions,
      currentQuestion,
      currentQuestionIndex,
      loading,
      error,
      answerQuestion,
      goBack
    };
  }
});
</script>

<style scoped>
.questionnaire-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #e0f7fa, #ffffff);
  padding: 20px;
}

.content {
  width: 100%;
  max-width: 500px;
}

.loading, .error, .no-questions {
  background: white;
  padding: 30px;
  border-radius: 16px;
  text-align: center;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
}

.error {
  color: #e53935;
}

.question-content {
  display: flex;
  justify-content: center;
}

.question-card {
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 30px 20px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  width: 100%;
  text-align: center;
}

.question-text {
  font-size: 20px;
  font-weight: 600;
  margin-bottom: 30px;
  color: #333;
}

.answers {
  display: flex;
  justify-content: space-around;
  margin-bottom: 20px;
}

.btn {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  font-size: 18px;
  font-weight: bold;
  cursor: pointer;
  border: none;
  transition: all 0.2s ease-in-out;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.btn:hover {
  transform: scale(1.05);
}

.btn-yes {
  background-color: #4caf50;
  color: white;
}

.btn-no {
  background-color: #f44336;
  color: white;
}

.progress {
  font-size: 14px;
  color: #555;
  margin-top: 10px;
}

.back-button {
  margin-top: 20px;
  padding: 10px 20px;
  background-color: #4caf50;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  transition: background 0.2s;
}

.back-button:hover {
  background-color: #43a047;
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