FROM php:8.2-fpm

# Instalar dependencias del sistema, NGINX y NODEJS
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    nginx \
    gnupg

# Instalar Node.js para compilar assets
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && apt-get install -y nodejs

# Limpiar cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Obtener Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Directorio de trabajo
WORKDIR /var/www

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias de PHP
RUN composer install --no-interaction --optimize-autoloader --no-dev

# INSTALAR Y COMPILAR ASSETS (Vite)
RUN npm install
RUN npm run build

# Configuración de Nginx
COPY docker/nginx/conf.d/app.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Permisos
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod +x /var/www/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/var/www/docker-entrypoint.sh"]
