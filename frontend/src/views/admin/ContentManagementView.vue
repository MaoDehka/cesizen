<template>
  <div class="content-management">
    <div class="header-section">
      <div class="back-button-container">
        <button @click="goBack" class="btn-back">
          <i class="fas fa-arrow-left"></i> Retour
        </button>
      </div>
      <h1>Gestion des contenus</h1>
    </div>
    
    <div v-if="loading" class="loading-spinner">
      <div class="spinner"></div>
      <p>Chargement des contenus...</p>
    </div>
    
    <div v-else-if="error" class="error-message">
      <p>{{ error }}</p>
      <button @click="fetchContents" class="btn-retry">Réessayer</button>
    </div>
    
    <div v-else class="content-section">
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Page</th>
              <th>Titre</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="contents.length === 0">
              <td colspan="5" class="text-center">Aucun contenu trouvé</td>
            </tr>
            <tr v-for="content in contents" :key="content.id">
              <td>{{ content.id }}</td>
              <td>{{ content.page }}</td>
              <td>{{ content.title }}</td>
              <td>
                <span
                  class="status-badge"
                  :class="{ active: content.active, inactive: !content.active }"
                >
                  {{ content.active ? 'Actif' : 'Inactif' }}
                </span>
              </td>
              <td class="actions">
                <button @click="editContent(content)" class="btn-edit" title="Modifier">
                  <i class="fas fa-edit"></i> Modifier
                </button>
                <button @click="previewContent(content)" class="btn-view" title="Aperçu">
                  <i class="fas fa-eye"></i> Aperçu
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- Modal d'édition -->
    <div v-if="showEditModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Modifier le contenu</h2>
          <button @click="closeEditModal" class="close-btn">&times;</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveContent">
            <div class="form-group">
              <label for="title">Titre</label>
              <input
                type="text"
                id="title"
                v-model="contentForm.title"
                required
              />
            </div>
            
            <div class="form-group">
              <label for="content">Contenu HTML</label>
              <textarea
                id="content"
                v-model="contentForm.content"
                rows="15"
                class="content-editor"
                required
              ></textarea>
            </div>
            
            <div class="form-group">
              <label class="checkbox-label">
                <input type="checkbox" v-model="contentForm.active">
                Actif
              </label>
            </div>
            
            <div class="form-actions">
              <button type="button" @click="closeEditModal" class="btn-secondary">Annuler</button>
              <button type="submit" class="btn-primary">Enregistrer</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    <!-- Modal d'aperçu -->
    <div v-if="showPreviewModal" class="modal">
      <div class="modal-content preview-modal">
        <div class="modal-header">
          <h2>Aperçu: {{ currentContent?.title }}</h2>
          <button @click="closePreviewModal" class="close-btn">&times;</button>
        </div>
        <div class="modal-body">
          <div class="preview-content" v-html="currentContent?.content"></div>
        </div>
        <div class="modal-footer">
          <button @click="closePreviewModal" class="btn-secondary">Fermer</button>
          <button @click="editContent(currentContent)" class="btn-primary">Modifier</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useContentStore, type Content } from '../../stores/content';

export default defineComponent({
  name: 'ContentManagementView',
  setup() {
    const router = useRouter();
    const contentStore = useContentStore();
    
    // États
    const showEditModal = ref(false);
    const showPreviewModal = ref(false);
    const currentContent = ref<Content | null>(null);
    
    // Formulaire
    const contentForm = ref({
      title: '',
      content: '',
      active: true,
    });
    
    // Computed properties
    const loading = computed(() => contentStore.loading);
    const error = computed(() => contentStore.error);
    const contents = computed(() => contentStore.contents);
    
    // Actions
    const fetchContents = async () => {
      try {
        await contentStore.fetchContents();
      } catch (err) {
        console.error('Erreur lors du chargement des contenus:', err);
      }
    };
    
    const goBack = () => {
      router.push('/admin');
    };
    
    const editContent = (content: Content | null) => {
      if (!content) return;
      
      currentContent.value = content;
      contentForm.value = {
        title: content.title,
        content: content.content,
        active: content.active,
      };
      
      // Fermer la modal d'aperçu si elle est ouverte
      showPreviewModal.value = false;
      showEditModal.value = true;
    };
    
    const closeEditModal = () => {
      showEditModal.value = false;
      currentContent.value = null;
    };
    
    const saveContent = async () => {
      if (!currentContent.value) return;
      
      try {
        await contentStore.updateContent(currentContent.value.id, contentForm.value);
        closeEditModal();
      } catch (err) {
        console.error('Erreur lors de la mise à jour du contenu:', err);
      }
    };
    
    const previewContent = (content: Content) => {
      currentContent.value = content;
      showPreviewModal.value = true;
    };
    
    const closePreviewModal = () => {
      showPreviewModal.value = false;
      currentContent.value = null;
    };
    
    onMounted(() => {
      fetchContents();
    });
    
    return {
      contents,
      loading,
      error,
      showEditModal,
      showPreviewModal,
      currentContent,
      contentForm,
      fetchContents,
      goBack,
      editContent,
      closeEditModal,
      saveContent,
      previewContent,
      closePreviewModal,
    };
  },
});
</script>

<style scoped>
.content-management {
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

.actions {
  display: flex;
  gap: 8px;
}

.btn-edit, .btn-view {
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

.btn-view {
  background-color: #2196F3;
  color: white;
}

.btn-view:hover {
  background-color: #1976D2;
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
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.preview-modal {
  max-width: 1000px;
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

.modal-footer {
  padding: 15px;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  border-top: 1px solid #eee;
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
textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
}

.content-editor {
  font-family: monospace;
  min-height: 300px;
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

.preview-content {
  background-color: #f9f9f9;
  padding: 20px;
  border-radius: 4px;
  min-height: 300px;
}

@media (max-width: 768px) {
  .actions {
    flex-direction: column;
    gap: 5px;
  }
  
  .btn-edit, .btn-view {
    width: 100%;
    justify-content: center;
  }
}
</style>