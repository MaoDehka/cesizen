# CESIZen Frontend - Application Vue.js

Interface utilisateur moderne et responsive pour l'application CESIZen, construite avec Vue 3, TypeScript et une architecture modulaire basée sur les stores Pinia.

## 🎨 Stack Technique

- **Framework** : Vue 3.5+ avec Composition API
- **Langage** : TypeScript 5.7+
- **Build Tool** : Vite 6.3+
- **State Management** : Pinia 3.0+
- **Routing** : Vue Router 4.5+
- **HTTP Client** : Fetch API native (service personnalisé)
- **Mobile** : Capacitor 7.2+ (iOS/Android)
- **Styling** : CSS3 natif avec variables CSS

## 📁 Structure du Projet

```
frontend/
├── src/
│   ├── components/           # Composants réutilisables
│   ├── views/               # Pages/vues de l'application
│   │   ├── auth/            # Authentification
│   │   ├── questionnaire/   # Questionnaires & diagnostics
│   │   ├── history/         # Historique
│   │   └── admin/           # Administration
│   ├── stores/              # State management (Pinia)
│   │   ├── auth.ts          # Authentification
│   │   ├── questionnaire.ts # Questionnaires
│   │   ├── diagnostic.ts    # Diagnostics
│   │   └── content.ts       # Gestion contenu
│   ├── services/            # Services & API
│   │   ├── api.ts           # Client API
│   │   ├── mobileService.ts # Fonctionnalités mobile
│   │   └── storageService.ts # Stockage cross-platform
│   ├── router/              # Configuration routing
│   ├── types/               # Définitions TypeScript
│   ├── config/              # Configuration
│   └── assets/              # Assets statiques
├── docker/                  # Configuration Docker
├── public/                  # Assets publics
└── capacitor.config.ts      # Configuration mobile
```

## 🚀 Installation & Développement

### Prérequis

```bash
node --version  # 20+
npm --version   # 10+
```

### Installation Locale

```bash
# Cloner et installer
git clone <repo-url>
cd cesizen/frontend
npm install

# Développement
npm run dev
# Application accessible sur http://localhost:5173
```

### Variables d'Environnement

```bash
# .env.local
VITE_API_URL=http://localhost:8000/api
VITE_APP_ENV=development
```

### Avec Docker

```bash
# Construction
docker-compose up -d frontend

# Development avec hot-reload
docker-compose -f docker-compose.dev.yml up frontend
```

## 🏗️ Architecture & Patterns

### Composition API + TypeScript

```typescript
// Exemple de composant avec composition API
<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import type { User } from '@/types'

// Props typées
interface Props {
  userId: number
}
const props = defineProps<Props>()

// Stores
const authStore = useAuthStore()

// État local
const loading = ref(false)
const user = ref<User | null>(null)

// Computed
const isCurrentUser = computed(() => 
  user.value?.id === authStore.user?.id
)

// Lifecycle
onMounted(async () => {
  await loadUser()
})
</script>
```

### State Management avec Pinia

```typescript
// stores/auth.ts
export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const loading = ref(false)
  
  const isAuthenticated = computed(() => !!user.value)
  const isAdmin = computed(() => user.value?.role?.name === 'admin')
  
  const login = async (credentials: LoginForm) => {
    loading.value = true
    try {
      const response = await api.post<AuthResponse>('/login', credentials)
      user.value = response.user
      // Gestion du token JWT...
    } finally {
      loading.value = false
    }
  }
  
  return { user, loading, isAuthenticated, isAdmin, login }
})
```

## 🔐 Authentification JWT

### Configuration JWT

```typescript
// config/jwt.ts
export default {
  storageTokenKey: 'token',
  storageExpirationKey: 'token_expires_at',
  refreshBeforeExpiry: 5, // minutes
  authHeader: 'Authorization',
  tokenPrefix: 'Bearer',
  refreshEndpoint: '/refresh-token',
  tokenCheckInterval: 60000 // 1 minute
}
```

### Service API avec Auto-refresh

