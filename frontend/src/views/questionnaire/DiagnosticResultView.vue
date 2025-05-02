<template>
  <div class="diagnostic-result-container">
    <h1 class="result-title">Score de stress</h1>
    
    <div v-if="loading" class="loading-spinner">
      <div class="spinner"></div>
      <p>Chargement des résultats...</p>
    </div>
    
    <div v-else-if="error" class="error-container">
      <p>{{ error }}</p>
      <button @click="goBack" class="btn-secondary">Retour</button>
    </div>
    
    <div v-else-if="diagnostic" class="result-content">
      <!-- Score Circle -->
      <div class="score-circle">
        <span class="score-value">{{ diagnostic.score_total }}</span>
      </div>
      
      <!-- Risk Section -->
      <div class="risk-section">
        <h2 class="risk-title">Risque détecté : {{ diagnostic.stress_level }}</h2>
        
        <p class="risk-subtitle">
          Risque estimé : <strong>{{ getRiskPercentage() }}% de probabilité</strong> 
          de tomber malade dans les deux ans
        </p>
        
        <div class="consequences">
          <p><strong>Conséquences possibles</strong> : {{ diagnostic.consequences }}</p>
        </div>
      </div>
      
      <!-- Solutions Section -->
      <div class="solutions-section">
        <h2 class="solutions-title">Solutions recommandées</h2>
        
        <ul class="solutions-list">
          <li v-for="(recommendation, index) in recommendations" :key="index">
            <span class="checkmark">✓</span> {{ recommendation.description }}
          </li>
        </ul>
      </div>
      
      <!-- Actions -->
      <div class="actions">
        <button @click="goToQuestionnaire" class="btn-primary">Faire un nouveau test</button>
        <button @click="saveDiagnostic" v-if="!diagnostic.saved" class="btn-save">
          <i class="fas fa-save"></i> Sauvegarder ce résultat
        </button>
        <button disabled v-else class="btn-saved">
          <i class="fas fa-check"></i> Résultat sauvegardé
        </button>
        <button @click="goToHistory" class="btn-history">
          <i class="fas fa-history"></i> Voir l'historique
        </button>
        <button @click="goBack" class="btn-secondary">Retour</button>
      </div>
    </div>
    
    <div v-else class="not-found">
      <p>Diagnostic non trouvé.</p>
      <button @click="goToQuestionnaire" class="btn-primary">Faire un diagnostic</button>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useDiagnosticStore } from '../../stores/diagnostic';
import type { Recommendation } from '../../types';

export default defineComponent({
  name: 'DiagnosticResultView',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const diagnosticStore = useDiagnosticStore();
    
    const loading = computed(() => diagnosticStore.loading);
    const error = computed(() => diagnosticStore.error);
    const diagnostic = computed(() => diagnosticStore.currentDiagnostic);
    const stressLevel = computed(() => diagnosticStore.currentStressLevel);
    const recommendations = computed<Recommendation[]>(() => {
      // Si nous avons des recommandations du backend, les utiliser
      if (diagnosticStore.currentRecommendations && diagnosticStore.currentRecommendations.length > 0) {
        return diagnosticStore.currentRecommendations;
      }
      
      // Sinon, créer des recommandations à partir de la chaîne advices
      if (diagnostic.value?.advices) {
        const advicesList = diagnostic.value.advices.split(', ');
        return advicesList.map((advice, index) => ({
          id: index,
          stress_level_id: 0,
          description: advice,
          order: index + 1,
          active: true,
          created_at: '',
          updated_at: ''
        }));
      }
      
      return [];
    });
    
    const fetchDiagnostic = async () => {
      const diagnosticId = Number(route.params.id);
      if (!isNaN(diagnosticId)) {
        await diagnosticStore.fetchDiagnosticById(diagnosticId);
      }
    };
    
    const getRiskPercentage = () => {
      if (stressLevel.value) {
        return stressLevel.value.risk_percentage;
      }
      
      // Fallback basé sur l'échelle de Holmes et Rahe
      const score = diagnostic.value?.score_total || 0;
      if (score < 150) {
        return 37;
      } else if (score <= 300) {
        return 50;
      } else {
        return 80;
      }
    };
    
    const saveDiagnostic = async () => {
  if (!diagnostic.value) return;
  
  try {
    // Utiliser la nouvelle méthode de sauvegarde spécifique
    await diagnosticStore.saveDiagnostic(diagnostic.value.id);
    
    // Recharger le diagnostic pour avoir les données à jour
    await diagnosticStore.fetchDiagnosticById(diagnostic.value.id);
  } catch (err) {
    console.error('Erreur lors de la sauvegarde du diagnostic:', err);
    alert('Une erreur est survenue lors de la sauvegarde du diagnostic');
  }
};
    
    const goToQuestionnaire = () => {
      router.push('/questionnaires');
    };
    
    const goToHistory = () => {
      router.push('/history');
    };
    
    const goBack = () => {
      router.back();
    };
    
    onMounted(() => {
      fetchDiagnostic();
    });
    
    return {
      diagnostic,
      stressLevel,
      recommendations,
      loading,
      error,
      getRiskPercentage,
      saveDiagnostic,
      goToQuestionnaire,
      goToHistory,
      goBack
    };
  }
});
</script>

