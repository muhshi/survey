# Image dasar FrankenPHP (PHP 8.3 + Caddy)
FROM dunglas/frankenphp:php8.4

ENV SERVER_NAME=":80"
# Lokasi proyek di dalam container
WORKDIR /app

# Sistem deps & ekstensi PHP yang umum dipakai Laravel/Filament
RUN apt-get update && apt-get install -y \
    curl \
    gnupg \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) intl gd zip pdo_mysql \
    && docker-php-ext-enable intl gd zip pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Node.js (untuk npm build)
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer (buat install deps dari dalam container)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Salin manifest dependensi terlebih dahulu agar memanfaatkan cache layer Docker
COPY composer.json composer.lock* ./
RUN composer install --no-interaction --no-dev --no-scripts --no-autoloader --prefer-dist

COPY package.json package-lock.json* ./
RUN npm install

# Salin seluruh kode aplikasi
COPY . /app

# Selesaikan autoloader composer & build aset frontend
RUN composer dump-autoload --optimize --no-dev \
    && npm run build

# Copy konfigurasi Caddy/FrankenPHP
COPY Caddyfile /etc/caddy/Caddyfile

EXPOSE 80
EXPOSE 443
EXPOSE 443/udp

# (Opsional tapi berguna) set permission direktori Laravel
# Catatan: karena kamu bind-mount ., permission tetap mengikuti host.
# Perintah ini tetap aman dijalankan.
RUN mkdir -p /app/storage /app/bootstrap/cache \
    && chown -R www-data:www-data /app/storage /app/bootstrap/cache
