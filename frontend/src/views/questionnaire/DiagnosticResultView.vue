<template>
    <div class="diagnostic-result-container">
      <div v-if="loading" class="loading">
        <p>Chargement des résultats...</p>
      </div>
      
      <div v-else-if="error" class="error">
        <p>{{ error }}</p>
      </div>
      
      <div v-else-if="diagnostic" class="result-content">
        <h1>Résultat de votre diagnostic de stress</h1>
        
        <div class="score-container">
          <div class="score-circle">
            <span class="score-value">{{ diagnostic.score_total }}</span>
          </div>
          <div class="stress-level" :class="stressLevelClass">
            Niveau de stress {{ diagnostic.stress_level }}
          </div>
        </div>
        
        <div class="analysis-container">
          <div class="consequences">
            <h2>Conséquences potentielles</h2>
            <p>{{ diagnostic.consequences || 'Aucune conséquence spécifiée.' }}</p>
          </div>
          
          <div class="advices">
            <h2>Conseils et recommandations</h2>
            <p>{{ diagnostic.advices || 'Aucun conseil spécifié.' }}</p>
          </div>
        </div>
        
        <div class="actions">
          <router-link to="/diagnostics" class="btn-retry">Refaire le test</router-link>
          <router-link to="/" class="btn-home">Retour à l'accueil</router-link>
        </div>
      </div>
      
      <div v-else class="not-found">
        <p>Diagnostic non trouvé.</p>
        <router-link to="/diagnostics" class="btn-retry">Faire un diagnostic</router-link>
      </div>
    </div>
  </template>
  
  <script lang="ts">
  import { defineComponent, ref, computed, onMounted } from 'vue';
  import { useRoute } from 'vue-router';
  import { useDiagnosticStore } from '../../stores/diagnostic';
  
  export default defineComponent({
    name: 'DiagnosticResultView',
    setup() {
      const route = useRoute();
      const diagnosticStore = useDiagnosticStore();
      
      const loading = computed(() => diagnosticStore.loading);
      const error = computed(() => diagnosticStore.error);
      const diagnostic = computed(() => diagnosticStore.currentDiagnostic);
      
      const stressLevelClass = computed(() => {
        if (!diagnostic.value) return '';
        
        switch (diagnostic.value.stress_level.toLowerCase()) {
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
      
      const fetchDiagnostic = async () => {
        const diagnosticId = Number(route.params.id);
        if (!isNaN(diagnosticId)) {
          await diagnosticStore.fetchDiagnosticById(diagnosticId);
        }
      };
      
      onMounted(() => {
        fetchDiagnostic();
      });
      
      return {
        loading,
        error,
        diagnostic,
        stressLevelClass
      };
    }
  });
  </script>
  
  <style scoped>
  .diagnostic-result-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
  }
  
  h1 {
    color: #333;
    margin-bottom: 30px;
    text-align: center;
  }
  
  h2 {
    color: #444;
    margin-bottom: 15px;
  }
  
  .loading, .error, .not-found {
    text-align: center;
    padding: 30px;
    background-color: #f9f9f9;
    border-radius: 8px;
    margin-bottom: 20px;
  }
  
  .error {
    color: #e53935;
  }
  
  .score-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 40px;
  }
  
  .score-circle {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background-color: #4CAF50;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 20px;
  }
  
  .score-value {
    font-size: 3rem;
    font-weight: bold;
    color: white;
  }
  
  .stress-level {
    font-size: 1.5rem;
    font-weight: bold;
    padding: 8px 16px;
    border-radius: 4px;
  }
  
  .level-low {
    background-color: #c8e6c9;
    color: #2e7d32;
  }
  
  .level-medium {
    background-color: #fff9c4;
    color: #f57f17;
  }
  
  .level-high {
    background-color: #ffcdd2;
    color: #c62828;
  }
  
  .analysis-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 40px;
  }
  
  .consequences, .advices {
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
  }
  
  .actions {
    display: flex;
    justify-content: center;
    gap: 20px;
  }
  
  .btn-retry, .btn-home {
    padding: 10px 20px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: bold;
  }
  
  .btn-retry {
    background-color: #4CAF50;
    color: white;
  }
  
  .btn-home {
    background-color: #f5f5f5;
    color: #333;
  }
  
  @media (max-width: 768px) {
    .analysis-container {
      grid-template-columns: 1fr;
    }
    
    .actions {
      flex-direction: column;
      gap: 10px;
    }
    
    .btn-retry, .btn-home {
      text-align: center;
    }
  }
  </style>