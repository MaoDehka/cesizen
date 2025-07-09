<template>
  <div class="ticket-dashboard">
    <h1>Dashboard des tickets Gestsup</h1>
    
    <div class="metrics-grid">
      <div class="metric-card">
        <h3>Temps de résolution moyen</h3>
        <div class="metric-value">{{ metrics.avgResolutionTime }} jours</div>
      </div>
      
      <div class="metric-card">
        <h3>Vélocité équipe</h3>
        <div class="metric-value">{{ metrics.velocity }} points/sprint</div>
      </div>
      
      <div class="metric-card">
        <h3>Satisfaction client</h3>
        <div class="metric-value">{{ metrics.satisfaction }}%</div>
      </div>
      
      <div class="metric-card">
        <h3>Tickets en attente</h3>
        <div class="metric-value">{{ metrics.pendingTickets }}</div>
      </div>
    </div>
    
    <div class="charts-section">
      <div class="chart-container">
        <h3>Répartition par type</h3>
        <canvas ref="typeChart"></canvas>
      </div>
      
      <div class="chart-container">
        <h3>Évolution mensuelle</h3>
        <canvas ref="evolutionChart"></canvas>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const metrics = ref({
  avgResolutionTime: 0,
  velocity: 0,
  satisfaction: 0,
  pendingTickets: 0
})

// You can fetch and update metrics here as needed
</script>