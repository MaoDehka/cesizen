<template>
  <div class="home-container">
    <header class="app-header">
      <img src="../assets/logo.jpg" alt="CESIZen Logo" class="logo" />
      <h1>CESIZen</h1>
      <p>L'application de votre santé mentale</p>
    </header>

    <section class="intro-section">
      <h2>Bienvenue sur CESIZen</h2>
      <p>
        Votre plateforme dédiée à la gestion du stress et à la santé mentale.
        Découvrez des outils et des informations pour mieux comprendre et gérer votre stress au quotidien.
      </p>
    </section>

    <section class="features-section">
      <h2>Nos fonctionnalités</h2>
      
      <div class="feature-card">
        <h3>Diagnostics de stress</h3>
        <p>Évaluez votre niveau de stress grâce à notre questionnaire basé sur l'échelle de Holmes et Rahe.</p>
        <router-link to="/diagnostics" class="feature-link">Faire le test</router-link>
      </div>
      
      <div class="feature-card">
        <h3>Exercices de respiration</h3>
        <p>Pratiquez des exercices de cohérence cardiaque pour réduire votre stress et vous relaxer.</p>
        <router-link to="/breathing" class="feature-link">Commencer un exercice</router-link>
      </div>
      
      <div class="feature-card">
        <h3>Tracker d'émotions</h3>
        <p>Suivez vos émotions au quotidien et visualisez leur évolution dans le temps.</p>
        <router-link to="/emotions" class="feature-link">Accéder au tracker</router-link>
      </div>
    </section>

    <section class="info-section">
      <h2>Le stress et la santé mentale</h2>
      <p>
        Le stress est une réaction physiologique naturelle face aux exigences de la vie quotidienne. 
        Toutefois, lorsqu'il devient chronique ou excessif, il peut entraîner des conséquences néfastes 
        sur la santé mentale et physique.
      </p>
      <p>
        Comprendre les mécanismes du stress et disposer d'outils pour le gérer est essentiel pour 
        maintenir un bon équilibre émotionnel et favoriser le bien-être général.
      </p>
    </section>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'HomeView'
});
</script>

<style scoped>
.home-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

.app-header {
  text-align: center;
  margin-bottom: 30px;
}

.logo {
  width: 80px;
  height: auto;
  color: #4CAF50;
}

.intro-section,
.features-section,
.info-section {
  margin-bottom: 40px;
}

.feature-card {
  background-color: #f9f9f9;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.feature-link {
  display: inline-block;
  margin-top: 10px;
  padding: 8px 15px;
  background-color: #4CAF50;
  color: white;
  text-decoration: none;
  border-radius: 4px;
}

h1, h2, h3 {
  color: #333;
}

p {
  line-height: 1.6;
  color: #666;
}
</style>