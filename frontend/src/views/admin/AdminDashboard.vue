<template>
  <div class="admin-dashboard">
    <h1>Tableau de bord d'administration</h1>
    
    <div class="dashboard-tabs">
      <button 
        class="tab-button" 
        :class="{ active: activeTab === 'users' }" 
        @click="activeTab = 'users'; fetchUsers()"
      >
        Utilisateurs
      </button>
      <button 
        class="tab-button" 
        :class="{ active: activeTab === 'questionnaires' }" 
        @click="activeTab = 'questionnaires'; fetchQuestionnaires()"
      >
        Questionnaires
      </button>
      <button 
        class="tab-button" 
        :class="{ active: activeTab === 'stress-levels' }" 
        @click="activeTab = 'stress-levels'; fetchStressLevels()"
      >
        Niveaux de Stress
      </button>
      <button 
        class="tab-button" 
        :class="{ active: activeTab === 'diagnostics' }" 
        @click="activeTab = 'diagnostics'; fetchDiagnostics()"
      >
        Diagnostics
      </button>
      <button 
        class="tab-button" 
        :class="{ active: activeTab === 'statistics' }" 
        @click="activeTab = 'statistics'; fetchStatistics()"
      >
        Statistiques
      </button>
    </div>
    
    <div class="tab-content">
      <!-- Onglet Utilisateurs -->
      <div v-if="activeTab === 'users'" class="users-tab">
        <h2>Gestion des utilisateurs</h2>
        <button @click="openAddUserModal" class="btn-add">
          <i class="fas fa-plus"></i> Ajouter un utilisateur
        </button>
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
        <div v-if="loading" class="loading-spinner">
          <div class="spinner"></div>
          <p>Chargement des statistiques...</p>
        </div>

        <div v-else>
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
              <div v-if="!Object.keys(stressLevelDistribution).length" class="empty-chart">
                <p>Aucune donnée disponible</p>
              </div>
              <div v-else class="stress-distribution">
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
              <div v-if="!Object.keys(questionnaireScores).length" class="empty-chart">
                <p>Aucune donnée disponible</p>
              </div>
              <div v-else class="questionnaire-scores">
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
    </div>
    
    <!-- Modal pour ajouter/modifier un niveau de stress -->
    <div v-if="showStressLevelModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>{{ isEditing ? 'Modifier le niveau de stress' : 'Ajouter un niveau de stress' }}</h2>
          <button @click="closeStressLevelModal" class="close-btn">&times;</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveStressLevel">
            <div class="form-group">
              <label for="name">Nom</label>
              <input 
                type="text" 
                id="name" 
                v-model="stressLevelForm.name" 
                required
              />
            </div>
            <div class="form-group">
              <label for="min_score">Score minimum</label>
              <input 
                type="number" 
                id="min_score" 
                v-model.number="stressLevelForm.min_score" 
                required
                min="0"
              />
            </div>
            <div class="form-group">
              <label for="max_score">Score maximum</label>
              <input 
                type="number" 
                id="max_score" 
                v-model.number="stressLevelForm.max_score" 
                required
                min="0"
              />
            </div>
            <div class="form-group">
              <label for="risk_percentage">Pourcentage de risque</label>
              <input 
                type="number" 
                id="risk_percentage" 
                v-model.number="stressLevelForm.risk_percentage" 
                required
                min="0"
                max="100"
              />
            </div>
            <div class="form-group">
              <label for="description">Description</label>
              <textarea 
                id="description" 
                v-model="stressLevelForm.description" 
                rows="3"
              ></textarea>
            </div>
            <div class="form-group">
              <label for="consequences">Conséquences</label>
              <textarea 
                id="consequences" 
                v-model="stressLevelForm.consequences" 
                rows="3"
              ></textarea>
            </div>
            <div class="form-group">
              <label class="checkbox-label">
                <input type="checkbox" v-model="stressLevelForm.active">
                Actif
              </label>
            </div>
            <div class="form-actions">
              <button type="button" @click="closeStressLevelModal" class="btn-secondary">Annuler</button>
              <button type="submit" class="btn-primary">{{ isEditing ? 'Enregistrer' : 'Ajouter' }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal pour ajouter/modifier un questionnaire -->
    <div v-if="showQuestionnaireModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>{{ isEditing ? 'Modifier le questionnaire' : 'Ajouter un questionnaire' }}</h2>
          <button @click="closeQuestionnaireModal" class="close-btn">&times;</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveQuestionnaire">
            <div class="form-group">
              <label for="title">Titre</label>
              <input 
                type="text" 
                id="title" 
                v-model="questionnaireForm.title" 
                required
              />
            </div>
            <div class="form-group">
              <label for="description">Description</label>
              <textarea 
                id="description" 
                v-model="questionnaireForm.description" 
                rows="3"
              ></textarea>
            </div>
            <div class="form-group">
              <label class="checkbox-label">
                <input type="checkbox" v-model="questionnaireForm.active">
                Actif
              </label>
            </div>
            <div class="form-actions">
              <button type="button" @click="closeQuestionnaireModal" class="btn-secondary">Annuler</button>
              <button type="submit" class="btn-primary">{{ isEditing ? 'Enregistrer' : 'Ajouter' }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal pour ajouter/modifier un utilisateur -->
    <div v-if="showUserModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>{{ isEditing ? 'Modifier l\'utilisateur' : 'Ajouter un utilisateur' }}</h2>
          <button @click="closeUserModal" class="close-btn">&times;</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveUser">
            <div class="form-group">
              <label for="user_name">Nom</label>
              <input 
                type="text" 
                id="user_name" 
                v-model="userForm.name" 
                required
              />
            </div>
            <div class="form-group">
              <label for="email">Email</label>
              <input 
                type="email" 
                id="email" 
                v-model="userForm.email" 
                required
              />
            </div>
            <div class="form-group" v-if="!isEditing">
              <label for="password">Mot de passe</label>
              <input 
                type="password" 
                id="password" 
                v-model="userForm.password" 
                required
              />
            </div>
            <div class="form-group" v-if="!isEditing">
              <label for="password_confirmation">Confirmer le mot de passe</label>
              <input 
                type="password" 
                id="password_confirmation" 
                v-model="userForm.password_confirmation" 
                required
              />
            </div>
            <div class="form-group">
              <label for="role_id">Rôle</label>
              <select 
                id="role_id" 
                v-model="userForm.role_id" 
                required
              >
                <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
              </select>
            </div>
            <div class="form-group">
              <label class="checkbox-label">
                <input type="checkbox" v-model="userForm.active">
                Actif
              </label>
            </div>
            <div class="form-actions">
              <button type="button" @click="closeUserModal" class="btn-secondary">Annuler</button>
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
          <p>{{ deleteConfirmationMessage }}</p>
          <div class="form-actions">
            <button @click="cancelDelete" class="btn-secondary">Annuler</button>
            <button @click="confirmDelete" class="btn-delete">Supprimer</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';
import type { User, Questionnaire, Diagnostic, StressLevel, Role } from '../../types';

export default defineComponent({
  name: 'AdminDashboard',
  setup() {
    const router = useRouter();
    const activeTab = ref('users');
    const loading = ref(false);
    const error = ref<string | null>(null);
    
    // États
    const users = ref<User[]>([]);
    const questionnaires = ref<Questionnaire[]>([]);
    const diagnostics = ref<Diagnostic[]>([]);
    const stressLevels = ref<StressLevel[]>([]);
    const roles = ref<Role[]>([
      { id: 1, name: 'admin', description: 'Administrateur' },
      { id: 2, name: 'user', description: 'Utilisateur' }
    ]);
    
    // États pour les modals
    const showUserModal = ref(false);
    const showQuestionnaireModal = ref(false);
    const showStressLevelModal = ref(false);
    const showDeleteConfirmation = ref(false);
    const isEditing = ref(false);
    const deleteConfirmationMessage = ref('');
    const currentItemToDelete = ref<any>(null);
    const deleteItemType = ref<string>('');

    // Formulaires
    const userForm = ref({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      role_id: 2,
      active: true
    });

    const questionnaireForm = ref({
      title: '',
      description: '',
      active: true
    });

    const stressLevelForm = ref({
      name: '',
      min_score: 0,
      max_score: 0,
      risk_percentage: 0,
      description: '',
      consequences: '',
      active: true
    });
    
    // Chargement des utilisateurs
    const fetchUsers = async () => {
      loading.value = true;
      error.value = null;
      
      try {
        users.value = await api.get<User[]>('/users');
        console.log('Utilisateurs chargés:', users.value);
      } catch (err: any) {
        console.error('Erreur lors du chargement des utilisateurs:', err);
        error.value = err.message || 'Une erreur est survenue lors du chargement des utilisateurs';
      } finally {
        loading.value = false;
      }
    };
    
    // Chargement des questionnaires
    const fetchQuestionnaires = async () => {
      loading.value = true;
      error.value = null;
      
      try {
        // Récupérer tous les questionnaires (pas seulement les actifs)
        questionnaires.value = await api.get<Questionnaire[]>('/questionnaires');
        console.log('Questionnaires chargés:', questionnaires.value);
      } catch (err: any) {
        console.error('Erreur lors du chargement des questionnaires:', err);
        error.value = err.message || 'Une erreur est survenue lors du chargement des questionnaires';
      } finally {
        loading.value = false;
      }
    };
    
    // Chargement des diagnostics
    const fetchDiagnostics = async () => {
      loading.value = true;
      error.value = null;
      
      try {
        // Utiliser l'endpoint spécifique pour l'administrateur
        diagnostics.value = await api.get<Diagnostic[]>('/admin/diagnostics');
        console.log('Diagnostics chargés:', diagnostics.value);
      } catch (err: any) {
        console.error('Erreur lors du chargement des diagnostics:', err);
        error.value = err.message || 'Une erreur est survenue lors du chargement des diagnostics';
      } finally {
        loading.value = false;
      }
    };
    
    // Chargement des niveaux de stress
    const fetchStressLevels = async () => {
      loading.value = true;
      error.value = null;
      
      try {
        stressLevels.value = await api.get<StressLevel[]>('/admin/stress-levels');
        console.log('Niveaux de stress chargés:', stressLevels.value);
      } catch (err: any) {
        console.error('Erreur lors du chargement des niveaux de stress:', err);
        error.value = err.message || 'Une erreur est survenue lors du chargement des niveaux de stress';
      } finally {
        loading.value = false;
      }
    };

    // Chargement des statistiques
    const fetchStatistics = async () => {
      loading.value = true;
      error.value = null;
      
      try {
        // S'assurer que toutes les données sont chargées
        if (users.value.length === 0) await fetchUsers();
        if (questionnaires.value.length === 0) await fetchQuestionnaires();
        if (diagnostics.value.length === 0) await fetchDiagnostics();
        if (stressLevels.value.length === 0) await fetchStressLevels();
      } catch (err: any) {
        console.error('Erreur lors du chargement des statistiques:', err);
        error.value = err.message || 'Une erreur est survenue lors du chargement des statistiques';
      } finally {
        loading.value = false;
      }
    };

    // Récupérer les valeurs calculées
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
    const formatDate = (dateString?: string) => {
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
    
    // Actions pour les modales
    // Modal utilisateur
    const openAddUserModal = () => {
      isEditing.value = false;
      userForm.value = {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role_id: 2,
        active: true
      };
      showUserModal.value = true;
    };
    
    const editUser = (user: User) => {
      isEditing.value = true;
      userForm.value = {
        name: user.name,
        email: user.email,
        password: '',
        password_confirmation: '',
        role_id: user.role_id,
        active: user.active
      };
      currentItemToDelete.value = user;
      showUserModal.value = true;
    };
    
    const closeUserModal = () => {
      showUserModal.value = false;
    };
    
    // Modal questionnaire
    const openAddQuestionnaireModal = () => {
      isEditing.value = false;
      questionnaireForm.value = {
        title: '',
        description: '',
        active: true
      };
      showQuestionnaireModal.value = true;
    };
    
    const editQuestionnaire = (questionnaire: Questionnaire) => {
      isEditing.value = true;
      questionnaireForm.value = {
        title: questionnaire.title,
        description: questionnaire.description || '',
        active: questionnaire.active
      };
      currentItemToDelete.value = questionnaire;
      showQuestionnaireModal.value = true;
    };
    
    const closeQuestionnaireModal = () => {
      showQuestionnaireModal.value = false;
    };
    
    // Modal niveau de stress
    const openAddStressLevelModal = () => {
      isEditing.value = false;
      stressLevelForm.value = {
        name: '',
        min_score: 0,
        max_score: 0,
        risk_percentage: 0,
        description: '',
        consequences: '',
        active: true
      };
      showStressLevelModal.value = true;
    };
    
    const editStressLevel = (level: StressLevel) => {
      isEditing.value = true;
      stressLevelForm.value = {
        name: level.name,
        min_score: level.min_score,
        max_score: level.max_score,
        risk_percentage: level.risk_percentage,
        description: level.description || '',
        consequences: level.consequences || '',
        active: level.active
      };
      currentItemToDelete.value = level;
      showStressLevelModal.value = true;
    };
    
    const closeStressLevelModal = () => {
      showStressLevelModal.value = false;
    };
    
    // Actions pour la redirection
    const viewQuestions = (questionnaire: Questionnaire) => {
      router.push(`/admin/questionnaires/${questionnaire.id}/questions`);
    };
    
    const viewRecommendations = (level: StressLevel) => {
      router.push(`/admin/stress-levels/${level.id}/recommendations`);
    };
    
    const viewDiagnostic = (diagnostic: Diagnostic) => {
      router.push(`/diagnostics/${diagnostic.id}`);
    };
    
    // Actions pour la confirmation de suppression
    const confirmDeleteUser = (user: User) => {
      deleteItemType.value = 'user';
      currentItemToDelete.value = user;
      deleteConfirmationMessage.value = `Êtes-vous sûr de vouloir supprimer l'utilisateur ${user.name} ?`;
      showDeleteConfirmation.value = true;
    };
    
    const confirmDeleteQuestionnaire = (questionnaire: Questionnaire) => {
      deleteItemType.value = 'questionnaire';
      currentItemToDelete.value = questionnaire;
      deleteConfirmationMessage.value = `Êtes-vous sûr de vouloir supprimer le questionnaire "${questionnaire.title}" ?`;
      showDeleteConfirmation.value = true;
    };
    
    const confirmDeleteStressLevel = (level: StressLevel) => {
      deleteItemType.value = 'stressLevel';
      currentItemToDelete.value = level;
      deleteConfirmationMessage.value = `Êtes-vous sûr de vouloir supprimer le niveau de stress "${level.name}" ?`;
      showDeleteConfirmation.value = true;
    };
    
    const confirmDeleteDiagnostic = (diagnostic: Diagnostic) => {
      deleteItemType.value = 'diagnostic';
      currentItemToDelete.value = diagnostic;
      deleteConfirmationMessage.value = `Êtes-vous sûr de vouloir supprimer ce diagnostic du ${formatDate(diagnostic.diagnostic_date)} ?`;
      showDeleteConfirmation.value = true;
    };
    
    const cancelDelete = () => {
      deleteItemType.value = '';
      currentItemToDelete.value = null;
      deleteConfirmationMessage.value = '';
      showDeleteConfirmation.value = false;
    };
    
    // Effectuer la suppression
    const confirmDelete = async () => {
      if (!currentItemToDelete.value) {
        cancelDelete();
        return;
      }
      
      loading.value = true;
      
      try {
        switch (deleteItemType.value) {
          case 'user':
            await api.delete(`/users/${currentItemToDelete.value.id}`);
            await fetchUsers();
            break;
            
          case 'questionnaire':
            await api.delete(`/questionnaires/${currentItemToDelete.value.id}`);
            await fetchQuestionnaires();
            break;
            
          case 'stressLevel':
            await api.delete(`/admin/stress-levels/${currentItemToDelete.value.id}`);
            await fetchStressLevels();
            break;
            
          case 'diagnostic':
            await api.delete(`/diagnostics/${currentItemToDelete.value.id}`);
            await fetchDiagnostics();
            break;
            
          default:
            console.error('Type d\'élément inconnu:', deleteItemType.value);
            break;
        }
        
        cancelDelete();
      } catch (err: any) {
        console.error('Erreur lors de la suppression:', err);
        error.value = err.message || 'Une erreur est survenue lors de la suppression';
      } finally {
        loading.value = false;
      }
    };
    
    // Sauvegarder les formulaires
    const saveUser = async () => {
      loading.value = true;
      error.value = null;
      
      try {
        if (isEditing.value && currentItemToDelete.value) {
          // Mise à jour
          await api.put(`/users/${currentItemToDelete.value.id}`, userForm.value);
        } else {
          // Création
          await api.post('/users', userForm.value);
        }
        
        await fetchUsers();
        closeUserModal();
      } catch (err: any) {
        console.error('Erreur lors de l\'enregistrement de l\'utilisateur:', err);
        error.value = err.message || 'Une erreur est survenue lors de l\'enregistrement de l\'utilisateur';
      } finally {
        loading.value = false;
      }
    };
    
    const saveQuestionnaire = async () => {
      loading.value = true;
      error.value = null;
      
      try {
        if (isEditing.value && currentItemToDelete.value) {
          // Mise à jour
          await api.put(`/questionnaires/${currentItemToDelete.value.id}`, questionnaireForm.value);
        } else {
          // Création
          await api.post('/questionnaires', questionnaireForm.value);
        }
        
        await fetchQuestionnaires();
        closeQuestionnaireModal();
      } catch (err: any) {
        console.error('Erreur lors de l\'enregistrement du questionnaire:', err);
        error.value = err.message || 'Une erreur est survenue lors de l\'enregistrement du questionnaire';
      } finally {
        loading.value = false;
      }
    };
    
    const saveStressLevel = async () => {
      loading.value = true;
      error.value = null;
      
      try {
        if (isEditing.value && currentItemToDelete.value) {
          // Mise à jour
          await api.put(`/admin/stress-levels/${currentItemToDelete.value.id}`, stressLevelForm.value);
        } else {
          // Création
          await api.post('/admin/stress-levels', stressLevelForm.value);
        }
        
        await fetchStressLevels();
        closeStressLevelModal();
      } catch (err: any) {
        console.error('Erreur lors de l\'enregistrement du niveau de stress:', err);
        error.value = err.message || 'Une erreur est survenue lors de l\'enregistrement du niveau de stress';
      } finally {
        loading.value = false;
      }
    };
    
    // Initialisation
    onMounted(() => {
      // Charger les données correspondant à l'onglet actif
      fetchUsers();
    });
    
    // Observer les changements d'onglet
    watch(activeTab, (newTab) => {
      // Nettoyer les erreurs
      error.value = null;
      
      // Fermer toutes les modales
      showUserModal.value = false;
      showQuestionnaireModal.value = false;
      showStressLevelModal.value = false;
      showDeleteConfirmation.value = false;
    });
    
    // Retourner les variables et fonctions à exposer au template
    return {
      activeTab,
      loading,
      error,
      users,
      questionnaires,
      diagnostics,
      stressLevels,
      roles,
      savedDiagnosticsCount,
      stressLevelDistribution,
      questionnaireScores,
      maxAvgScore,
      
      // Modals
      showUserModal,
      showQuestionnaireModal,
      showStressLevelModal,
      showDeleteConfirmation,
      isEditing,
      deleteConfirmationMessage,
      
      // Formulaires
      userForm,
      questionnaireForm,
      stressLevelForm,
      
      // Actions de chargement
      fetchUsers,
      fetchQuestionnaires,
      fetchDiagnostics,
      fetchStressLevels,
      fetchStatistics,
      
      // Helpers
      formatDate,
      truncateText,
      getUserName,
      getQuestionnaireName,
      getStressLevelClass,
      
      // Actions modales
      openAddUserModal,
      editUser,
      closeUserModal,
      openAddQuestionnaireModal,
      editQuestionnaire,
      closeQuestionnaireModal,
      openAddStressLevelModal,
      editStressLevel,
      closeStressLevelModal,
      
      // Actions redirections
      viewQuestions,
      viewRecommendations,
      viewDiagnostic,
      
      // Actions suppressions
      confirmDeleteUser,
      confirmDeleteQuestionnaire,
      confirmDeleteStressLevel,
      confirmDeleteDiagnostic,
      cancelDelete,
      confirmDelete,
      
      // Actions de sauvegarde
      saveUser,
      saveQuestionnaire,
      saveStressLevel
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

.empty-chart {
  padding: 30px;
  text-align: center;
  color: #999;
  background-color: #f5f5f5;
  border-radius: 4px;
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
input[type="email"],
input[type="password"],
textarea,
select {
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

.btn-primary,
.btn-secondary {
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
  
  .actions {
    flex-direction: column;
    gap: 5px;
  }
  
  .btn-view,
  .btn-edit,
  .btn-delete {
    width: 100%;
    justify-content: center;
  }
}
</style>