# syntax=docker/dockerfile:1

# ---- Stage 1: build the Vite/Inertia frontend assets ----
FROM node:20-alpine AS frontend

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# ---- Stage 2: PHP-FPM + nginx runtime for Cloud Run ----
FROM richarvey/nginx-php-fpm:3.1.6 AS app

WORKDIR /var/www/html
COPY . .
COPY --from=frontend /app/public/build ./public/build

ENV WEBROOT=/var/www/html/public \
    PHP_ERRORS_STDERR=1 \
    RUN_SCRIPTS=1 \
    REAL_IP_HEADER=1 \
    COMPOSER_ALLOW_SUPERUSER=1 \
    SKIP_COMPOSER=0

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && php artisan config:clear

# Cloud Run injects $PORT and expects the container to listen on it;
# the base image's nginx template already honours $PORT.
EXPOSE 8080

CMD ["/start.sh"]
