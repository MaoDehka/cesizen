# Multi-stage build pour optimiser la taille
FROM node:20 AS builder

LABEL org.opencontainers.image.source=https://github.com/OWNER/cesizen
LABEL org.opencontainers.image.description="CESIZen Frontend - Application de santé mentale"

ARG GITHUB_SHA=unknown
ARG GITHUB_REF_NAME=unknown

WORKDIR /app

# Copie des fichiers de dépendances
COPY package*.json ./

# Installation des dépendances
RUN npm ci

# Copie du code source
COPY . .

ENV VITE_GIT_COMMIT=${GITHUB_SHA}
ENV VITE_GIT_BRANCH=${GITHUB_REF_NAME}
RUN export BUILD_TIME=$(date -u +"%Y-%m-%dT%H:%M:%SZ") && \
    echo "export VITE_BUILD_TIME=$BUILD_TIME" >> /etc/environment
    
# Build de l'application
RUN npm run build

# Image de production avec Nginx
FROM nginx:alpine AS production

# Installation de curl pour les health checks
RUN apk add --no-cache curl

# Copie des fichiers buildés
COPY --from=builder /app/dist /usr/share/nginx/html

# Configuration Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/conf.d/default.conf

# Script pour injecter les variables d'environnement au runtime
# CORRECTION: Copier vers le bon répertoire et avec le bon nom
COPY docker/entrypoint.sh /docker-entrypoint.d/40-inject-env-vars.sh
RUN chmod +x /docker-entrypoint.d/40-inject-env-vars.sh

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

EXPOSE 80

# Nginx démarre automatiquement avec l'image de base