# Guide de Déploiement CESIZen

Guide complet pour déployer CESIZen dans différents environnements avec Docker, CI/CD et monitoring.

## 🏗️ Architecture de Déploiement

```
┌─────────────────────────────────────────────────────────────┐
│                    Internet (HTTPS)                        │
└─────────────────────┬───────────────────────────────────────┘
                      │
                ┌─────▼─────┐
                │   Nginx   │ ← Reverse Proxy + SSL
                │ (Port 443)│
                └─────┬─────┘
                      │
        ┌─────────────┼─────────────┐
        │             │             │
  ┌─────▼─────┐ ┌─────▼─────┐ ┌─────▼─────┐
  │ Frontend  │ │ Backend   │ │   MySQL   │
  │   Vue.js  │ │  Laravel  │ │ Database  │
  └─────┬─────┘ └─────┬─────┘ └─────┬─────┘
        │             │             │
        └─────────────┼─────────────┘
                ┌─────▼─────┐
                │   Redis   │ ← Cache + Sessions
                └───────────┘
```

## 🌍 Environnements

### 1. Développement (Local)
- **URL** : http://localhost
- **Base de données** : MySQL avec données de test
- **SSL** : Non (HTTP seulement)
- **Logs** : Verbeux pour debugging
- **Mises à jour** : Automatiques (5 minutes)

### 2. Test (Staging)  
- **URL** : http://cesizen-test.chickenkiller.com
- **Base de données** : MySQL isolée
- **SSL** : Optionnel
- **Tests E2E** : Cypress automatisés
- **Mises à jour** : Automatiques (2 minutes)

### 3. Production
- **URL** : https://cesizen-prod.chickenkiller.com
- **Base de données** : MySQL avec sauvegardes
- **SSL** : Let's Encrypt obligatoire
- **Logs** : Niveau warning+
- **Monitoring** : Complet avec alertes
- **Mises à jour** : Automatiques (1 heure)

## 🚀 Déploiement Rapide

### Prérequis

```bash
# Serveur Linux (Ubuntu/Debian)
sudo apt update && sudo apt upgrade -y

# Docker & Docker Compose
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh
sudo usermod -aG docker $USER

# Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/download/v2.20.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Git
sudo apt install -y git
```

### Déploiement Production

```bash
# 1. Cloner le repository
git clone https://github.com/votre-username/cesizen.git
cd cesizen

# 2. Configuration environnement
cp .env.example .env.prod
nano .env.prod  # Configurer variables

# 3. Génération certificats SSL
./scripts/init-ssl.sh cesizen-prod.chickenkiller.com

# 4. Déploiement
docker-compose -f docker-compose.prod.yml up -d

# 5. Vérification
docker-compose -f docker-compose.prod.yml ps
curl -k https://cesizen-prod.chickenkiller.com/health
```

## 🔧 Configuration Détaillée

### Variables d'Environnement

```bash
# .env.prod
# Application
LARAVEL_APP_KEY=base64:VOTRE_CLE_32_CARACTERES
JWT_SECRET=VOTRE_JWT_SECRET_64_CARACTERES

# Base de données
MYSQL_ROOT_PASSWORD=votre_mot_de_passe_root_secure
DB_PASSWORD=votre_mot_de_passe_db_secure

# SSL/Domaine
DOMAIN_NAME=cesizen-prod.chickenkiller.com
EMAIL_LETSENCRYPT=admin@chickenkiller.com

# Sauvegardes
BACKUP_SCHEDULE="0 2 * * *"  # 2h du matin quotidien

# Monitoring
WATCHTOWER_POLL_INTERVAL=3600  # 1 heure
```

### Génération des Secrets

```bash
# Clé Laravel (32 caractères base64)
openssl rand -base64 32

# Secret JWT (64 caractères)
openssl rand -hex 64

# Mots de passe sécurisés
openssl rand -base64 32 | tr -d "=+/" | cut -c1-25
```

## 🔐 SSL/TLS avec Let's Encrypt

### Configuration Automatique

