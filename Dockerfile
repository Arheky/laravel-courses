# ---------- STAGE 1: FRONTEND ----------
FROM node:20 AS frontend
WORKDIR /app

# lock dosyası olsa da olmasa da çalışır
COPY package*.json ./
RUN npm install --legacy-peer-deps --no-audit --no-fund

COPY . .
RUN npm run build

# bazı ortamlarda manifest .vite altına düşüyor; fallback:
RUN if [ -f public/build/.vite/manifest.json ] && [ ! -f public/build/manifest.json ]; then \
      cp public/build/.vite/manifest.json public/build/manifest.json; \
    fi

# ---------- STAGE 2: BACKEND ----------
FROM php:8.3-cli AS app
WORKDIR /var/www

# Sistem paketleri ve PHP eklentileri
RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libzip-dev libonig-dev zip \
 && docker-php-ext-install pdo_pgsql pgsql mbstring zip bcmath opcache \
 && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Composer cache katmanı için önce composer dosyaları
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts

# Uygulama dosyaları
COPY . .

# Frontend çıktısı
COPY --from=frontend /app/public/build ./public/build

# Laravel optimize + izinler
RUN php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    php artisan storage:link || true && \
    chmod -R 775 storage bootstrap/cache && \
    composer install --no-dev --optimize-autoloader --no-interaction

# Healthcheck (container içinden)
HEALTHCHECK --interval=30s --timeout=3s \
  CMD curl -fsS http://127.0.0.1:${PORT}/healthz || exit 1

EXPOSE 10000

# Uygulamayı başlat
CMD if [ -z "$APP_KEY" ]; then php artisan key:generate --force; fi && \
    php artisan optimize && \
    php artisan serve --host=0.0.0.0 --port=${PORT}
