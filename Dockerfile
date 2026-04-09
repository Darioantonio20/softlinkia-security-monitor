FROM php:8.2-fpm

# Instalar dependencias del sistema y NGINX
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    nginx

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

# Copiar configuración de Nginx para Docker
COPY docker/nginx/conf.d/app.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Instalar dependencias con permisos adecuados
RUN composer install --no-interaction --optimize-autoloader --no-dev
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Dar permisos de ejecución al script de entrada
RUN chmod +x /var/www/docker-entrypoint.sh

# Exponer el puerto que Railway espera (80 por defecto en Nginx)
EXPOSE 80

# Usar el script de entrada
ENTRYPOINT ["/var/www/docker-entrypoint.sh"]
