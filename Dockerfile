# ============================================================
# 🧱 STAGE 1 — Frontend Build (Node 20 + Vite)
# ============================================================
FROM node:20 AS frontend
WORKDIR /app

# 1️⃣ Paketleri yükle (peer dependency hatalarını önlemek için)
COPY package*.json ./
RUN npm install --legacy-peer-deps

# 2️⃣ Kaynak dosyaları kopyala
COPY . .

# 4️⃣ 💡 npx ile Vite build (doğrudan çalıştırma)
RUN npx vite build


# ============================================================
# 🐘 STAGE 2 — Backend (Laravel + PHP 8.2 + Composer)
# ============================================================
FROM php:8.2-fpm AS backend
WORKDIR /var/www

# 1️⃣ Sistem bağımlılıkları (PostgreSQL + Zip + Mbstring)
RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libzip-dev libonig-dev zip \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring zip bcmath

# 2️⃣ Composer’ı ekle
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 3️⃣ Uygulama dosyalarını kopyala
COPY . .

# 4️⃣ Frontend build çıktısını backend’e kopyala
COPY --from=frontend /app/public/build ./public/build

# 5️⃣ 💡 Otomatik manifest düzeltme (.vite → manifest.json)
RUN if [ -f public/build/.vite/manifest.json ]; then \
        cp public/build/.vite/manifest.json public/build/manifest.json; \
    fi

# 6️⃣ PHP bağımlılıklarını yükle
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 7️⃣ Laravel cache işlemleri ve izinler
RUN php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    php artisan optimize && \
    chmod -R 775 storage bootstrap/cache

# 8️⃣ Render portu
EXPOSE 10000

# 9️⃣ Laravel’i başlat
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