```typescript
// services/api.ts
class ApiService {
  private async request<T>(endpoint: string, options: ApiOptions = {}): Promise<T> {
    // Ajout automatique du token JWT
    const token = localStorage.getItem(jwtConfig.storageTokenKey)
    if (token) {
      headers[jwtConfig.authHeader] = `${jwtConfig.tokenPrefix} ${token}`
    }
    
    const response = await fetch(url, fetchOptions)
    
    // Gestion auto-refresh token expiré
    if (response.status === 401 && endpoint !== jwtConfig.refreshEndpoint) {
      const refreshResult = await this.refreshToken()
      if (refreshResult) {
        return this.request<T>(endpoint, options) // Retry
      }
    }
    
    return response.json()
  }
}
```

## 🛡️ Guards & Navigation

### Protection des Routes

```typescript
// router/index.ts
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const requiresAdmin = to.matched.some(record => record.meta.requiresAdmin)
  const isForGuests = to.matched.some(record => record.meta.guest)

  // Vérification expiration token
  const tokenExpiresAtStr = localStorage.getItem('token_expires_at')
  let tokenExpired = false
  
  if (tokenExpiresAtStr) {
    const tokenExpiresAt = parseInt(tokenExpiresAtStr)
    tokenExpired = Date.now() > tokenExpiresAt
  }
  
  if (tokenExpired && authStore.isAuthenticated) {
    authStore.logout()
    return next({ name: 'login', query: { expired: 'true' } })
  }

  if (requiresAuth && !authStore.isAuthenticated) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }
  
  if (requiresAdmin && !authStore.isAdmin) {
    return next({ name: 'home' })
  }
  
  next()
})
```

## 📱 Support Mobile (Capacitor)

### Configuration

```typescript
// capacitor.config.ts
const config: CapacitorConfig = {
  appId: 'com.cesizen.app',
  appName: 'CESIZen',
  webDir: 'dist',
  plugins: {
    SplashScreen: {
      launchShowDuration: 3000,
      backgroundColor: "#4CAF50"
    },
    LocalNotifications: {
      smallIcon: "ic_stat_icon_config_sample",
      iconColor: "#4CAF50"
    },
    StatusBar: {
      style: "LIGHT",
      backgroundColor: "#4CAF50"
    }
  }
}
```

### Services Mobile

```typescript
// services/mobileService.ts
export class MobileService {
  static async initializeApp() {
    if (!Capacitor.isNativePlatform()) return

    await SplashScreen.hide()
    await StatusBar.setBackgroundColor({ color: '#4CAF50' })
    
    // Gestion bouton retour Android
    App.addListener('backButton', ({ canGoBack }) => {
      if (!canGoBack) {
        App.exitApp()
      } else {
        window.history.back()
      }
    })
  }
  
  static async showToast(message: string, duration: 'short' | 'long' = 'short') {
    await Toast.show({
      text: message,
      duration: duration,
      position: 'bottom'
    })
  }
}
```

## 🎨 Styling & Theming

### Variables CSS

```css
/* assets/main.css */
:root {
  /* Colors */
  --primary-color: #4CAF50;
  --primary-dark: #3e8e41;
  --primary-light: #c8e6c9;
  --text-color: #333333;
  --background-color: #f5f5f5;
  
  /* Typography */
  --font-family: 'Arial', sans-serif;
  --font-size-normal: 1rem;
  --font-size-large: 1.5rem;
  
  /* Spacing */
  --spacing-sm: 0.5rem;
  --spacing-md: 1rem;
  --spacing-lg: 1.5rem;
  
  /* Effects */
  --border-radius: 4px;
  --box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
```

### Classes Utilitaires

```css
/* Boutons */
.btn {
  display: inline-block;
  padding: var(--spacing-sm) var(--spacing-lg);
  background-color: var(--primary-color);
  color: white;
  border: none;
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.btn:hover {
  background-color: var(--primary-dark);
}

/* Cards */
.card {
  background-color: white;
  border-radius: var(--border-radius);
  box-shadow: var(--box-shadow);
  padding: var(--spacing-lg);
}

/* Layout */
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 var(--spacing-md);
}

/* Responsive */
@media (max-width: 768px) {
  .container {
    padding: 0 var(--spacing-sm);
  }
}
```

## 📄 Pages & Composants

### Structure des Vues

