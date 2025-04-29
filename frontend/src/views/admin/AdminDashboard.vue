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
                  <td colspan="6" class="text-center">Chargement...</td>
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
                    <button class="btn-edit">Modifier</button>
                    <button class="btn-delete">Supprimer</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
  
        <!-- Onglet Questionnaires -->
        <div v-if="activeTab === 'questionnaires'" class="questionnaires-tab">
          <h2>Gestion des questionnaires</h2>
          <button class="btn-add">Ajouter un questionnaire</button>
          
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Titre</th>
                  <th>Questions</th>
                  <th>Statut</th>
                  <th>Dernière modification</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="6" class="text-center">Chargement...</td>
                </tr>
                <tr v-else-if="questionnaires.length === 0">
                  <td colspan="6" class="text-center">Aucun questionnaire trouvé</td>
                </tr>
                <tr v-for="questionnaire in questionnaires" :key="questionnaire.id">
                  <td>{{ questionnaire.id }}</td>
                  <td>{{ questionnaire.title }}</td>
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
                    <button class="btn-edit">Modifier</button>
                    <button class="btn-delete">Supprimer</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
  
        <!-- Onglet Statistiques -->
        <div v-if="activeTab === 'statistics'" class="statistics-tab">
          <h2>Statistiques</h2>
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
          </div>
          
          <div class="charts-container">
            <div class="chart">
              <h3>Niveaux de stress</h3>
              <!-- Graphique des niveaux de stress -->
            </div>
            
            <div class="chart">
              <h3>Activité utilisateurs</h3>
              <!-- Graphique d'activité utilisateurs -->
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script lang="ts">
  import { defineComponent, ref, onMounted } from 'vue';
  import type { User, Questionnaire, Diagnostic } from '../../types';
  
  export default defineComponent({
    name: 'AdminDashboard',
    setup() {
      const activeTab = ref('users');
      const loading = ref(false);
      const users = ref<User[]>([]);
      const questionnaires = ref<Questionnaire[]>([]);
      const diagnostics = ref<Diagnostic[]>([]);
      
      const fetchUsers = async () => {
        loading.value = true;
        // Simulating API call
        setTimeout(() => {
          users.value = [
            { id: 1, name: 'Admin User', email: 'admin@cesizen.com', role_id: 1, role: { id: 1, name: 'admin', description: 'Administrateur' }, active: true },
            { id: 2, name: 'Test User', email: 'user@cesizen.com', role_id: 2, role: { id: 2, name: 'user', description: 'Utilisateur' }, active: true }
          ];
          loading.value = false;
        }, 500);
      };
      
      const fetchQuestionnaires = async () => {
        loading.value = true;
        // Simulating API call
        setTimeout(() => {
          questionnaires.value = [
            { 
              id: 1, 
              title: 'Échelle de stress de Holmes et Rahe',
              description: 'Évaluez votre niveau de stress en fonction des événements vécus', 
              nb_question: 10, 
              creation_date: '2023-01-01T00:00:00', 
              last_modification: '2023-01-15T00:00:00', 
              active: true 
            }
          ];
          loading.value = false;
        }, 500);
      };
      
      const fetchDiagnostics = async () => {
        loading.value = true;
        // Simulating API call
        setTimeout(() => {
          diagnostics.value = [
            { 
              id: 1, 
              user_id: 2, 
              score_total: 250, 
              stress_level: 'Modéré', 
              diagnostic_date: '2023-02-01T00:00:00',
              consequences: 'Risque modéré de problèmes de santé liés au stress',
              advices: 'Pratiquer des exercices de relaxation régulièrement'
            }
          ];
          loading.value = false;
        }, 500);
      };
      
      const formatDate = (dateString: string) => {
        const date = new Date(dateString);
        return date.toLocaleDateString();
      };
      
      onMounted(() => {
        fetchUsers();
        fetchQuestionnaires();
        fetchDiagnostics();
      });
      
      return {
        activeTab,
        loading,
        users,
        questionnaires,
        diagnostics,
        formatDate
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
    margin-bottom: 20px;
  }
  
  .tab-button {
    padding: 10px 20px;
    background-color: #f5f5f5;
    border: none;
    cursor: pointer;
    font-size: 16px;
  }
  
  .tab-button.active {
    background-color: #4CAF50;
    color: white;
  }
  
  .tab-content {
    background-color: white;
    padding: 20px;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }
  
  h2 {
    margin-bottom: 20px;
    color: #333;
  }
  
  .btn-add {
    padding: 8px 16px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    margin-bottom: 20px;
    cursor: pointer;
  }
  
  .table-responsive {
    overflow-x: auto;
  }
  
  .data-table {
    width: 100%;
    border-collapse: collapse;
  }
  
  .data-table th,
  .data-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
  }
  
  .data-table th {
    background-color: #f5f5f5;
  }
  
  .status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
  }
  
  .status-badge.active {
    background-color: #c8e6c9;
    color: #2e7d32;
  }
  
  .status-badge.inactive {
    background-color: #ffcdd2;
    color: #c62828;
  }
  
  .actions {
    display: flex;
    gap: 10px;
  }
  
  .btn-edit,
  .btn-delete {
    padding: 4px 8px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
  }
  
  .btn-edit {
    background-color: #2196F3;
    color: white;
  }
  
  .btn-delete {
    background-color: #F44336;
    color: white;
  }
  
  .stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
  }
  
  .stat-card {
    background-color: #f5f5f5;
    padding: 20px;
    border-radius: 4px;
    text-align: center;
  }
  
  .stat-value {
    font-size: 2rem;
    font-weight: bold;
    color: #4CAF50;
  }
  
  .charts-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 20px;
  }
  
  .chart {
    background-color: #f5f5f5;
    padding: 20px;
    border-radius: 4px;
    min-height: 300px;
  }
  
  @media (max-width: 768px) {
    .dashboard-tabs {
      flex-direction: column;
    }
    
    .charts-container {
      grid-template-columns: 1fr;
    }
  }
  </style>