```bash
# scripts/init-ssl.sh
#!/bin/bash
DOMAIN=$1
EMAIL=${2:-admin@$DOMAIN}

echo "🔐 Configuration SSL pour $DOMAIN"

# Créer structure certificats
mkdir -p nginx/ssl/live/$DOMAIN

# Obtenir certificat initial
docker-compose -f docker-compose.prod.yml run --rm certbot \
  certonly --webroot -w /var/www/certbot \
  -d $DOMAIN --email $EMAIL --agree-tos --no-eff-email

# Recharger Nginx avec SSL
docker-compose -f docker-compose.prod.yml restart nginx

echo "✅ SSL configuré pour $DOMAIN"
```

### Renouvellement Automatique

```bash
# Cron job pour renouvellement (tous les 12h)
0 */12 * * * cd /opt/cesizen && docker-compose -f docker-compose.prod.yml exec certbot renew --quiet && docker-compose -f docker-compose.prod.yml restart nginx
```

### Vérification SSL

```bash
# Tester la configuration SSL
openssl s_client -connect cesizen-prod.chickenkiller.com:443 -servername cesizen-prod.chickenkiller.com

# Vérifier certificat
curl -vI https://cesizen-prod.chickenkiller.com

# Score SSL Labs
curl -s "https://api.ssllabs.com/api/v3/analyze?host=cesizen-prod.chickenkiller.com"
```

## 🏭 CI/CD avec GitHub Actions

### Workflow Principal

```yaml
# .github/workflows/ci-cd.yml
name: CESIZen CI/CD

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main, develop]

env:
  REGISTRY: ghcr.io
  IMAGE_NAME_BACKEND: cesizen-backend
  IMAGE_NAME_FRONTEND: cesizen-frontend

jobs:
  # Tests et build
  build-and-test:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'
          cache-dependency-path: frontend/package-lock.json

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, dom, fileinfo, mysql, gd

      - name: Install Backend Dependencies
        working-directory: backend
        run: composer install --no-progress --optimize-autoloader

      - name: Install Frontend Dependencies
        working-directory: frontend
        run: npm ci

      - name: Run Backend Tests
        working-directory: backend
        run: |
          cp .env.example .env
          echo "JWT_SECRET=${{ secrets.JWT_SECRET }}" >> .env
          php artisan key:generate
          touch database/database.sqlite
          php artisan migrate --force
          php artisan test

      - name: Build Frontend
        working-directory: frontend
        run: npm run build

  # Sécurité
  security-audit:
    runs-on: ubuntu-latest
    needs: build-and-test
    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Frontend Security Audit
        working-directory: frontend
        run: |
          npm ci
          npm audit --audit-level=high

      - name: Backend Security Audit
        working-directory: backend
        run: |
          composer install
          composer audit

  # Build et push images Docker
  build-images:
    runs-on: ubuntu-latest
    needs: [build-and-test, security-audit]
    if: github.event_name == 'push'
    permissions:
      contents: read
      packages: write
    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Login to Container Registry
        uses: docker/login-action@v3
        with:
          registry: ${{ env.REGISTRY }}
          username: ${{ github.actor }}
          password: ${{ secrets.GITHUB_TOKEN }}

      - name: Build Backend Image
        uses: docker/build-push-action@v5
        with:
          context: ./backend
          platforms: linux/amd64,linux/arm64
          push: true
          tags: |
            ${{ env.REGISTRY }}/${{ github.repository_owner }}/${{ env.IMAGE_NAME_BACKEND }}:latest
            ${{ env.REGISTRY }}/${{ github.repository_owner }}/${{ env.IMAGE_NAME_BACKEND }}:${{ github.ref_name }}

      - name: Build Frontend Image
        uses: docker/build-push-action@v5
        with:
          context: ./frontend
          platforms: linux/amd64,linux/arm64
          push: true
          tags: |
            ${{ env.REGISTRY }}/${{ github.repository_owner }}/${{ env.IMAGE_NAME_FRONTEND }}:latest
            ${{ env.REGISTRY }}/${{ github.repository_owner }}/${{ env.IMAGE_NAME_FRONTEND }}:${{ github.ref_name }}

  # Déploiement développement
  deploy-dev:
    runs-on: ubuntu-latest
    needs: build-images
    if: github.ref == 'refs/heads/develop'
    environment: development
    steps:
      - name: Deploy to Development
        uses: appleboy/ssh-action@v1
        with:
          host: ${{ secrets.DEV_HOST }}
          username: ${{ secrets.DEV_USER }}
          key: ${{ secrets.DEV_SSH_KEY }}
          script: |
            cd /opt/cesizen-dev
            docker-compose -f docker-compose.dev.yml pull
            docker-compose -f docker-compose.dev.yml up -d
            docker system prune -f

  # Déploiement production  
  deploy-prod:
    runs-on: ubuntu-latest
    needs: build-images
    if: github.ref == 'refs/heads/main'
    environment: production
    steps:
      - name: Deploy to Production
        uses: appleboy/ssh-action@v1
        with:
          host: ${{ secrets.PROD_HOST }}
          username: ${{ secrets.PROD_USER }}
          key: ${{ secrets.PROD_SSH_KEY }}
          script: |
            cd /opt/cesizen-prod
            docker-compose -f docker-compose.prod.yml pull
            docker-compose -f docker-compose.prod.yml up -d
            docker system prune -f
```

