# ---------- STAGE 1: FRONTEND (Vite build) ----------
FROM node:20 AS frontend
WORKDIR /app

# Daha deterministik: npm ci
COPY package*.json ./
RUN npm install --legacy-peer-deps

COPY . .
RUN npm run build

# Vite bazı sürümlerde manifest'i .vite klasörüne koyabiliyor
RUN if [ -f public/build/.vite/manifest.json ] && [ ! -f public/build/manifest.json ]; then \
      cp public/build/.vite/manifest.json public/build/manifest.json; \
    fi


# ---------- STAGE 2: BACKEND (php-fpm + nginx) ----------
FROM php:8.3-fpm

WORKDIR /var/www

# Sistem paketleri + nginx + curl (healthcheck için)
RUN apt-get update && apt-get install -y \
    nginx curl unzip git libpq-dev libzip-dev zip \
 && docker-php-ext-install pdo_pgsql pgsql mbstring zip bcmath opcache \
 && rm -rf /var/lib/apt/lists/*

# OPcache (prod için önerilen)
RUN { \
      echo "opcache.enable=1"; \
      echo "opcache.validate_timestamps=0"; \
      echo "opcache.memory_consumption=128"; \
      echo "opcache.interned_strings_buffer=16"; \
      echo "opcache.max_accelerated_files=10000"; \
      echo "opcache.save_comments=1"; \
      echo "opcache.fast_shutdown=1"; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Uygulama kodu + build edilmiş asset'ler
COPY . .
COPY --from=frontend /app/public/build ./public/build

# Composer prod install (APP_KEY vs. runtime’da verileceği için optimize burada, cache'ler runtime’da)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Nginx loglarını stdout/stderr'e bağla (Render loglarında görünsün)
RUN ln -sf /dev/stdout /var/log/nginx/access.log \
 && ln -sf /dev/stderr /var/log/nginx/error.log

# Basit bir Nginx conf template (PORT'u runtime'da envsubst ile dolduracağız)
RUN mkdir -p /etc/nginx/conf.d
RUN bash -lc 'cat > /etc/nginx/conf.d/default.conf.template << "EOF"
server {
    listen       ${PORT} default_server;
    server_name  _;
    root   /var/www/public;
    index  index.php index.html;

    # Health check
    location = /healthz {
        access_log off;
        add_header Content-Type text/plain;
        return 200 "ok";
    }

    # Statik dosyalar
    location ~* \.(?:css|js|jpg|jpeg|gif|png|ico|svg|woff|woff2|ttf|map)$ {
        expires 30d;
        access_log off;
        try_files $uri =404;
    }

    # Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include         fastcgi_params;
        fastcgi_pass    127.0.0.1:9000;
        fastcgi_param   SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param   DOCUMENT_ROOT $realpath_root;
        fastcgi_read_timeout 60s;
    }

    client_max_body_size 10m;
    sendfile on;
    # gzip ayarlarını istersen ekleyebilirsin
}
EOF'

# Start script: Nginx conf'u PORT ile üret, Laravel’i optimize et, servisleri foreground'da başlat
RUN bash -lc 'cat > /usr/local/bin/start.sh << "EOF"
#!/usr/bin/env bash
set -euo pipefail

# Nginx conf - PORT env'ini yerleştir
envsubst "\$PORT" < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf

# Laravel hazırlık
php artisan storage:link || true

# APP_KEY yoksa oluştur
if [ -z "${APP_KEY:-}" ]; then
  php artisan key:generate --force
fi

# Prod cache'leri
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# İzinler (konteyner reboot’larında sorun çıkmaması için toleranslı)
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rwX storage bootstrap/cache || true

# php-fpm (foreground) + nginx (foreground)
php-fpm -F &

exec nginx -g "daemon off;"
EOF
chmod +x /usr/local/bin/start.sh'

# Healthcheck (Host header optional; istersen APP_URL_HOSTNAME ile de gönderebilirsin)
HEALTHCHECK --interval=30s --timeout=5s --start-period=20s \
  CMD curl -fsS "http://127.0.0.1:${PORT}/healthz" || exit 1

# Belirtmek şart değil ama dokümantatif
EXPOSE 8080

CMD ["/usr/local/bin/start.sh"]
