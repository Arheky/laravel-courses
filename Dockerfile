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
FROM php:8.3-cli AS backend
WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libzip-dev zip \
 && docker-php-ext-install pdo_pgsql pgsql zip bcmath

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Uygulama kaynakları ve derlenmiş frontend
COPY . .
COPY --from=frontend /app/public/build ./public/build

# PHP bağımlılıkları
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
 && php artisan config:clear && php artisan route:clear && php artisan view:clear \
 && chmod -R 775 storage bootstrap/cache

# Render PORT'u runtime'da verir; burada sadece dokümantatif.
EXPOSE 10000

# Container içi healthcheck (Render'ın dış healthcheck'ine ek destek)
HEALTHCHECK --interval=30s --timeout=5s --start-period=20s \
  CMD curl -fsS http://127.0.0.1:${PORT}/healthz || exit 1

# Uygulamayı Render'ın verdiği PORT'ta başlat
CMD if [ -z "$APP_KEY" ]; then php artisan key:generate --force; fi && \
    php artisan optimize && \
    php artisan serve --host=0.0.0.0 --port=${PORT}
