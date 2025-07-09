#!/bin/bash
# scripts/deploy-with-ticket-update.sh

ENVIRONMENT=$1
TICKET_ID=$2

echo "🚀 Déploiement en cours pour l'environnement: $ENVIRONMENT"

# Déploiement Docker
docker-compose -f docker-compose.$ENVIRONMENT.yml pull
docker-compose -f docker-compose.$ENVIRONMENT.yml up -d

# Vérification du déploiement
if [ $? -eq 0 ]; then
    echo "✅ Déploiement réussi"
    
    # Mise à jour du statut du ticket dans Gestsup
    if [ ! -z "$TICKET_ID" ]; then
        curl -X PATCH \
            -H "Authorization: Bearer $GESTSUP_API_KEY" \
            -H "Content-Type: application/json" \
            -d "{\"status\": \"livre\", \"deployed_at\": \"$(date -u +%Y-%m-%dT%H:%M:%SZ)\"}" \
            "$GESTSUP_API_URL/tickets/$TICKET_ID"
        
        echo "📝 Ticket GEST-$TICKET_ID mis à jour"
    fi
else
    echo "❌ Échec du déploiement"
    exit 1
fi