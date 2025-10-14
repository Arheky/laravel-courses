# ---------- STAGE 1: FRONTEND (Vite Build - Node 20) ----------
FROM node:20 AS frontend
WORKDIR /app

# 1️⃣ Paket dosyalarını kopyala
COPY package*.json ./

# 2️⃣ Bağımlılıkları kur (peer-dep hatalarını yok say)
RUN npm install --legacy-peer-deps

# 3️⃣ Kaynak kodu kopyala
COPY . .

# 4️⃣ Frontend'i derle (Vite)
RUN npm run build


# ---------- STAGE 2: BACKEND (Laravel + PHP + Composer) ----------
FROM php:8.3-fpm AS backend
WORKDIR /var/www

# 1️⃣ Gerekli sistem paketleri
RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libzip-dev libonig-dev zip \
    && docker-php-ext-install pdo_pgsql pgsql mbstring zip bcmath

# 2️⃣ Composer'ı yükle
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# 3️⃣ Laravel kaynaklarını kopyala
COPY . .

# 4️⃣ Frontend build çıktısını public'e taşı
COPY --from=frontend /app/public/build ./public/build

# 5️⃣ PHP bağımlılıklarını kur
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 6️⃣ Laravel cache ve optimize işlemlerini runtime'da çalıştırmak için build'te sadece temizleme yapıyoruz
RUN php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    chmod -R 775 storage bootstrap/cache

# 7️⃣ Render HTTP portunu aç
EXPOSE 10000

# 8️⃣ Laravel uygulamasını başlat (runtime aşamasında APP_KEY kontrolü + optimize)
CMD if [ -z "$APP_KEY" ]; then \
      echo "⚠️  APP_KEY bulunamadı, yeni anahtar oluşturuluyor..."; \
      php artisan key:generate --force; \
    fi && \
    echo "🚀 Laravel optimize ediliyor..." && \
    php artisan config:clear && \
    php artisan cache:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    php artisan optimize && \
    php artisan serve --host=0.0.0.0 --port=10000
