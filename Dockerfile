# ---------- Stage 1: Frontend ----------
FROM node:18 AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build


# ---------- Stage 2: Backend ----------
FROM php:8.2-fpm
WORKDIR /var/www

# Sistem bağımlılıkları
RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libzip-dev libonig-dev zip vim nano \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring zip bcmath

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Uygulama dosyaları
COPY . .

# Frontend build çıktısını kopyala
COPY --from=frontend /app/public/build ./public/build

# PHP bağımlılıkları
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Laravel önbellek işlemleri
RUN php artisan key:generate --force || true
RUN php artisan config:clear || true
RUN php artisan cache:clear || true
RUN php artisan route:clear || true
RUN php artisan view:clear || true

# Laravel migration (DB bağlantısı varsa çalışır)
RUN php artisan migrate --force || true
RUN php artisan optimize

EXPOSE 10000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
