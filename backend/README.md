# CESIZen Backend - API Laravel

API REST robuste pour l'application CESIZen, construite avec Laravel 12 et intégrant l'authentification JWT, la gestion des rôles et une architecture modulaire.

## 🔧 Stack Technique

- **Framework** : Laravel 12.x
- **PHP** : 8.2+
- **Base de données** : MySQL 8.0 / SQLite (dev)
- **Authentification** : JWT (tymon/jwt-auth 2.2)
- **Cache** : Redis 7
- **File d'attente** : Redis
- **Serveur Web** : Nginx + PHP-FPM
- **Conteneurisation** : Docker multi-stage

## 📁 Structure du Projet

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── API/          # Contrôleurs API
│   │   ├── Middleware/       # Middlewares personnalisés
│   │   └── Kernel.php
│   ├── Models/               # Modèles Eloquent
│   ├── Providers/            # Service providers
│   └── Services/             # Services métier
├── config/                   # Configuration Laravel
├── database/
│   ├── factories/            # Factories pour tests
│   ├── migrations/           # Migrations base de données
│   └── seeders/              # Données de base
├── docker/                   # Configuration Docker
│   ├── nginx/                # Config Nginx
│   ├── php/                  # Config PHP-FPM
│   └── supervisor/           # Config Supervisor
├── routes/
│   ├── api.php               # Routes API
│   └── web.php               # Routes web
└── storage/                  # Stockage & logs
```

## 🚀 Installation & Configuration

### Développement Local

1. **Prérequis**
   ```bash
   # Avec Docker (recommandé)
   docker --version
   docker-compose --version
   
   # Ou installation locale
   php --version  # 8.2+
   composer --version
   mysql --version # 8.0+
   ```

2. **Installation**
   ```bash
   # Cloner et installer dépendances
   git clone <repo-url>
   cd cesizen/backend
   composer install
   
   # Configuration environnement
   cp .env.example .env
   php artisan key:generate
   php artisan jwt:secret
   ```

3. **Base de données**
   ```bash
   # Créer la base de données
   touch database/database.sqlite  # SQLite (dev)
   # ou configurer MySQL dans .env
   
   # Exécuter migrations
   php artisan migrate --seed
   ```

4. **Lancement**
   ```bash
   # Serveur de développement
   php artisan serve
   # API accessible sur http://localhost:8000
   ```

### Avec Docker

```bash
# Construction et lancement
docker-compose up -d backend

# Commandes utiles
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan test
docker-compose logs -f backend
```

## 📊 Modèle de Données

### Entités Principales

```php
// Utilisateurs et rôles
User -> Role (belongsTo)
User -> Diagnostic[] (hasMany)
User -> Response[] (hasMany)

// Questionnaires et questions
Questionnaire -> Question[] (hasMany)
Question -> Response[] (hasMany)

// Diagnostics
Diagnostic -> User (belongsTo)
Diagnostic -> Questionnaire (belongsTo)

// Niveaux de stress et recommandations
StressLevel -> Recommendation[] (hasMany)

// Contenu managé
Content (page, title, content, active)
```

### Migrations Principales

- `2025_04_16_162711_create_roles_table.php`
- `2025_04_16_162713_create_questionnaires_table.php`
- `2025_04_16_162714_create_diagnostics_table.php`
- `2025_05_01_100003_create_stress_levels_table.php`
- `create_contents_table.php`

## 🔐 Authentification JWT

### Configuration

```php
// config/jwt.php
'ttl' => env('JWT_TTL', 60),           // Durée de vie (minutes)
'refresh_ttl' => env('JWT_REFRESH_TTL', 20160), // Refresh TTL
'algo' => env('JWT_ALGO', 'HS256'),    // Algorithme
```

### Utilisation

```php
// Connexion
POST /api/login
{
    "email": "user@cesizen.com",
    "password": "password123"
}

// Réponse
{
    "user": {...},
    "token": "eyJ0eXAiOiJKV1Q...",
    "token_type": "bearer",
    "expires_in": 3600
}

// Header requis pour routes protégées
Authorization: Bearer {token}
```

## 🛠️ API Endpoints

### Authentification
```http
POST   /api/register              # Inscription
POST   /api/login                 # Connexion
POST   /api/logout                # Déconnexion
POST   /api/refresh-token         # Renouveler token
GET    /api/user                  # Profil utilisateur
POST   /api/reset-password        # Changer mot de passe
```

### Questionnaires
```http
GET    /api/questionnaires        # Liste questionnaires
GET    /api/questionnaires/{id}   # Détail questionnaire
POST   /api/questionnaires        # Créer questionnaire (admin)
PUT    /api/questionnaires/{id}   # Modifier questionnaire (admin)
DELETE /api/questionnaires/{id}   # Supprimer questionnaire (admin)
```

### Diagnostics
```http
GET    /api/diagnostics           # Historique utilisateur
GET    /api/diagnostics/{id}      # Détail diagnostic
POST   /api/diagnostics           # Créer diagnostic
PUT    /api/diagnostics/{id}      # Modifier diagnostic
POST   /api/diagnostics/{id}/save # Sauvegarder diagnostic
DELETE /api/diagnostics/{id}      # Supprimer diagnostic
```

### Administration
```http
GET    /api/admin/statistics      # Statistiques globales
GET    /api/admin/diagnostics     # Tous les diagnostics
GET    /api/admin/stress-levels   # Niveaux de stress
POST   /api/admin/stress-levels   # Créer niveau
PUT    /api/admin/stress-levels/{id} # Modifier niveau
DELETE /api/admin/stress-levels/{id} # Supprimer niveau
```

### Contenu
```http
GET    /api/contents/{page}       # Contenu par page (public)
GET    /api/admin/contents        # Tous contenus (admin)
PUT    /api/admin/contents/{id}   # Modifier contenu (admin)
```

## 🧪 Tests

### Exécution des Tests

```bash
# Tous les tests
php artisan test

