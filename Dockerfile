# ---------- STAGE 1: FRONTEND ----------
FROM node:20 AS frontend
WORKDIR /app

COPY package*.json ./
RUN npm install --legacy-peer-deps

COPY . .
RUN npm run build

# Bazı ortamlar manifest'i .vite altına bırakabiliyor; fallback:
RUN if [ -f public/build/.vite/manifest.json ] && [ ! -f public/build/manifest.json ]; then \
      cp public/build/.vite/manifest.json public/build/manifest.json; \
    fi

# ---------- STAGE 2: BACKEND ----------
FROM php:8.3-fpm AS backend
WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libzip-dev libonig-dev zip \
 && docker-php-ext-install pdo_pgsql pgsql mbstring zip bcmath

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Uygulama kaynakları
COPY . .
# Frontend çıktısını kopyala (manifest dahil)
COPY --from=frontend /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Sadece temizlik + izinler
RUN php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    chmod -R 775 storage bootstrap/cache

EXPOSE 10000

# Runtime'da son optimizasyon ve serve
CMD if [ -z "$APP_KEY" ]; then php artisan key:generate --force; fi && \
    php artisan config:clear && \
    php artisan cache:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    php artisan optimize && \
    php artisan serve --host=0.0.0.0 --port=10000