```
views/
├── auth/
│   ├── LoginView.vue        # Connexion utilisateur
│   ├── RegisterView.vue     # Inscription
│   ├── ForgotPasswordView.vue
│   └── ResetPasswordView.vue
├── questionnaire/
│   ├── QuestionnaireListView.vue    # Liste questionnaires
│   ├── QuestionnaireQuestionsView.vue # Questions
│   └── DiagnosticResultView.vue     # Résultats
├── history/
│   └── HistoryView.vue      # Historique diagnostics
├── admin/
│   ├── AdminDashboard.vue   # Tableau de bord admin
│   ├── ContentManagementView.vue
│   └── QuestionnaireQuestionsView.vue
├── HomeView.vue             # Page d'accueil
└── NotFoundView.vue         # 404
```

### Exemple de Vue avec Store

```vue
<!-- views/questionnaire/QuestionnaireListView.vue -->
<template>
  <div class="questionnaire-list">
    <h1>Questionnaires Disponibles</h1>
    
    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      Chargement des questionnaires...
    </div>
    
    <div v-else-if="error" class="alert alert-error">
      {{ error }}
    </div>
    
    <div v-else class="questionnaires-grid">
      <div 
        v-for="questionnaire in questionnaires" 
        :key="questionnaire.id"
        class="questionnaire-card card"
      >
        <h3>{{ questionnaire.title }}</h3>
        <p>{{ questionnaire.description }}</p>
        <div class="card-footer">
          <span class="badge">{{ questionnaire.nb_question }} questions</span>
          <router-link 
            :to="`/questionnaires/${questionnaire.id}`"
            class="btn btn-primary"
          >
            Commencer
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useQuestionnaireStore } from '@/stores/questionnaire'

const questionnaireStore = useQuestionnaireStore()

// Computed depuis le store
const { questionnaires, loading, error } = storeToRefs(questionnaireStore)

onMounted(async () => {
  await questionnaireStore.fetchQuestionnaires()
})
</script>
```

## 🔄 Gestion d'État Avancée

### Store Diagnostic avec Cache

```typescript
// stores/diagnostic.ts
export const useDiagnosticStore = defineStore('diagnostic', () => {
  const diagnostics = ref<Diagnostic[]>([])
  const currentDiagnostic = ref<Diagnostic | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const createDiagnostic = async (questionnaireId: number, questions: number[]) => {
    loading.value = true
    try {
      const response = await api.post<DiagnosticResponse>('/diagnostics', { 
        questionnaire_id: questionnaireId,
        questions 
      })
      
      // Mise à jour état local
      diagnostics.value.unshift(response.diagnostic)
      currentDiagnostic.value = response.diagnostic
      
      return response
    } catch (err: any) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  const saveDiagnostic = async (id: number) => {
    try {
      await api.post(`/diagnostics/${id}/save`)
      
      // Mise à jour locale
      if (currentDiagnostic.value?.id === id) {
        currentDiagnostic.value.saved = true
      }
      
      const index = diagnostics.value.findIndex(d => d.id === id)
      if (index !== -1) {
        diagnostics.value[index].saved = true
      }
    } catch (err: any) {
      error.value = err.message
      throw err
    }
  }

  return {
    diagnostics: readonly(diagnostics),
    currentDiagnostic: readonly(currentDiagnostic),
    loading: readonly(loading),
    error: readonly(error),
    createDiagnostic,
    saveDiagnostic
  }
})
```

### Store Content avec Cache & Events

```typescript
// stores/content.ts
export const useContentStore = defineStore('content', () => {
  const contentCache = ref<Record<string, Content>>({})
  
  const fetchContentByPage = async (page: string) => {
    // Cache hit
    if (contentCache.value[page]) {
      return contentCache.value[page]
    }
    
    // Cache miss - fetch from API
    const content = await api.get<Content>(`/contents/${page}`)
    contentCache.value[page] = content
    return content
  }

  const updateContent = async (id: number, data: Partial<Content>) => {
    const updatedContent = await api.put<Content>(`/admin/contents/${id}`, data)
    
    // Invalider cache
    if (updatedContent.page) {
      delete contentCache.value[updatedContent.page]
      
      // Émettre événement pour composants
      const event = new CustomEvent('content-updated', { 
        detail: { page: updatedContent.page, id }
      })
      contentUpdatedEvent.dispatchEvent(event)
    }
    
    return updatedContent
  }

  return { fetchContentByPage, updateContent }
})
```