# Tests spécifiques
php artisan test --filter=AuthTest
php artisan test tests/Feature/DiagnosticTest.php

# Coverage
php artisan test --coverage
```

### Structure des Tests

```
tests/
├── Feature/                 # Tests d'intégration
│   ├── AuthTest.php
│   ├── DiagnosticTest.php
│   └── QuestionnaireTest.php
└── Unit/                    # Tests unitaires
    ├── UserTest.php
    └── StressLevelTest.php
```

## 📈 Seeders & Données de Base

### Données Initialisées

```bash
php artisan db:seed
```

- **Rôles** : admin, user
- **Utilisateurs** : admin@cesizen.com, user@cesizen.com
- **Questionnaire** : Échelle Holmes et Rahe (43 questions)
- **Niveaux de stress** : Faible (0-149), Modéré (150-300), Élevé (301+)
- **Recommandations** : Personnalisées par niveau
- **Contenus** : Pages accueil, menu, footer

### Questionnaire Holmes et Rahe

```php
// Exemples d'événements avec scores
[
    ['Mort du conjoint', 100],
    ['Divorce', 73],
    ['Séparation des époux', 65],
    ['Blessure corporelle ou maladie', 53],
    ['Mariage', 50],
    // ... 38 autres événements
]
```

## 🔧 Configuration Docker

### Dockerfile Multi-stage

```dockerfile
# Builder stage
FROM composer:2.6 AS composer-builder
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# Production stage
FROM php:8.2-fpm-alpine3.18
# Installation dépendances, configuration Nginx/PHP-FPM
```

### Services Conteneurisés

- **PHP-FPM** : Application Laravel
- **Nginx** : Serveur web reverse proxy
- **Supervisor** : Gestion processus (PHP-FPM + Nginx + Queue)
- **Laravel Queue Worker** : Traitement tâches asynchrones

## 🚀 Déploiement

### Variables d'Environnement

```bash
# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://cesizen-prod.chickenkiller.com

# Base de données
DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=cesizen_prod
DB_USERNAME=cesizen_user
DB_PASSWORD=***

# JWT
JWT_SECRET=***

# Cache & Sessions
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=redis

# Sécurité HTTPS
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

### Commandes de Déploiement

```bash
# Optimisations production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migrations
php artisan migrate --force

# Queue workers
php artisan queue:work --tries=3
```

## 📊 Monitoring & Logs

### Logs Laravel

```bash
# Localisation
storage/logs/laravel.log

# Niveaux configurables
LOG_CHANNEL=daily
LOG_LEVEL=warning  # error, warning, info, debug
```

### Health Checks

```http
GET /health              # Statut général
GET /ssl-check          # Vérification SSL
```

### Métriques Surveillées

- Temps de réponse API
- Erreurs 4xx/5xx
- Usage mémoire PHP
- Connexions base de données
- Queue jobs en attente

## 🛡️ Sécurité

### Mesures Implémentées

- **Authentification JWT** avec refresh tokens
- **Validation stricte** des entrées (FormRequest)
- **Protection CSRF** pour routes web
- **Rate limiting** sur routes API
- **Chiffrement bcrypt** pour mots de passe
- **Headers sécurité** (HSTS, CSP, etc.)
- **Validation rôles** sur routes admin

### Middleware de Sécurité

```php
// app/Http/Middleware/
├── Cors.php              # Gestion CORS
├── JWTAuthenticate.php   # Auth JWT
└── HandleCors.php        # Headers sécurité
```

## 🔄 Performance

### Optimisations

- **Cache Redis** pour sessions/config
- **Eager loading** relations Eloquent
- **Indexes base de données** sur clés étrangères
- **Compression Gzip** réponses
- **OpCache PHP** activé
- **Connection pooling** MySQL

### Monitoring

```bash
# Performance routes
php artisan route:list --compact
# Queries lentes
php artisan db:monitor
# Cache stats
php artisan cache:table
```

## 🤝 Contribution

### Standards de Code

- **PSR-12** - Standard de code PHP
- **Laravel Pint** - Formatage automatique
- **PHPStan** - Analyse statique (niveau 8)

### Workflow

```bash
# Tests avant commit
php artisan test
php artisan pint --test
vendor/bin/phpstan analyse

# Migrations
php artisan make:migration create_table_name
php artisan make:model ModelName -mfc
```

## 🆘 Dépannage

### Problèmes Courants

```bash
# Erreur permissions storage
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage

# JWT secret manquant
php artisan jwt:secret --force

# Cache corrompus
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Migration en échec
php artisan migrate:rollback
php artisan migrate --step
```

### Debug

```bash
# Mode debug
APP_DEBUG=true
LOG_LEVEL=debug

# Queries SQL
DB_LOG_QUERIES=true

# Profiling
php artisan debugbar:publish
```

---

*Pour plus d'informations, consulter la [documentation Laravel officielle](https://laravel.com/docs) et le [README principal](../README.md).*