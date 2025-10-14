# ---------- STAGE 1: FRONTEND ----------
FROM node:20 AS frontend
WORKDIR /app

COPY package*.json ./
RUN npm install --legacy-peer-deps
COPY . .
RUN npm run build

# Vite manifest fallback (gerekirse)
RUN if [ -f public/build/.vite/manifest.json ] && [ ! -f public/build/manifest.json ]; then \
      cp public/build/.vite/manifest.json public/build/manifest.json; \
    fi

# ---------- STAGE 2: BACKEND ----------
FROM php:8.3-fpm AS backend
WORKDIR /var/www

# Nginx ve envsubst için gettext-base
RUN apt-get update && apt-get install -y \
    nginx gettext-base git curl unzip libpq-dev libzip-dev libonig-dev zip \
 && docker-php-ext-install pdo_pgsql pgsql mbstring zip bcmath \
 && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Uygulama dosyaları + build çıktısı
COPY . .
COPY --from=frontend /app/public/build ./public/build

# Laravel optimizasyonları ve izinler
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
 && php artisan config:clear && php artisan route:clear && php artisan view:clear \
 && mkdir -p storage/logs bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# Nginx site template (PORT runtime’da envsubst ile değişecek)
RUN mkdir -p /etc/nginx/conf.d && \
  printf '%s\n' \
'server {' \
'  listen ${PORT} default_server;' \
'  server_name _;' \
'  root /var/www/public;' \
'  index index.php index.html;' \
'' \
'  location = /healthz { access_log off; add_header Content-Type text/plain; return 200 "ok"; }' \
'' \
'  location / { try_files $uri $uri/ /index.php?$query_string; }' \
'' \
'  location ~ \.php$ {' \
'    include fastcgi_params;' \
'    fastcgi_pass 127.0.0.1:9000;' \
'    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;' \
'    fastcgi_param DOCUMENT_ROOT $realpath_root;' \
'  }' \
'}' \
> /etc/nginx/conf.d/default.conf.template

# Start script (heredoc YOK – printf ile yazılıyor)
RUN printf '%s\n' \
'#!/usr/bin/env bash' \
'set -euo pipefail' \
'' \
': "${PORT:=10000}"' \
'# Laravel optimize (hata olsa da düşürme)' \
'php artisan optimize || true' \
'' \
'# PORT değişkenini Nginx conf’a bas' \
'envsubst '\''$PORT'\'' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf' \
'' \
'# PHP-FPM ve Nginx' \
'php-fpm -D' \
'nginx -g "daemon off;"' \
> /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

EXPOSE 10000

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s \
  CMD curl -fsS http://127.0.0.1:${PORT}/healthz || exit 1

CMD ["/usr/local/bin/start.sh"]