<style scoped>
.diagnostic-result-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
  font-family: Arial, sans-serif;
}

.result-title {
  text-align: center;
  color: #333;
  margin-bottom: 40px;
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

.error-container, .not-found {
  text-align: center;
  margin: 40px 0;
  padding: 20px;
  background-color: #f9f9f9;
  border-radius: 8px;
}

.result-content {
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* Score Circle */
.score-circle {
  width: 150px;
  height: 150px;
  background-color: #4CAF50;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 40px;
}

.score-value {
  color: white;
  font-size: 48px;
  font-weight: bold;
}

/* Risk Section */
.risk-section {
  width: 100%;
  background-color: #FFF8E1;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 30px;
  border-left: 4px solid #FFA000;
}

.risk-title {
  color: #E65100;
  margin-top: 0;
  margin-bottom: 15px;
  font-size: 24px;
}

.risk-subtitle {
  margin: 10px 0;
  font-size: 16px;
}

.consequences {
  margin-top: 15px;
  font-size: 16px;
  line-height: 1.5;
}

/* Solutions Section */
.solutions-section {
  width: 100%;
  background-color: #E8F5E9;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 30px;
  border-left: 4px solid #4CAF50;
}

.solutions-title {
  color: #2E7D32;
  margin-top: 0;
  margin-bottom: 15px;
  font-size: 24px;
}

.solutions-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.solutions-list li {
  margin-bottom: 10px;
  font-size: 16px;
  display: flex;
  align-items: flex-start;
}

.checkmark {
  color: #4CAF50;
  font-weight: bold;
  margin-right: 10px;
  font-size: 18px;
}

/* Actions */
.actions {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  margin-top: 20px;
  justify-content: center;
}

.btn-primary, .btn-secondary, .btn-save, .btn-saved, .btn-history {
  padding: 10px 15px;
  border: none;
  border-radius: 4px;
  font-size: 14px;
  cursor: pointer;
  transition: background-color 0.3s;
  display: flex;
  align-items: center;
  gap: 5px;
}

.btn-primary {
  background-color: #4CAF50;
  color: white;
}

.btn-primary:hover {
  background-color: #388E3C;
}

.btn-secondary {
  background-color: #F5F5F5;
  color: #333;
  border: 1px solid #DDD;
}

.btn-secondary:hover {
  background-color: #EEEEEE;
}

.btn-save {
  background-color: #2196F3;
  color: white;
}

.btn-save:hover {
  background-color: #1976D2;
}

.btn-saved {
  background-color: #8BC34A;
  color: white;
  cursor: default;
}

.btn-history {
  background-color: #9C27B0;
  color: white;
}

.btn-history:hover {
  background-color: #7B1FA2;
}

@media (max-width: 600px) {
  .actions {
    flex-direction: column;
    width: 100%;
  }
  
  .btn-primary, .btn-secondary, .btn-save, .btn-saved, .btn-history {
    width: 100%;
    justify-content: center;
  }
}
</style>