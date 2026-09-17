FROM php:8.2-fpm

# 1. تثبيت حزم النظام السيرفر والـ MySQL
RUN apt-get update && apt-get install -y \
    nginx \
    default-mysql-server \
    supervisor \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl

# 2. تثبيت إضافات PHP المباشرة للـ MySQL
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# 3. تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. تحديد مجلد العمل ونسخ الملفات
WORKDIR /var/www
COPY . /var/www

# 5. تثبيت حزم Composer للإنتاج
RUN composer install --no-dev --optimize-autoloader

# 6. نسخ إعدادات Nginx و Supervisor
COPY nginx.conf /etc/nginx/sites-available/default
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 7. إعطاء الصلاحيات المباشرة لمجلدات التخزين
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# 8. سكربت التشغيل المبدئي للـ Database والـ Migrations
RUN service mysql start && \
    mysql -e "CREATE DATABASE IF NOT EXISTS laravel_db;" && \
    mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'root_password';"

EXPOSE 80

CMD ["/usr/bin/supervisord"]