## 🧪 Tests

### Configuration Tests

```bash
# Installation dépendances test
npm install -D @vue/test-utils vitest jsdom

# Exécution
npm run test          # Tests unitaires
npm run test:coverage # Coverage
npm run test:watch    # Mode watch
```

### Exemple Test Composant

```typescript
// tests/components/QuestionnaireCard.test.ts
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import QuestionnaireCard from '@/components/QuestionnaireCard.vue'
import type { Questionnaire } from '@/types'

describe('QuestionnaireCard', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  const mockQuestionnaire: Questionnaire = {
    id: 1,
    title: 'Test Questionnaire',
    description: 'Description test',
    nb_question: 10,
    active: true,
    creation_date: '2024-01-01',
    last_modification: '2024-01-01'
  }

  it('renders questionnaire information correctly', () => {
    const wrapper = mount(QuestionnaireCard, {
      props: { questionnaire: mockQuestionnaire }
    })

    expect(wrapper.text()).toContain('Test Questionnaire')
    expect(wrapper.text()).toContain('10 questions')
  })

  it('emits start event when button clicked', async () => {
    const wrapper = mount(QuestionnaireCard, {
      props: { questionnaire: mockQuestionnaire }
    })

    await wrapper.find('.btn-start').trigger('click')
    expect(wrapper.emitted('start')).toBeTruthy()
  })
})
```

## 📦 Build & Déploiement

### Configuration Vite

```typescript
// vite.config.ts
export default defineConfig({
  plugins: [vue()],
  build: {
    target: 'es2020',
    outDir: 'dist',
    sourcemap: true,
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['vue', 'vue-router', 'pinia'],
          utils: ['axios']
        }
      }
    }
  },
  define: {
    // Injection variables build-time
    __APP_VERSION__: JSON.stringify(process.env.npm_package_version),
    __BUILD_DATE__: JSON.stringify(new Date().toISOString())
  }
})
```

### Build Production

```bash
# Build optimisé
npm run build

# Preview build local
npm run preview

# Analyse bundle
npm run build -- --analyze
```

### Docker Multi-stage

```dockerfile
# Build stage
FROM node:20 AS builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Production stage  
FROM nginx:alpine
COPY --from=builder /app/dist /usr/share/nginx/html
COPY docker/nginx.conf /etc/nginx/nginx.conf
```

### Variables d'Environnement Runtime

```bash
# docker/entrypoint.sh - Injection runtime
#!/bin/sh
API_URL=${VITE_API_URL:-"https://cesizen-prod.chickenkiller.com/api"}
APP_ENV=${VITE_APP_ENV:-"production"}

# Remplacer placeholders dans fichiers JS
find /usr/share/nginx/html -name "*.js" -type f -exec sed -i "s|VITE_API_URL_PLACEHOLDER|$API_URL|g" {} \;
find /usr/share/nginx/html -name "*.js" -type f -exec sed -i "s|VITE_APP_ENV_PLACEHOLDER|$APP_ENV|g" {} \;
```

## 🔧 Configuration Nginx

### Production avec SSL

```nginx
# docker/default.conf
server {
    listen 80;
    server_name _;
    root /usr/share/nginx/html;
    index index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    # SPA routing
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Static assets caching
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # API proxy
    location /api/ {
        proxy_pass http://backend;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

## 📱 Applications Mobiles Natives

### Build Android

```bash
# Ajouter plateforme
npx cap add android

# Build web
npm run build

# Copier vers Capacitor
npx cap copy android

# Ouvrir Android Studio
npx cap open android
```

### Build iOS

```bash
# Ajouter plateforme (macOS uniquement)
npx cap add ios

# Build et copie
npm run build
npx cap copy ios

