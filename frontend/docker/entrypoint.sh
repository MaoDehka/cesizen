#!/bin/sh
set -e

echo "🎨 Démarrage de CESIZen Frontend..."

# Fonction pour remplacer les variables d'environnement dans les fichiers JS
echo "🔧 Injection des variables d'environnement..."

# Variables par défaut (HTTP seulement)
VITE_API_URL_DEFAULT="http://cesizen-prod1.chickenkiller.com/api"
VITE_APP_ENV_DEFAULT="production"

# Utiliser les valeurs par défaut si les variables ne sont pas définies
API_URL=${VITE_API_URL:-$VITE_API_URL_DEFAULT}
APP_ENV=${VITE_APP_ENV:-$VITE_APP_ENV_DEFAULT}

echo "Variables utilisées:"
echo "VITE_API_URL: $API_URL"
echo "VITE_APP_ENV: $APP_ENV"

find /usr/share/nginx/html -name "*.js" -type f -exec sed -i "s|VITE_API_URL_PLACEHOLDER|$API_URL|g" {} \; 2>/dev/null || true
find /usr/share/nginx/html -name "*.js" -type f -exec sed -i "s|VITE_APP_ENV_PLACEHOLDER|$APP_ENV|g" {} \; 2>/dev/null || true

echo "✅ Variables d'environnement injectées!"
echo "✅ CESIZen Frontend prêt!"

# Ne pas executer de commande, laisser nginx démarrer normalement 