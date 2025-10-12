# -----------------------------
# LaravelCourses - Render Deploy (PHP 8.2 + Node + PostgreSQL)
# -----------------------------

# 1️⃣ Base Image
FROM php:8.2-fpm

# 2️⃣ System dependencies (PostgreSQL + Node için)
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# 3️⃣ Node.js yükle (Vite build için)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# 4️⃣ Composer yükle
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# 5️⃣ Çalışma dizini
WORKDIR /var/www/html

# 6️⃣ Proje dosyalarını kopyala
COPY . .

# 7️⃣ Backend ve Frontend bağımlılıklarını yükle
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# 8️⃣ Laravel izinleri
RUN chmod -R 775 storage bootstrap/cache

# 9️⃣ ENV değişkenleri
ENV APP_ENV=production
ENV PORT=8000

# 🔟 Uygulamayı başlat (migrate dahil)
EXPOSE 8000
CMD php artisan migrate --force && php artisan optimize:clear && php artisan config:cache && php artisan serve --host=0.0.0.0 --port=8000
