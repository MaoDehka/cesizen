import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
      meta: { title: 'Accueil - CESIZen' }
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/auth/LoginView.vue'),
      meta: { title: 'Connexion - CESIZen', guest: true }
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/auth/RegisterView.vue'),
      meta: { title: 'Inscription - CESIZen', guest: true }
    },
    // Liste des questionnaires
    {
      path: '/questionnaires',
      name: 'questionnaire-list',
      component: () => import('../views/questionnaire/QuestionnairelistView.vue'),
      meta: { title: 'Questionnaires - CESIZen', requiresAuth: true }
    },
    // Questions du questionnaire
    {
      path: '/questionnaires/:id',
      name: 'questionnaire-questions',
      component: () => import('../views/questionnaire/QuestionnaireQuestionsView.vue'),
      meta: { title: 'Questionnaire - CESIZen', requiresAuth: true }
    },
    // Ancienne route pour la vue questionnaire (conservée pour la compatibilité)
    {
      path: '/diagnostics',
      name: 'questionnaire',
      component: () => import('../views/questionnaire/QuestionnaireView.vue'),
      meta: { title: 'Questionnaire de stress - CESIZen', requiresAuth: true }
    },
    // Route pour les résultats
    {
      path: '/diagnostics/:id',
      name: 'diagnostic-result',
      component: () => import('../views/questionnaire/DiagnosticResultView.vue'),
      meta: { title: 'Résultat du diagnostic - CESIZen', requiresAuth: true }
    },
    // Route pour l'historique des diagnostics
    {
      path: '/history',
      name: 'history',
      component: () => import('../views/history/HistoryView.vue'),
      meta: { title: 'Historique des diagnostics - CESIZen', requiresAuth: true }
    },
    // Administration
    {
      path: '/admin',
      name: 'admin',
      component: () => import('../views/admin/AdminDashboard.vue'),
      meta: { title: 'Administration - CESIZen', requiresAuth: true, requiresAdmin: true }
    },
    // Nouvelles routes admin
    {
      path: '/admin/questionnaires/:id/questions',
      name: 'admin-questionnaire-questions',
      component: () => import('../views/admin/QuestionnaireQuestionsView.vue'),
      meta: { title: 'Gestion des questions - CESIZen', requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/admin/stress-levels/:id/recommendations',
      name: 'admin-stress-level-recommendations',
      component: () => import('../views/admin/StressLevelRecommendationsView.vue'),
      meta: { title: 'Gestion des recommandations - CESIZen', requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('../views/NotFoundView.vue'),
      meta: { title: 'Page non trouvée - CESIZen' }
    },
    {
      path: '/admin/contents',
      name: 'admin-contents',
      component: () => import('../views/admin/ContentManagementView.vue'),
      meta: { title: 'Gestion des contenus - CESIZen', requiresAuth: true, requiresAdmin: true }
    }
  ]
})

// Navigation guard
router.beforeEach((to, from, next) => {
  // Mise à jour du titre de la page
  document.title = to.meta.title as string || 'CESIZen'
  
  const authStore = useAuthStore()
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const requiresAdmin = to.matched.some(record => record.meta.requiresAdmin)
  const isForGuests = to.matched.some(record => record.meta.guest)

  // Rediriger vers la page de connexion si l'authentification est requise mais l'utilisateur n'est pas connecté
  if (requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login', query: { redirect: to.fullPath } })
  } 
  // Rediriger vers la page d'accueil si la page est réservée aux visiteurs mais l'utilisateur est connecté
  else if (isForGuests && authStore.isAuthenticated) {
    next({ name: 'home' })
  } 
  // Rediriger vers la page d'accueil si la page est réservée aux administrateurs mais l'utilisateur n'est pas admin
  else if (requiresAdmin && !authStore.isAdmin) {
    next({ name: 'home' })
  } 
  else {
    next()
  }
})

export default router