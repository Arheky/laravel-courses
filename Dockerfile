# ---------- STAGE 1: FRONTEND (Vite Build - Node 20) ----------
FROM node:20 AS frontend
WORKDIR /app

# 1️⃣ Daha hızlı ve kararlı bağımlılık yönetimi için pnpm kullanıyoruz
RUN npm install -g pnpm

# 2️⃣ Paket dosyalarını kopyala
COPY package*.json ./

# 3️⃣ Bağımlılıkları kur (peer-dep hatalarını yok say)
RUN pnpm install --no-frozen-lockfile

# 4️⃣ Kaynak kodu kopyala
COPY . .

# 5️⃣ Frontend'i derle (Vite)
RUN pnpm run build


# ---------- STAGE 2: BACKEND (Laravel + PHP + Composer) ----------
FROM php:8.3-fpm AS backend
WORKDIR /var/www

# 1️⃣ Gerekli sistem paketleri
RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libzip-dev libonig-dev zip \
    && docker-php-ext-install pdo_pgsql pgsql mbstring zip bcmath

# 2️⃣ Composer'ı yükle
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 3️⃣ Laravel kaynaklarını kopyala
COPY . .

# 4️⃣ Frontend build çıktısını public'e taşı
COPY --from=frontend /app/public/build ./public/build

# 5️⃣ PHP bağımlılıklarını kur
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 6️⃣ Laravel cache ve optimize işlemleri
RUN php artisan key:generate --force || true && \
    php artisan config:clear || true && \
    php artisan cache:clear || true && \
    php artisan route:clear || true && \
    php artisan view:clear || true && \
    php artisan optimize || true

# 7️⃣ Render HTTP portunu aç
EXPOSE 10000

# 8️⃣ Laravel uygulamasını başlat
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
