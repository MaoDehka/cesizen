set -e

echo "🎨 Démarrage de CESIZen Frontend..."

# Fonction pour remplacer les variables d'environnement dans les fichiers JS
replace_env_vars() {
    echo "🔧 Injection des variables d'environnement..."
    
    # Fichiers à traiter
    find /usr/share/nginx/html -name "*.js" -type f -exec sed -i "s|VITE_API_URL_PLACEHOLDER|${VITE_API_URL:-http://localhost:8000/api}|g" {} \;
    find /usr/share/nginx/html -name "*.js" -type f -exec sed -i "s|VITE_APP_ENV_PLACEHOLDER|${VITE_APP_ENV:-production}|g" {} \;
    
    echo "✅ Variables d'environnement injectées!"
}

# Injection des variables d'environnement
replace_env_vars

# Configuration nginx dynamique selon l'environnement
if [ "$VITE_APP_ENV" = "development" ]; then
    echo "🛠️  Configuration développement détectée"
    # Désactiver le cache pour le développement
    sed -i 's/expires 1y;/expires -1;/g' /etc/nginx/conf.d/default.conf
fi

echo "✅ CESIZen Frontend prêt!"

# Continuer avec l'entrée standard de Nginx
exec nginx -g 'daemon off;'