# ---------- STAGE 1: FRONTEND ----------
FROM node:20 AS frontend
WORKDIR /app

COPY package*.json ./
RUN if [ -f package-lock.json ]; then \
      npm ci --no-audit --no-fund; \
    else \
      npm install --legacy-peer-deps --no-audit --no-fund; \
    fi

COPY . .
RUN npm rebuild esbuild \
 && npm run build -- --debug \
 && if [ -f public/build/.vite/manifest.json ] && [ ! -f public/build/manifest.json ]; then \
      cp public/build/.vite/manifest.json public/build/manifest.json; \
    fi

# ---------- STAGE 2: BACKEND ----------
FROM php:8.3-cli AS app
WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libzip-dev libonig-dev zip \
 && docker-php-ext-install pdo_pgsql pgsql mbstring zip bcmath \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
 && php artisan config:clear \
 && php artisan route:clear \
 && php artisan view:clear \
 && chmod -R 775 storage bootstrap/cache

ENV PORT=10000
EXPOSE 10000
HEALTHCHECK --interval=30s --timeout=3s --start-period=30s \
  CMD curl -fsS http://127.0.0.1:${PORT}/healthz || exit 1

CMD sh -lc 'if [ -z "$APP_KEY" ]; then php artisan key:generate --force; fi \
 && php artisan config:clear \
 && php artisan cache:clear \
 && php artisan route:clear \
 && php artisan view:clear \
 && php artisan optimize \
 && php artisan serve --host=0.0.0.0 --port=${PORT}'
