<template>
  <div class="ticket-management">
    <div class="ticket-filters">
      <select v-model="selectedStatus">
        <option value="">Tous les statuts</option>
        <option value="nouveau">Nouveau</option>
        <option value="en_developpement">En développement</option>
        <option value="en_test">En test</option>
        <option value="resolu">Résolu</option>
      </select>
      
      <select v-model="selectedType">
        <option value="">Tous les types</option>
        <option value="bug">Bug</option>
        <option value="evolution">Évolution</option>
        <option value="amelioration">Amélioration</option>
      </select>
    </div>
    
    <div class="tickets-grid">
      <div 
        v-for="ticket in filteredTickets" 
        :key="ticket.id"
        class="ticket-card"
        :class="[`priority-${ticket.priority}`, `type-${ticket.type}`]"
      >
        <div class="ticket-header">
          <span class="ticket-id">GEST-{{ ticket.gestsup_id }}</span>
          <span class="ticket-priority">{{ ticket.priority }}</span>
        </div>
        
        <h3>{{ ticket.title }}</h3>
        <p>{{ ticket.description }}</p>
        
        <div class="ticket-meta">
          <span class="status">{{ ticket.status }}</span>
          <span class="assignee">{{ ticket.assignee?.name }}</span>
          <span class="estimation">{{ ticket.estimated_hours }}h</span>
        </div>
        
        <div class="ticket-actions">
          <button @click="viewTicket(ticket)">Voir détails</button>
          <button 
            v-if="canEditTicket(ticket)" 
            @click="editTicket(ticket)"
          >
            Modifier
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, computed, onMounted } from 'vue';
import { useTicketStore } from './../stores/ticket';

export default defineComponent({
  name: 'TicketManagement',
  setup() {
    const ticketStore = useTicketStore();
    const selectedStatus = ref('');
    const selectedType = ref('');
    
    const filteredTickets = computed(() => {
      return ticketStore.tickets.filter(ticket => {
        return (!selectedStatus.value || ticket.status === selectedStatus.value) &&
               (!selectedType.value || ticket.type === selectedType.value);
      });
    });
    
    const canEditTicket = (ticket: any) => {
      // Logique pour déterminer si l'utilisateur peut modifier le ticket
      return ticket.assignee_id === ticketStore.currentUserId || 
             ticketStore.userRole === 'admin';
    };
    
    onMounted(() => {
      ticketStore.fetchTickets();
    });
    
    return {
      selectedStatus,
      selectedType,
      filteredTickets,
      canEditTicket,
      viewTicket: ticketStore.viewTicket,
      editTicket: ticketStore.editTicket
    };
  }
});
</script>

<style scoped>
.ticket-card {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 16px;
  margin: 8px;
  background: white;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.priority-critique {
  border-left: 4px solid #e53935;
}

.priority-elevee {
  border-left: 4px solid #ff9800;
}

.priority-normale {
  border-left: 4px solid #4caf50;
}

.priority-faible {
  border-left: 4px solid #9e9e9e;
}
</style>