# Ouvrir Xcode
npx cap open ios
```

### Plugins Capacitor Utilisés

```typescript
// Plugins configurés
import { App } from '@capacitor/app'              // Gestion app
import { StatusBar } from '@capacitor/status-bar' // Barre de statut  
import { SplashScreen } from '@capacitor/splash-screen' // Écran de démarrage
import { Haptics } from '@capacitor/haptics'      // Vibrations
import { Toast } from '@capacitor/toast'          // Notifications toast
import { LocalNotifications } from '@capacitor/local-notifications' // Notifications
import { Preferences } from '@capacitor/preferences' // Stockage
```

## 🎯 Performance & Optimisations

### Lazy Loading Routes

```typescript
// router/index.ts
const routes = [
  {
    path: '/admin',
    component: () => import('../views/admin/AdminDashboard.vue')
  },
  {
    path: '/questionnaires/:id',
    component: () => import('../views/questionnaire/QuestionnaireQuestionsView.vue')
  }
]
```

### Code Splitting Stores

```typescript
// Chargement conditionnel stores admin
const loadAdminStore = () => import('../stores/admin')

// Dans composant admin
const { useAdminStore } = await loadAdminStore()
const adminStore = useAdminStore()
```

### Optimisations Bundle

- **Tree shaking** automatique avec Vite
- **Code splitting** par route
- **Lazy loading** composants
- **Compression Gzip** Nginx
- **Cache busting** avec hash fichiers
- **Preload** ressources critiques

## 🛠️ Développement

### Hot Module Replacement

```bash
# Dev avec HMR
npm run dev
# Auto-reload sur changements:
# - Composants Vue
# - Stores Pinia  
# - Styles CSS
# - Configuration TypeScript
```

### Debugging

```typescript
// Vue DevTools
app.config.globalProperties.$log = console.log

// Pinia DevTools intégré
// Performance DevTools dans navigateur
```

### Linting & Formatage

```bash
# ESLint
npm run lint
npm run lint:fix

# Prettier  
npm run format

# Type checking
npm run type-check
```

## 🔄 Intégrations

### API Backend

```typescript
// Services API organisés par domaine
services/
├── api.ts           # Client HTTP base
├── authService.ts   # Authentification  
├── questionnaireService.ts # Questionnaires
├── diagnosticService.ts    # Diagnostics
└── adminService.ts  # Administration
```

### Stockage Cross-Platform

```typescript
// services/storageService.ts
export class StorageService {
  static async set(key: string, value: any): Promise<void> {
    if (Capacitor.isNativePlatform()) {
      await Preferences.set({ key, value: JSON.stringify(value) })
    } else {
      localStorage.setItem(key, JSON.stringify(value))
    }
  }
  
  static async get<T>(key: string): Promise<T | null> {
    let value: string | null = null
    
    if (Capacitor.isNativePlatform()) {
      const result = await Preferences.get({ key })
      value = result.value
    } else {
      value = localStorage.getItem(key)
    }
    
    return value ? JSON.parse(value) : null
  }
}
```

## 🚀 Déploiement Continu

### GitHub Actions

```yaml
# .github/workflows/frontend.yml
name: Frontend CI/CD
on:
  push:
    branches: [main, develop]
    paths: ['frontend/**']

jobs:
  build-and-deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      
      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'
          cache-dependency-path: frontend/package-lock.json
          
      - name: Install dependencies
        working-directory: frontend
        run: npm ci
        
      - name: Run tests
        working-directory: frontend  
        run: npm run test
        
      - name: Build application
        working-directory: frontend
        run: npm run build
        
      - name: Build Docker image
        run: |
          docker build -t ghcr.io/user/cesizen-frontend:${{ github.sha }} frontend/
          docker push ghcr.io/user/cesizen-frontend:${{ github.sha }}
```

## 📊 Monitoring

### Métriques Client

```typescript
// Performance monitoring
performance.mark('app-start')
// ... app initialization
performance.mark('app-ready')
performance.measure('app-load-time', 'app-start', 'app-ready')

// Error tracking
window.addEventListener('error', (event) => {
  console.error('Global error:', event.error)
  // Envoyer à service monitoring
})

// Performance observer
new PerformanceObserver((list) => {
  list.getEntries().forEach((entry) => {
    if (entry.entryType === 'navigation') {
      console.log('Page load time:', entry.loadEventEnd - entry.fetchStart)
    }
  })
}).observe({ entryTypes: ['navigation'] })
```

---

*Pour plus d'informations, consulter la [documentation Vue.js](https://vuejs.org/) et le [README principal](../README.md).*