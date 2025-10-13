# ---------- STAGE 1: Frontend Build ----------
FROM node:20 AS frontend
ENV NODE_ENV=production
ENV NODE_OPTIONS="--max-old-space-size=4096"

WORKDIR /app

COPY package*.json ./
RUN npm install --legacy-peer-deps

COPY . .
RUN npx vite build

# ---------- STAGE 2: Backend (Laravel + PHP + Composer) ----------
FROM php:8.2-fpm AS backend
WORKDIR /var/www

# 1️⃣ Sistem paketleri
RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libzip-dev libonig-dev zip \
    && docker-php-ext-install pdo pdo_mysql mbstring zip bcmath

# 2️⃣ Composer yükle
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 3️⃣ Laravel dosyalarını kopyala
COPY . .

# 4️⃣ Frontend build çıktısını kopyala
COPY --from=frontend /app/public/build ./public/build

# 5️⃣ PHP bağımlılıklarını yükle
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 6️⃣ Laravel cache temizliği ve izinler
RUN php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    chmod -R 775 storage bootstrap/cache

# 7️⃣ Render için port aç
EXPOSE 10000

# 8️⃣ Laravel uygulamasını başlat
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
