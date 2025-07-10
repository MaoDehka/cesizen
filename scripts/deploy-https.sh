#!/bin/bash
# scripts/deploy-https.sh

set -e

echo "🚀 Déploiement CESIZen en HTTPS - Étape par étape"

# Fonction pour vérifier si un service répond
check_service() {
    local url=$1
    local name=$2
    echo "🔍 Vérification de $name..."
    if curl -f -s "$url" > /dev/null; then
        echo "✅ $name: OK"
        return 0
    else
        echo "❌ $name: ERREUR"
        return 1
    fi
}

# Étape 1: Déploiement en HTTP d'abord
echo "📋 Étape 1: Déploiement en HTTP"
echo "Assurez-vous que docker/nginx/prod.conf pointe vers la config HTTP simple"

# Vérifier que le docker-compose.prod.yml utilise la bonne config
if ! grep -q "prod.conf" docker-compose.prod.yml; then
    echo "⚠️  Vérifiez que docker-compose.prod.yml utilise prod.conf (pas prod-https.conf)"
fi

# Déploiement
docker-compose -f docker-compose.prod.yml down
docker-compose -f docker-compose.prod.yml pull
docker-compose -f docker-compose.prod.yml up -d

# Attendre que les services démarrent
echo "⏳ Attente du démarrage des services..."
sleep 30

# Vérifications
check_service "http://cesizen-prod.chickenkiller.com/health" "Application HTTP"
check_service "http://cesizen-prod.chickenkiller.com/api/contents/home" "API HTTP"

# Étape 2: Génération des certificats SSL
echo ""
echo "📋 Étape 2: Génération des certificats SSL"
echo "Lancement de certbot..."

# Test d'abord avec --dry-run
echo "🧪 Test de génération des certificats..."
docker-compose -f docker-compose.prod.yml exec certbot \
    certbot certonly --webroot -w /var/www/certbot \
    -d cesizen-prod.chickenkiller.com \
    --email admin@chickenkiller.com \
    --agree-tos --no-eff-email --dry-run

if [ $? -eq 0 ]; then
    echo "✅ Test réussi, génération réelle des certificats..."
    docker-compose -f docker-compose.prod.yml exec certbot \
        certbot certonly --webroot -w /var/www/certbot \
        -d cesizen-prod.chickenkiller.com \
        --email admin@chickenkiller.com \
        --agree-tos --no-eff-email
else
    echo "❌ Échec du test de génération des certificats"
    echo "Vérifiez que :"
    echo "- Le DNS pointe vers votre serveur"
    echo "- Le port 80 est ouvert"
    echo "- L'application répond sur http://cesizen-prod.chickenkiller.com/.well-known/acme-challenge/"
    exit 1
fi

# Étape 3: Passage en HTTPS
echo ""
echo "📋 Étape 3: Configuration HTTPS"
echo "⚠️  MANUEL: Modifiez docker-compose.prod.yml pour utiliser prod-https.conf"
echo "⚠️  MANUEL: Puis relancez le déploiement"

echo ""
echo "Commandes à exécuter manuellement :"
echo "1. Modifier docker-compose.prod.yml ligne nginx volumes:"
echo "   - ./docker/nginx/prod-https.conf:/etc/nginx/conf.d/default.conf"
echo ""
echo "2. Relancer les services:"
echo "   docker-compose -f docker-compose.prod.yml down"
echo "   docker-compose -f docker-compose.prod.yml up -d"
echo ""
echo "3. Vérifier HTTPS:"
echo "   curl -f https://cesizen-prod.chickenkiller.com/health"

echo ""
echo "🎉 Script terminé. Continuez avec les étapes manuelles ci-dessus."