# ---------- STAGE 1: Frontend Build ----------
FROM node:18 AS frontend
WORKDIR /app

# 1️⃣ Paket dosyalarını yükle
COPY package*.json ./
RUN npm install

# 2️⃣ Tüm kaynakları kopyala
COPY . .

# 3️⃣ Vite ile build al (çıktı: public/build)
RUN npm run build


# ---------- STAGE 2: Backend (Laravel + PHP + Composer) ----------
FROM php:8.2-fpm AS backend
WORKDIR /var/www

# 1️⃣ Gerekli sistem paketleri
RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libzip-dev libonig-dev zip \
    && docker-php-ext-install pdo pdo_mysql mbstring zip bcmath

# 2️⃣ Composer’ı yükle
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 3️⃣ Uygulama dosyalarını kopyala
COPY . .

# 4️⃣ Frontend build çıktısını backend’e kopyala
COPY --from=frontend /app/public/build ./public/build

# 5️⃣ PHP bağımlılıklarını yükle
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 6️⃣ Laravel cache’lerini temizle
RUN php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear

# 7️⃣ Render HTTP erişimi için port aç
EXPOSE 10000

# 8️⃣ Laravel uygulamasını başlat
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
