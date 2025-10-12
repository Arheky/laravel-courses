# -----------------------------
# 🚀 LaravelCourses - Render Deploy (PHP 8.2 + Node 20 + PostgreSQL)
# Tam Otomatik: Migrate + Optimize + Key Generate + Storage Link
# -----------------------------

FROM php:8.2-fpm

# 1️⃣ Sistem bağımlılıkları (PostgreSQL + Node + PHP Extensions)
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# 2️⃣ Node.js kurulumu (Vite için)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# 3️⃣ Composer kurulumu
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# 4️⃣ Çalışma dizini
WORKDIR /var/www/html

# 5️⃣ Proje dosyalarını kopyala
COPY . .

# 6️⃣ Laravel ve Frontend bağımlılıklarını yükle + Build işlemi
RUN composer install --no-dev --optimize-autoloader \
    && npm install --legacy-peer-deps \
    && npm run build

# 7️⃣ Laravel dosya izinleri
RUN chmod -R 775 storage bootstrap/cache || true

# 8️⃣ Ortam değişkenleri
ENV APP_ENV=production
ENV PORT=8000

# 9️⃣ Laravel başlangıç komutları (tam otomatik)
EXPOSE 8000
CMD php artisan key:generate --force \
    && php artisan storage:link \
    && php artisan migrate --force \
    && php artisan optimize:clear \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan serve --host=0.0.0.0 --port=8000