### Secrets GitHub Requis

```bash
# GitHub → Settings → Secrets and variables → Actions

# JWT
JWT_SECRET=votre_jwt_secret_64_caracteres

# Serveurs
DEV_HOST=ip.du.serveur.dev
DEV_USER=ubuntu
DEV_SSH_KEY=-----BEGIN OPENSSH PRIVATE KEY-----...

PROD_HOST=ip.du.serveur.prod  
PROD_USER=ubuntu
PROD_SSH_KEY=-----BEGIN OPENSSH PRIVATE KEY-----...
```

## 🗄️ Gestion Base de Données

### Migrations Production

```bash
# Sauvegarde avant migration
docker-compose exec mysql mysqldump -u root -p cesizen_prod > backup_pre_migration.sql

# Exécution migrations
docker-compose exec backend php artisan migrate --force

# Rollback si problème
docker-compose exec backend php artisan migrate:rollback --step=1
```

### Sauvegardes Automatiques

```bash
# Script de sauvegarde (scripts/backup.sh)
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/opt/backups/cesizen"

mkdir -p $BACKUP_DIR

# Sauvegarde base de données
docker-compose exec -T mysql mysqldump -u root -p$MYSQL_ROOT_PASSWORD cesizen_prod > $BACKUP_DIR/db_$DATE.sql

# Sauvegarde storage Laravel
docker run --rm -v cesizen_backend_storage:/source -v $BACKUP_DIR:/backup alpine tar czf /backup/storage_$DATE.tar.gz -C /source .

# Sauvegarde certificats SSL
docker run --rm -v cesizen_certbot_conf:/source -v $BACKUP_DIR:/backup alpine tar czf /backup/ssl_$DATE.tar.gz -C /source .

# Nettoyage (garder 30 jours)
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete

echo "✅ Sauvegarde terminée: $DATE"
```

### Cron Job Sauvegardes

```bash
# Ajout au crontab
0 2 * * * /opt/cesizen/scripts/backup.sh >> /var/log/cesizen-backup.log 2>&1

# Vérification sauvegardes
0 3 * * 0 /opt/cesizen/scripts/verify-backups.sh
```

### Restauration

```bash
# Restaurer base de données
docker-compose exec -T mysql mysql -u root -p$MYSQL_ROOT_PASSWORD cesizen_prod < backup_20241209_020000.sql

# Restaurer storage
docker run --rm -v cesizen_backend_storage:/target -v /opt/backups/cesizen:/backup alpine tar xzf /backup/storage_20241209_020000.tar.gz -C /target

# Redémarrer services
docker-compose restart backend
```

## 📊 Monitoring & Observabilité

### Health Checks

