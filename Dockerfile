# ---------- STAGE 1: FRONTEND ----------
FROM node:20 AS frontend
WORKDIR /app

COPY package*.json ./
RUN npm install --legacy-peer-deps

COPY . .
RUN npm run build

RUN if [ -f public/build/.vite/manifest.json ] && [ ! -f public/build/manifest.json ]; then \
      cp public/build/.vite/manifest.json public/build/manifest.json; \
    fi

# ---------- STAGE 2: BACKEND ----------
FROM php:8.3-fpm AS backend
WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libzip-dev libonig-dev zip \
 && docker-php-ext-install pdo_pgsql pgsql mbstring zip bcmath

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
 && php artisan config:clear && php artisan route:clear && php artisan view:clear \
 && chmod -R 775 storage bootstrap/cache

EXPOSE 10000

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s \
  CMD curl -fsS http://127.0.0.1:${PORT}/healthz || exit 1

CMD if [ -z "$APP_KEY" ]; then php artisan key:generate --force; fi && \
    php artisan optimize && \
    php artisan serve --host=0.0.0.0 --port=10000
