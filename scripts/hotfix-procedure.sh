#!/bin/bash
# scripts/hotfix-procedure.sh

TICKET_ID=$1
DESCRIPTION=$2

echo "🚨 Procédure hotfix pour ticket critique GEST-$TICKET_ID"

# 1. Créer branche hotfix depuis main
git checkout main
git pull origin main
git checkout -b "hotfix/GEST-$TICKET_ID-$DESCRIPTION"

# 2. Notifier l'équipe
curl -X POST $SLACK_WEBHOOK \
  -d "{\"text\": \"🚨 HOTFIX en cours: GEST-$TICKET_ID - $DESCRIPTION\"}"

# 3. Mettre à jour le statut Gestsup
curl -X PATCH \
  -H "Authorization: Bearer $GESTSUP_API_KEY" \
  -d '{"status": "en_developpement_urgent"}' \
  "$GESTSUP_API_URL/tickets/$TICKET_ID"

echo "✅ Branche hotfix créée, équipe notifiée, statut mis à jour"