```bash
# Script de surveillance (scripts/health-check.sh)
#!/bin/bash

SERVICES=("frontend" "backend" "mysql" "redis" "nginx")
ALERTS_EMAIL="admin@cesizen.com"
LOG_FILE="/var/log/cesizen-health.log"

for service in "${SERVICES[@]}"; do
    if ! docker-compose ps | grep -q "$service.*Up"; then
        echo "❌ $(date): Service $service DOWN" >> $LOG_FILE
        
        # Redémarrage automatique
        docker-compose restart $service
        
        # Alerte email si échec persiste
        sleep 30
        if ! docker-compose ps | grep -q "$service.*Up"; then
            echo "ALERT: Service $service failed to restart" | mail -s "CESIZen Alert" $ALERTS_EMAIL
        fi
    else
        echo "✅ $(date): Service $service OK" >> $LOG_FILE
    fi
done

# Test endpoints
curl -f https://cesizen-prod.chickenkiller.com/health || echo "❌ Frontend health check failed" >> $LOG_FILE
curl -f https://cesizen-prod.chickenkiller.com/api/health || echo "❌ Backend health check failed" >> $LOG_FILE
```

### Métriques & Alertes

```yaml
# docker-compose.monitoring.yml (optionnel)
version: '3.8'
services:
  prometheus:
    image: prom/prometheus:latest
    container_name: cesizen-prometheus
    volumes:
      - ./monitoring/prometheus.yml:/etc/prometheus/prometheus.yml
      - prometheus_data:/prometheus
    ports:
      - "9090:9090"
    command:
      - '--config.file=/etc/prometheus/prometheus.yml'
      - '--storage.tsdb.path=/prometheus'
      - '--web.console.libraries=/etc/prometheus/console_libraries'
      - '--web.console.templates=/etc/prometheus/consoles'

  grafana:
    image: grafana/grafana:latest
    container_name: cesizen-grafana
    volumes:
      - grafana_data:/var/lib/grafana
      - ./monitoring/dashboards:/etc/grafana/provisioning/dashboards
    ports:
      - "3000:3000"
    environment:
      - GF_SECURITY_ADMIN_PASSWORD=admin123

volumes:
  prometheus_data:
  grafana_data:
```

### Logs Centralisés

```bash
# Configuration logging
# docker-compose.prod.yml
services:
  backend:
    logging:
      driver: "json-file"
      options:
        max-size: "10m"
        max-file: "3"

  frontend:
    logging:
      driver: "json-file"
      options:
        max-size: "10m"
        max-file: "3"
```

### Surveillance Avancée

```bash
# Script monitoring détaillé (scripts/advanced-monitoring.sh)
#!/bin/bash

# Métriques système
echo "=== System Metrics ===" 
echo "CPU: $(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1)%"
echo "Memory: $(free | grep Mem | awk '{printf "%.1f%%", $3/$2 * 100.0}')"
echo "Disk: $(df -h / | awk 'NR==2{printf "%s", $5}')"

# Métriques Docker
echo -e "\n=== Docker Metrics ==="
docker stats --no-stream --format "table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.MemPerc}}"

# Base de données
echo -e "\n=== Database Metrics ==="
docker-compose exec mysql mysql -u root -p$MYSQL_ROOT_PASSWORD -e "
SELECT 
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Database Size (MB)',
    table_rows AS 'Total Rows'
FROM information_schema.tables 
WHERE table_schema = 'cesizen_prod';
"

# Redis
echo -e "\n=== Redis Metrics ==="
docker-compose exec redis redis-cli info memory | grep used_memory_human
docker-compose exec redis redis-cli info stats | grep total_connections_received

# Tests de charge
echo -e "\n=== Load Test ==="
curl -o /dev/null -s -w "Response Time: %{time_total}s\nHTTP Code: %{http_code}\n" https://cesizen-prod.chickenkiller.com/

# Alertes automatiques
LOAD_AVG=$(uptime | awk -F'load average:' '{print $2}' | cut -d',' -f1 | xargs)
if (( $(echo "$LOAD_AVG > 2.0" | bc -l) )); then
    echo "🚨 High load average: $LOAD_AVG" | mail -s "CESIZen Performance Alert" admin@cesizen.com
fi
```

## 🔄 Stratégies de Déploiement

### Blue-Green Deployment

