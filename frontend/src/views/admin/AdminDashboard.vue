<template>
  <div class="admin-dashboard">
    <h1>Tableau de bord d'administration</h1>
    
    <div class="dashboard-tabs">
      <button 
        class="tab-button" 
        :class="{ active: activeTab === 'users' }" 
        @click="activeTab = 'users'"
      >
        Utilisateurs
      </button>
      <button 
        class="tab-button" 
        :class="{ active: activeTab === 'questionnaires' }" 
        @click="activeTab = 'questionnaires'"
      >
        Questionnaires
      </button>
      <button 
        class="tab-button" 
        :class="{ active: activeTab === 'stress-levels' }" 
        @click="activeTab = 'stress-levels'"
      >
        Niveaux de Stress
      </button>
      <button 
        class="tab-button" 
        :class="{ active: activeTab === 'diagnostics' }" 
        @click="activeTab = 'diagnostics'"
      >
        Diagnostics
      </button>
      <button 
        class="tab-button" 
        :class="{ active: activeTab === 'statistics' }" 
        @click="activeTab = 'statistics'"
      >
        Statistiques
      </button>
    </div>
    
    <div class="tab-content">
      <!-- Onglet Utilisateurs -->
      <div v-if="activeTab === 'users'" class="users-tab">
        <h2>Gestion des utilisateurs</h2>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="6" class="text-center loading-cell">
                  <div class="loading-spinner">
                    <div class="spinner"></div>
                    <p>Chargement des utilisateurs...</p>
                  </div>
                </td>
              </tr>
              <tr v-else-if="users.length === 0">
                <td colspan="6" class="text-center">Aucun utilisateur trouvé</td>
              </tr>
              <tr v-for="user in users" :key="user.id">
                <td>{{ user.id }}</td>
                <td>{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td>{{ user.role?.name || 'N/A' }}</td>
                <td>
                  <span 
                    class="status-badge" 
                    :class="{ active: user.active, inactive: !user.active }"
                  >
                    {{ user.active ? 'Actif' : 'Inactif' }}
                  </span>
                </td>
                <td class="actions">
                  <button @click="editUser(user)" class="btn-edit">
                    <i class="fas fa-edit"></i> Modifier
                  </button>
                  <button @click="confirmDeleteUser(user)" class="btn-delete">
                    <i class="fas fa-trash"></i> Supprimer
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Onglet Questionnaires -->
      <div v-if="activeTab === 'questionnaires'" class="questionnaires-tab">
        <h2>Gestion des questionnaires</h2>
        <button @click="openAddQuestionnaireModal" class="btn-add">
          <i class="fas fa-plus"></i> Ajouter un questionnaire
        </button>
        
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Description</th>
                <th>Questions</th>
                <th>Statut</th>
                <th>Dernière modification</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="7" class="text-center loading-cell">
                  <div class="loading-spinner">
                    <div class="spinner"></div>
                    <p>Chargement des questionnaires...</p>
                  </div>
                </td>
              </tr>
              <tr v-else-if="questionnaires.length === 0">
                <td colspan="7" class="text-center">Aucun questionnaire trouvé</td>
              </tr>
              <tr v-for="questionnaire in questionnaires" :key="questionnaire.id">
                <td>{{ questionnaire.id }}</td>
                <td>{{ questionnaire.title }}</td>
                <td>{{ truncateText(questionnaire.description, 50) }}</td>
                <td>{{ questionnaire.nb_question }}</td>
                <td>
                  <span 
                    class="status-badge" 
                    :class="{ active: questionnaire.active, inactive: !questionnaire.active }"
                  >
                    {{ questionnaire.active ? 'Actif' : 'Inactif' }}
                  </span>
                </td>
                <td>{{ formatDate(questionnaire.last_modification) }}</td>
                <td class="actions">
                  <button @click="viewQuestions(questionnaire)" class="btn-view" title="Voir les questions">
                    <i class="fas fa-list"></i>
                  </button>
                  <button @click="editQuestionnaire(questionnaire)" class="btn-edit" title="Modifier">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button @click="confirmDeleteQuestionnaire(questionnaire)" class="btn-delete" title="Supprimer">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Onglet Niveaux de Stress -->
      <div v-if="activeTab === 'stress-levels'" class="stress-levels-tab">
        <h2>Gestion des niveaux de stress</h2>
        <button @click="openAddStressLevelModal" class="btn-add">
          <i class="fas fa-plus"></i> Ajouter un niveau de stress
        </button>
        
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Plage de score</th>
                <th>Risque (%)</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="6" class="text-center loading-cell">
                  <div class="loading-spinner">
                    <div class="spinner"></div>
                    <p>Chargement des niveaux de stress...</p>
                  </div>
                </td>
              </tr>
              <tr v-else-if="stressLevels.length === 0">
                <td colspan="6" class="text-center">Aucun niveau de stress trouvé</td>
              </tr>
              <tr v-for="level in stressLevels" :key="level.id">
                <td>{{ level.id }}</td>
                <td>{{ level.name }}</td>
                <td>{{ level.min_score }} - {{ level.max_score }}</td>
                <td>{{ level.risk_percentage }}%</td>
                <td>
                  <span 
                    class="status-badge" 
                    :class="{ active: level.active, inactive: !level.active }"
                  >
                    {{ level.active ? 'Actif' : 'Inactif' }}
                  </span>
                </td>
                <td class="actions">
                  <button @click="viewRecommendations(level)" class="btn-view" title="Voir les recommandations">
                    <i class="fas fa-list"></i>
                  </button>
                  <button @click="editStressLevel(level)" class="btn-edit" title="Modifier">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button @click="confirmDeleteStressLevel(level)" class="btn-delete" title="Supprimer">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Onglet Diagnostics -->
      <div v-if="activeTab === 'diagnostics'" class="diagnostics-tab">
        <h2>Tous les diagnostics</h2>
        
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Utilisateur</th>
                <th>Questionnaire</th>
                <th>Score</th>
                <th>Niveau</th>
                <th>Date</th>
                <th>Sauvegardé</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="8" class="text-center loading-cell">
                  <div class="loading-spinner">
                    <div class="spinner"></div>
                    <p>Chargement des diagnostics...</p>
                  </div>
                </td>
              </tr>
              <tr v-else-if="diagnostics.length === 0">
                <td colspan="8" class="text-center">Aucun diagnostic trouvé</td>
              </tr>
              <tr v-for="diagnostic in diagnostics" :key="diagnostic.id">
                <td>{{ diagnostic.id }}</td>
                <td>{{ getUserName(diagnostic.user_id) }}</td>
                <td>{{ getQuestionnaireName(diagnostic.questionnaire_id) }}</td>
                <td>{{ diagnostic.score_total }}</td>
                <td>
                  <span 
                    class="stress-level-badge" 
                    :class="getStressLevelClass(diagnostic.stress_level)"
                  >
                    {{ diagnostic.stress_level }}
                  </span>
                </td>
                <td>{{ formatDate(diagnostic.diagnostic_date) }}</td>
                <td>
                  <span 
                    class="status-badge" 
                    :class="{ active: diagnostic.saved, inactive: !diagnostic.saved }"
                  >
                    {{ diagnostic.saved ? 'Oui' : 'Non' }}
                  </span>
                </td>
                <td class="actions">
                  <button @click="viewDiagnostic(diagnostic)" class="btn-view" title="Voir détails">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button @click="confirmDeleteDiagnostic(diagnostic)" class="btn-delete" title="Supprimer">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Onglet Statistiques -->
      <div v-if="activeTab === 'statistics'" class="statistics-tab">
        <h2>Statistiques générales</h2>
        <div class="stats-cards">
          <div class="stat-card">
            <h3>Utilisateurs</h3>
            <p class="stat-value">{{ users.length }}</p>
          </div>
          <div class="stat-card">
            <h3>Questionnaires</h3>
            <p class="stat-value">{{ questionnaires.length }}</p>
          </div>
          <div class="stat-card">
            <h3>Diagnostics</h3>
            <p class="stat-value">{{ diagnostics.length }}</p>
          </div>
          <div class="stat-card">
            <h3>Diagnostics sauvegardés</h3>
            <p class="stat-value">{{ savedDiagnosticsCount }}</p>
          </div>
        </div>
        
        <div class="charts-container">
          <div class="chart">
            <h3>Répartition des niveaux de stress</h3>
            <div class="stress-distribution">
              <div 
                v-for="(count, level) in stressLevelDistribution" 
                :key="level"
                class="stress-bar-container"
              >
                <span class="stress-level-label">{{ level }}</span>
                <div class="stress-bar-wrapper">
                  <div 
                    class="stress-bar" 
                    :class="getStressLevelClass(level)"
                    :style="{ width: `${(count / diagnostics.length) * 100}%` }"
                  ></div>
                  <span class="stress-count">{{ count }}</span>
                </div>
              </div>
            </div>
          </div>
          
          <div class="chart">
            <h3>Score moyen par questionnaire</h3>
            <div class="questionnaire-scores">
              <div 
                v-for="(data, id) in questionnaireScores" 
                :key="id"
                class="questionnaire-score-item"
              >
                <span class="questionnaire-name">{{ data.name }}</span>
                <div class="score-bar-wrapper">
                  <div 
                    class="score-bar"
                    :style="{ width: `${(data.avgScore / maxAvgScore) * 100}%` }"
                  ></div>
                  <span class="score-value">{{ Math.round(data.avgScore) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Modaux de confirmation et d'édition seraient ajoutés ici -->
    
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';
import type { User, Questionnaire, Diagnostic, StressLevel } from '../../types';

export default defineComponent({
  name: 'AdminDashboard',
  setup() {
    const router = useRouter();
    const activeTab = ref('users');
    const loading = ref(false);
    
    // États
    const users = ref<User[]>([]);
    const questionnaires = ref<Questionnaire[]>([]);
    const diagnostics = ref<Diagnostic[]>([]);
    const stressLevels = ref<StressLevel[]>([]);
    
    // Récupération des données depuis l'API
    const fetchUsers = async () => {
      loading.value = true;
      try {
        const response = await api.get<User[]>('/users');
        users.value = response;
      } catch (error) {
        console.error('Erreur lors du chargement des utilisateurs:', error);
      } finally {
        loading.value = false;
      }
    };
    
    const fetchQuestionnaires = async () => {
      loading.value = true;
      try {
        const response = await api.get<Questionnaire[]>('/questionnaires');
        questionnaires.value = response;
      } catch (error) {
        console.error('Erreur lors du chargement des questionnaires:', error);
      } finally {
        loading.value = false;
      }
    };
    
    const fetchDiagnostics = async () => {
      loading.value = true;
      try {
        // Pour l'admin, nous voulons récupérer tous les diagnostics
        // Note: Vous devrez probablement créer un endpoint spécifique dans l'API
        const response = await api.get<Diagnostic[]>('/admin/diagnostics');
        diagnostics.value = response;
      } catch (error) {
        console.error('Erreur lors du chargement des diagnostics:', error);
      } finally {
        loading.value = false;
      }
    };
    
    const fetchStressLevels = async () => {
      loading.value = true;
      try {
        // Note: Vous devrez créer un endpoint pour récupérer les niveaux de stress
        const response = await api.get<StressLevel[]>('/admin/stress-levels');
        stressLevels.value = response;
      } catch (error) {
        console.error('Erreur lors du chargement des niveaux de stress:', error);
      } finally {
        loading.value = false;
      }
    };
    
    // Computed properties pour les statistiques
    const savedDiagnosticsCount = computed(() => {
      return diagnostics.value.filter(d => d.saved).length;
    });
    
    const stressLevelDistribution = computed(() => {
      const distribution: Record<string, number> = {};
      
      diagnostics.value.forEach(diagnostic => {
        const level = diagnostic.stress_level;
        if (!distribution[level]) {
          distribution[level] = 0;
        }
        distribution[level]++;
      });
      
      return distribution;
    });
    
    const questionnaireScores = computed(() => {
      const scores: Record<number, { name: string, totalScore: number, count: number, avgScore: number }> = {};
      
      diagnostics.value.forEach(diagnostic => {
        const qId = diagnostic.questionnaire_id;
        if (!qId) return;
        
        if (!scores[qId]) {
          const questionnaire = questionnaires.value.find(q => q.id === qId);
          scores[qId] = {
            name: questionnaire ? questionnaire.title : `Questionnaire #${qId}`,
            totalScore: 0,
            count: 0,
            avgScore: 0
          };
        }
        
        scores[qId].totalScore += diagnostic.score_total;
        scores[qId].count++;
      });
      
      // Calculer les moyennes
      Object.keys(scores).forEach(key => {
        const id = Number(key);
        if (scores[id].count > 0) {
          scores[id].avgScore = scores[id].totalScore / scores[id].count;
        }
      });
      
      return scores;
    });
    
    const maxAvgScore = computed(() => {
      const scores = Object.values(questionnaireScores.value);
      if (scores.length === 0) return 100; // Valeur par défaut
      
      return Math.max(...scores.map(score => score.avgScore));
    });
    
    // Helpers
    const formatDate = (dateString: string) => {
      if (!dateString) return 'N/A';
      
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }).format(date);
    };
    
    const truncateText = (text?: string, maxLength: number = 50) => {
      if (!text) return 'N/A';
      if (text.length <= maxLength) return text;
      
      return text.substring(0, maxLength) + '...';
    };
    
    const getUserName = (userId: number) => {
      const user = users.value.find(u => u.id === userId);
      return user ? user.name : `Utilisateur #${userId}`;
    };
    
    const getQuestionnaireName = (questionnaireId?: number) => {
      if (!questionnaireId) return 'Non spécifié';
      
      const questionnaire = questionnaires.value.find(q => q.id === questionnaireId);
      return questionnaire ? questionnaire.title : `Questionnaire #${questionnaireId}`;
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
    
    // Actions
    const viewQuestions = (questionnaire: Questionnaire) => {
      // Rediriger vers une page de gestion des questions pour ce questionnaire
      router.push(`/admin/questionnaires/${questionnaire.id}/questions`);
    };
    
    const viewRecommendations = (level: StressLevel) => {
      // Rediriger vers une page de gestion des recommandations pour ce niveau
      router.push(`/admin/stress-levels/${level.id}/recommendations`);
    };
    
    const viewDiagnostic = (diagnostic: Diagnostic) => {
      // Rediriger vers la page de détail d'un diagnostic
      router.push(`/diagnostics/${diagnostic.id}`);
    };
    
    // Ces fonctions seraient implémentées pour la gestion complète
    const editUser = (user: User) => {
      // Ouvrir une modale d'édition ou rediriger vers une page d'édition
      console.log('Éditer utilisateur:', user);
    };
    
    const confirmDeleteUser = (user: User) => {
      // Ouvrir une modale de confirmation
      console.log('Confirmer suppression utilisateur:', user);
    };
    
    const editQuestionnaire = (questionnaire: Questionnaire) => {
      console.log('Éditer questionnaire:', questionnaire);
    };
    
    const confirmDeleteQuestionnaire = (questionnaire: Questionnaire) => {
      console.log('Confirmer suppression questionnaire:', questionnaire);
    };
    
    const editStressLevel = (level: StressLevel) => {
      console.log('Éditer niveau de stress:', level);
    };
    
    const confirmDeleteStressLevel = (level: StressLevel) => {
      console.log('Confirmer suppression niveau de stress:', level);
    };
    
    const confirmDeleteDiagnostic = (diagnostic: Diagnostic) => {
      console.log('Confirmer suppression diagnostic:', diagnostic);
    };
    
    const openAddQuestionnaireModal = () => {
      console.log('Ouvrir modale d\'ajout de questionnaire');
    };
    
    const openAddStressLevelModal = () => {
      console.log('Ouvrir modale d\'ajout de niveau de stress');
    };
    
    // Initialisation
    onMounted(() => {
      fetchUsers();
      fetchQuestionnaires();
      fetchDiagnostics();
      fetchStressLevels();
    });
    
    // Retour des variables et fonctions à exposer au template
    return {
      activeTab,
      loading,
      users,
      questionnaires,
      diagnostics,
      stressLevels,
      savedDiagnosticsCount,
      stressLevelDistribution,
      questionnaireScores,
      maxAvgScore,
      formatDate,
      truncateText,
      getUserName,
      getQuestionnaireName,
      getStressLevelClass,
      viewQuestions,
      viewRecommendations,
      viewDiagnostic,
      editUser,
      confirmDeleteUser,
      editQuestionnaire,
      confirmDeleteQuestionnaire,
      editStressLevel,
      confirmDeleteStressLevel,
      confirmDeleteDiagnostic,
      openAddQuestionnaireModal,
      openAddStressLevelModal
    };
  }
});
</script>

<style scoped>
.admin-dashboard {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

h1 {
  margin-bottom: 30px;
  color: #333;
}

.dashboard-tabs {
  display: flex;
  flex-wrap: wrap;
  margin-bottom: 20px;
  gap: 5px;
}

.tab-button {
  padding: 10px 20px;
  background-color: #f5f5f5;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 16px;
  transition: all 0.2s ease;
}

.tab-button.active {
  background-color: #4CAF50;
  color: white;
}

.tab-content {
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

h2 {
  margin-bottom: 20px;
  color: #333;
  display: flex;
  align-items: center;
}

.btn-add {
  padding: 8px 16px;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 4px;
  margin-bottom: 20px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 5px;
  transition: background-color 0.2s;
}

.btn-add:hover {
  background-color: #388E3C;
}

.table-responsive {
  overflow-x: auto;
  margin-bottom: 20px;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th,
.data-table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

.data-table th {
  background-color: #f5f5f5;
  font-weight: 600;
}

.data-table tr:hover {
  background-color: #f9f9f9;
}

.data-table .text-center {
  text-align: center;
}

.loading-cell {
  padding: 30px !important;
}

.loading-spinner {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 20px;
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

.status-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
  display: inline-block;
}

.status-badge.active {
  background-color: #c8e6c9;
  color: #2e7d32;
}

.status-badge.inactive {
  background-color: #ffcdd2;
  color: #c62828;
}

.stress-level-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
  display: inline-block;
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

.actions {
  display: flex;
  gap: 8px;
}

.btn-view,
.btn-edit,
.btn-delete {
  padding: 6px 10px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 5px;
  transition: background-color 0.2s;
}

.btn-view {
  background-color: #2196F3;
  color: white;
}

.btn-view:hover {
  background-color: #1976D2;
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

/* Stats styles */
.stats-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background-color: #f5f5f5;
  padding: 20px;
  border-radius: 8px;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.stat-card h3 {
  font-size: 16px;
  margin-bottom: 10px;
  color: #555;
}

.stat-value {
  font-size: 2rem;
  font-weight: bold;
  color: #4CAF50;
  margin: 0;
}

.charts-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(500px, 1fr));
  gap: 20px;
}

.chart {
  background-color: #f9f9f9;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.chart h3 {
  margin-top: 0;
  margin-bottom: 20px;
  font-size: 18px;
  color: #333;
}

/* Graphique de distribution des niveaux de stress */
.stress-distribution {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.stress-bar-container {
  display: flex;
  align-items: center;
}

.stress-level-label {
  width: 80px;
  font-weight: 500;
}

.stress-bar-wrapper {
  flex: 1;
  height: 25px;
  background-color: #f0f0f0;
  border-radius: 4px;
  overflow: hidden;
  position: relative;
  margin: 0 10px;
}

.stress-bar {
  height: 100%;
  background-color: #4CAF50;
  transition: width 0.5s ease;
}

.stress-bar.level-low {
  background-color: #81C784;
}

.stress-bar.level-medium {
  background-color: #FFB74D;
}

.stress-bar.level-high {
  background-color: #E57373;
}

.stress-count {
  min-width: 30px;
  text-align: right;
  font-weight: 500;
}

/* Graphique de score moyen par questionnaire */
.questionnaire-scores {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.questionnaire-score-item {
  display: flex;
  align-items: center;
}

.questionnaire-name {
  width: 200px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-weight: 500;
}

.score-bar-wrapper {
  flex: 1;
  height: 25px;
  background-color: #f0f0f0;
  border-radius: 4px;
  overflow: hidden;
  position: relative;
  margin: 0 10px;
}

.score-bar {
  height: 100%;
  background-color: #7986CB;
  transition: width 0.5s ease;
}

.score-value {
  min-width: 30px;
  text-align: right;
  font-weight: 500;
}

@media (max-width: 768px) {
  .dashboard-tabs {
    flex-direction: column;
    width: 100%;
  }
  
  .tab-button {
    width: 100%;
    text-align: center;
  }
  
  .charts-container {
    grid-template-columns: 1fr;
  }
  
  .questionnaire-name {
    width: 120px;
  }
}
</style>