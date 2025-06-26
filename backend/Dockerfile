# Multi-stage build pour optimiser la taille de l'image
FROM composer:2.6 AS composer-builder
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

FROM node:20-alpine AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci --only=production

FROM php:8.2-fpm-alpine AS production

LABEL org.opencontainers.image.source=https://github.com/OWNER/cesizen
LABEL org.opencontainers.image.description="CESIZen Backend - Application de santé mentale"
LABEL org.opencontainers.image.version="1.0.0"

# Installation des dépendances système
RUN apk add --no-cache \
    bash=5.1.16-r0 \
    curl=7.83.1-r0 \
    git=2.36.1-r0 \
    nginx=1.22.1-r0 \
    supervisor=4.2.4-r0 \
    sqlite-dev=3.38.5-r0 \
    libpng-dev=1.6.37-r1 \
    libjpeg-turbo-dev=2.1.3-r1 \
    freetype-dev=2.12.1-r0 \
    zip=3.0-r7 \
    unzip=6.0-r9 \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_sqlite pdo_mysql

# Installation de Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Création de l'utilisateur www-data
RUN addgroup -g 1000 www && \
    adduser -u 1000 -G www -s /bin/sh -D www

# Configuration du répertoire de travail
WORKDIR /var/www/html

# Copie des fichiers de dépendances
COPY --from=composer-builder /app/vendor ./vendor
COPY composer.json composer.lock ./

# Copie du code source
COPY . .

# Configuration des permissions
RUN chown -R www:www /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Configuration Nginx
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Configuration PHP-FPM
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php/php.ini /usr/local/etc/php/php.ini

# Configuration Supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Script d'entrée
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

# Variables d'environnement par défaut
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr
ENV DB_CONNECTION=sqlite
ENV DB_DATABASE=/var/www/html/database/database.sqlite

EXPOSE 80

ENTRYPOINT ["entrypoint"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]