```bash
# scripts/blue-green-deploy.sh
#!/bin/bash

CURRENT_ENV=$(docker-compose -f docker-compose.prod.yml ps --services | head -1 | grep -o "blue\|green" || echo "blue")
NEW_ENV=$([ "$CURRENT_ENV" = "blue" ] && echo "green" || echo "blue")

echo "🚀 Déploiement Blue-Green: $CURRENT_ENV → $NEW_ENV"

# 1. Préparer nouvel environnement
docker-compose -f docker-compose.$NEW_ENV.yml pull
docker-compose -f docker-compose.$NEW_ENV.yml up -d

# 2. Tests de santé
sleep 30
if curl -f http://localhost:8081/health; then
    echo "✅ $NEW_ENV environment healthy"
    
    # 3. Basculer le trafic (Nginx)
    sed -i "s/upstream backend_$CURRENT_ENV/upstream backend_$NEW_ENV/g" nginx/prod.conf
    docker-compose exec nginx nginx -s reload
    
    # 4. Arrêter ancien environnement
    sleep 60
    docker-compose -f docker-compose.$CURRENT_ENV.yml down
    
    echo "✅ Déploiement terminé: Actif sur $NEW_ENV"
else
    echo "❌ $NEW_ENV environment failed health check"
    docker-compose -f docker-compose.$NEW_ENV.yml down
    exit 1
fi
```

### Rollback Automatique

```bash
# scripts/rollback.sh
#!/bin/bash

LAST_WORKING_TAG=$(git describe --tags --abbrev=0)
echo "🔄 Rollback vers $LAST_WORKING_TAG"

# Retag des images
docker tag ghcr.io/user/cesizen-backend:$LAST_WORKING_TAG ghcr.io/user/cesizen-backend:latest
docker tag ghcr.io/user/cesizen-frontend:$LAST_WORKING_TAG ghcr.io/user/cesizen-frontend:latest

# Redéploiement
docker-compose -f docker-compose.prod.yml up -d

# Vérification
sleep 30
if curl -f https://cesizen-prod.chickenkiller.com/health; then
    echo "✅ Rollback réussi"
else
    echo "❌ Rollback échoué - intervention manuelle requise"
    exit 1
fi
```

## 🛡️ Sécurité Production

### Durcissement Serveur

```bash
# scripts/server-hardening.sh
#!/bin/bash

# Mise à jour système
sudo apt update && sudo apt upgrade -y

# Firewall UFW
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22   # SSH
sudo ufw allow 80   # HTTP
sudo ufw allow 443  # HTTPS
sudo ufw --force enable

# Fail2ban pour SSH
sudo apt install -y fail2ban
sudo systemctl enable fail2ban

# Configuration SSH sécurisée
sudo sed -i 's/#PasswordAuthentication yes/PasswordAuthentication no/' /etc/ssh/sshd_config
sudo sed -i 's/#PermitRootLogin yes/PermitRootLogin no/' /etc/ssh/sshd_config
sudo systemctl restart sshd

# Surveillance des logs
sudo apt install -y logwatch
echo "0 7 * * * /usr/sbin/logwatch --detail Med --mailto admin@cesizen.com" | sudo crontab -

# Automatic security updates
sudo apt install -y unattended-upgrades
sudo dpkg-reconfigure -plow unattended-upgrades
```

### Scan de Vulnérabilités

```bash
# Scan images Docker
docker run --rm -v /var/run/docker.sock:/var/run/docker.sock \
  aquasec/trivy:latest image ghcr.io/user/cesizen-backend:latest

docker run --rm -v /var/run/docker.sock:/var/run/docker.sock \
  aquasec/trivy:latest image ghcr.io/user/cesizen-frontend:latest

# Scan réseau
nmap -sS -T4 -A -v cesizen-prod.chickenkiller.com

# Test SSL
./scripts/ssl-test.sh cesizen-prod.chickenkiller.com
```

### Audit Sécurité

