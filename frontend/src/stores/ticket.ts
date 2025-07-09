import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '../services/api';

export interface Ticket {
  id: number;
  gestsup_id: string;
  title: string;
  description: string;
  type: 'bug' | 'evolution' | 'amelioration' | 'support';
  priority: 'critique' | 'elevee' | 'normale' | 'faible';
  status: string;
  assignee_id?: number;
  creator_id: number;
  estimated_hours?: number;
  spent_hours?: number;
  due_date?: string;
  branch_name?: string;
  pull_request_url?: string;
  deployed_at?: string;
  assignee?: {
    id: number;
    name: string;
    email: string;
  };
  creator?: {
    id: number;
    name: string;
    email: string;
  };
}

export interface TicketMetrics {
  avgResolutionTime: number;
  velocity: number;
  satisfaction: number;
  pendingTickets: number;
}

export const useTicketStore = defineStore('ticket', () => {
  const tickets = ref<Ticket[]>([]);
  const currentTicket = ref<Ticket | null>(null);
  const metrics = ref<TicketMetrics>({
    avgResolutionTime: 0,
    velocity: 0,
    satisfaction: 0,
    pendingTickets: 0
  });
  const loading = ref(false);
  const error = ref<string | null>(null);

  // Computed
  const currentUserId = computed(() => {
    // Récupérer depuis le store auth ou localStorage
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    return user.id;
  });

  const userRole = computed(() => {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    return user.role?.name;
  });

  // Actions
  const fetchTickets = async () => {
    loading.value = true;
    error.value = null;
    
    try {
      // Pour l'instant, retourner des données mock
      // tickets.value = await api.get<Ticket[]>('/tickets');
      
      // Données mock pour la démonstration
      tickets.value = [
        {
          id: 1,
          gestsup_id: 'GEST-123',
          title: 'Correction du calcul de score de stress',
          description: 'Le calcul du score total ne prend pas en compte tous les facteurs',
          type: 'bug',
          priority: 'elevee',
          status: 'en_developpement',
          creator_id: 1,
          assignee_id: 2,
          estimated_hours: 4,
          spent_hours: 2,
          assignee: {
            id: 2,
            name: 'Développeur Test',
            email: 'dev@test.com'
          }
        },
        {
          id: 2,
          gestsup_id: 'GEST-124',
          title: 'Ajout export PDF des diagnostics',
          description: 'Permettre aux utilisateurs d\'exporter leurs diagnostics en PDF',
          type: 'evolution',
          priority: 'normale',
          status: 'nouveau',
          creator_id: 1,
          estimated_hours: 8
        }
      ];
      
      return tickets.value;
    } catch (err: any) {
      error.value = err.message || 'Erreur lors du chargement des tickets';
      console.error(error.value);
      return [];
    } finally {
      loading.value = false;
    }
  };

  const fetchMetrics = async () => {
    try {
      // metrics.value = await api.get<TicketMetrics>('/tickets/metrics');
      
      // Données mock pour la démonstration
      metrics.value = {
        avgResolutionTime: 3.2,
        velocity: 24,
        satisfaction: 87,
        pendingTickets: 5
      };
    } catch (err: any) {
      console.error('Erreur lors du chargement des métriques:', err);
    }
  };

  const viewTicket = (ticket: Ticket) => {
    currentTicket.value = ticket;
    // Logique pour afficher les détails (modal, navigation, etc.)
    console.log('Affichage du ticket:', ticket);
  };

  const editTicket = (ticket: Ticket) => {
    currentTicket.value = ticket;
    // Logique pour éditer le ticket
    console.log('Édition du ticket:', ticket);
  };

  const createTicket = async (ticketData: Partial<Ticket>) => {
    loading.value = true;
    error.value = null;
    
    try {
      // const newTicket = await api.post<Ticket>('/tickets', ticketData);
      // tickets.value.unshift(newTicket);
      
      console.log('Création du ticket:', ticketData);
      return true;
    } catch (err: any) {
      error.value = err.message || 'Erreur lors de la création du ticket';
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  return {
    tickets,
    currentTicket,
    metrics,
    loading,
    error,
    currentUserId,
    userRole,
    fetchTickets,
    fetchMetrics,
    viewTicket,
    editTicket,
    createTicket
  };
});