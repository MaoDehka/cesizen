#!/bin/bash
# scripts/deploy-with-integrated-gestsup.sh

echo "🚀 Déploiement CESIZen avec Gestsup intégré"

# 1. Télécharger et extraire Gestsup dans le dossier tickets/
if [ ! -d "tickets" ]; then
    echo "📥 Installation de Gestsup..."
    mkdir tickets
    # Ici vous copiez les fichiers Gestsup téléchargés
    echo "⚠️  Copiez manuellement les fichiers Gestsup dans ./tickets/"
fi

# 2. Déploiement normal
docker-compose -f docker-compose.prod.yml up -d

# 3. Vérification
echo "🔍 Vérification des services..."
sleep 10

if curl -f http://cesizen-prod1.chickenkiller.com/health > /dev/null 2>&1; then
    echo "✅ CESIZen: OK"
fi

if curl -f http://cesizen-prod1.chickenkiller.com/tickets/ > /dev/null 2>&1; then
    echo "✅ Gestsup: OK"
fi

echo "🎉 Déploiement terminé!"
echo "🌐 Application: http://cesizen-prod1.chickenkiller.com"
echo "🎫 Tickets: http://cesizen-prod1.chickenkiller.com/tickets"