```bash
# scripts/security-audit.sh
#!/bin/bash

echo "🔒 Audit de Sécurité CESIZen"

# Vérification certificats
echo "=== Certificats SSL ==="
echo | openssl s_client -servername cesizen-prod.chickenkiller.com -connect cesizen-prod.chickenkiller.com:443 2>/dev/null | openssl x509 -noout -dates

# Vérification headers sécurité
echo -e "\n=== Headers de Sécurité ==="
curl -I https://cesizen-prod.chickenkiller.com | grep -E "(Strict-Transport-Security|X-Frame-Options|X-Content-Type-Options|X-XSS-Protection)"

# Vérification ports ouverts
echo -e "\n=== Ports Ouverts ==="
nmap -sT -O localhost

# Vérification permissions fichiers
echo -e "\n=== Permissions Critiques ==="
find /opt/cesizen -name "*.env*" -exec ls -la {} \;
find /opt/cesizen -name "*.key" -exec ls -la {} \;

# Test injection SQL (basique)
echo -e "\n=== Test Injection SQL ==="
curl -s "https://cesizen-prod.chickenkiller.com/api/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"test'\''DROP TABLE users--","password":"test"}' | grep -q "error" && echo "✅ Protection SQL injection OK" || echo "❌ Vulnérabilité détectée"

# Rapport
echo -e "\n📋 Audit terminé - $(date)"
```

## 🚨 Plan de Reprise d'Activité

### Procédure d'Urgence

```bash
# scripts/emergency-restore.sh
#!/bin/bash

echo "🚨 RESTAURATION D'URGENCE"

# 1. Arrêt services défaillants
docker-compose -f docker-compose.prod.yml down

# 2. Restauration depuis sauvegarde
LATEST_BACKUP=$(ls -t /opt/backups/cesizen/db_*.sql | head -1)
echo "Restauration DB: $LATEST_BACKUP"

# 3. Redémarrage avec image stable connue
docker-compose -f docker-compose.prod.yml pull
docker-compose -f docker-compose.prod.yml up -d

# 4. Vérification et notification
sleep 60
if curl -f https://cesizen-prod.chickenkiller.com/health; then
    echo "✅ Restauration réussie" | mail -s "CESIZen: Service restauré" admin@cesizen.com
else
    echo "❌ Restauration échouée - escalade requise" | mail -s "CESIZen: URGENCE" admin@cesizen.com
fi
```

### Contacts d'Urgence

```bash
# Équipe technique
ADMIN_EMAIL="admin@cesizen.com"
DEV_TEAM="dev-team@cesizen.com"  
OPS_TEAM="ops@cesizen.com"

# Procédures d'escalade
# 1. Alerte automatique (monitoring)
# 2. Intervention automatique (scripts)
# 3. Notification équipe (email/SMS)
# 4. Escalade hiérarchique si non résolu en 30min
```

## 📋 Checklist de Déploiement

### Pré-déploiement

- [ ] Tests unitaires passent (backend + frontend)
- [ ] Tests d'intégration passent  
- [ ] Audit sécurité des dépendances
- [ ] Review de code terminée
- [ ] Documentation mise à jour
- [ ] Sauvegarde base de données
- [ ] Variables d'environnement configurées
- [ ] Certificats SSL valides

### Déploiement

- [ ] Images Docker buildées et taguées
- [ ] Déploiement sur environnement de test
- [ ] Tests E2E passent
- [ ] Validation fonctionnelle
- [ ] Déploiement production
- [ ] Health checks OK
- [ ] Tests de charge basiques

### Post-déploiement

- [ ] Monitoring actif et alertes fonctionnelles
- [ ] Logs applicatifs vérifiés
- [ ] Performance baseline établie
- [ ] Équipe notifiée du succès
- [ ] Documentation de release
- [ ] Planning next release

## 🔧 Dépannage Production

### Problèmes Courants

```bash
# Service ne démarre pas
docker-compose logs service_name
docker-compose restart service_name

# Base de données inaccessible  
docker-compose exec mysql mysql -u root -p
SHOW PROCESSLIST;

# Certificat SSL expiré
docker-compose run --rm certbot renew
docker-compose restart nginx

# Espace disque plein
docker system prune -a
find /var/log -name "*.log" -mtime +7 -delete

# Performance dégradée
docker stats
htop
iotop
```

### Commandes de Debug

```bash
# Diagnostics complets
./scripts/diagnostics.sh > diagnostics_$(date +%Y%m%d_%H%M%S).txt

# Logs en temps réel
docker-compose logs -f --tail=100

# Shell dans container
docker-compose exec backend bash
docker-compose exec frontend sh

# Monitoring réseau
docker-compose exec backend netstat -tulpn
```

---

*Pour support technique urgent: admin@cesizen.com | Escalade: +33 X XX XX XX XX*

*Documentation mise à jour: $(date)*