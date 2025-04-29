<template>
    <div class="not-found">
      <h1>404</h1>
      <h2>Page non trouvée</h2>
      <p>Désolé, la page que vous recherchez n'existe pas.</p>
      <router-link to="/" class="home-link">Retour à l'accueil</router-link>
    </div>
  </template>
  
  <script lang="ts">
  import { defineComponent } from 'vue';
  
  export default defineComponent({
    name: 'NotFoundView'
  });
  </script>
  
  <style scoped>
  .not-found {
    text-align: center;
    padding: 50px 20px;
    max-width: 600px;
    margin: 0 auto;
  }
  
  h1 {
    font-size: 6rem;
    margin-bottom: 0;
    color: #4CAF50;
  }
  
  h2 {
    font-size: 2rem;
    margin-top: 0;
  }
  
  p {
    margin-bottom: 30px;
    color: #666;
  }
  
  .home-link {
    display: inline-block;
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    text-decoration: none;
    border-radius: 4px;
  }
  </style>