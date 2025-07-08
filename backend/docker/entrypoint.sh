#!/bin/sh
set -e

echo "🚀 Démarrage de CESIZen Backend..."

# Attendre que la base de données soit prête
if [ "$DB_CONNECTION" = "mysql" ]; then
    echo "⏳ Attente de la base de données MySQL..."
    while ! nc -z "$DB_HOST" "$DB_PORT"; do
        sleep 1
    done
    echo "✅ Base de données MySQL prête!"
fi

# Créer le répertoire de stockage s'il n'existe pas
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views

# Définir les permissions
chown -R www:www /var/www/html/storage
chown -R www:www /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Optimiser l'application pour la production
if [ "$APP_ENV" = "production" ]; then
    echo "🔧 Optimisation pour la production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Exécuter les migrations
echo "📊 Exécution des migrations..."
php artisan migrate --force

# Seeders uniquement en développement ou si explicitement demandé
if [ "$APP_ENV" = "local" ] || [ "$RUN_SEEDERS" = "true" ]; then
    echo "🌱 Exécution des seeders..."
    if php artisan migrate:status | grep -q "No migrations found"; then
        echo "📊 Aucune migration trouvée, exécution des migrations..."
        php artisan migrate --force || echo "⚠️ Les migrations ont échoué, mais on continue."
    else
        echo "✅ Les migrations sont déjà appliquées, rien à faire."
    fi
fi

# Générer la clé JWT si elle n'existe pas
if [ -z "$JWT_SECRET" ]; then
    echo "🔑 Génération de la clé JWT..."
    php artisan jwt:secret --force
fi

# Nettoyer le cache
php artisan cache:clear
php artisan config:clear

echo "✅ CESIZen Backend prêt!"

# Exécuter la commande passée en paramètre
exec "$@"