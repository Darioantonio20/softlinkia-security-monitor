#!/bin/sh

# Configurar puerto dinámico
PORT=${PORT:-80}
echo "Servicio Softlinkia en puerto: $PORT"
sed -i "s/listen 80;/listen $PORT;/g" /etc/nginx/sites-available/default
sed -i "s/listen [0-9]\{2,5\};/listen $PORT;/g" /etc/nginx/sites-available/default

# Esperar a la DB
echo "Validando conexión a base de datos..."
MAX_TRIES=30
COUNT=0
while [ $COUNT -lt $MAX_TRIES ]; do
    php -r "
    try {
        new PDO('mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
        exit(0);
    } catch (Exception \$e) {
        exit(1);
    }
    "
    if [ $? -eq 0 ]; then
        echo "¡Base de datos lista!"
        break
    fi
    echo "Base de datos no disponible... reintentando ($COUNT/$MAX_TRIES)"
    sleep 2
    COUNT=$((COUNT+1))
done

# Procesos de despliegue
echo "Sincronizando esquema de base de datos..."
php artisan migrate --force --seed

# Limpiar cachés para evitar errores 500
echo "Limpiando y preparando carpetas de sistema..."
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache bootstrap/cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

php artisan config:clear
php artisan route:clear
php artisan view:clear

# Iniciar procesos
echo "Lanzando PHP-FPM..."
php-fpm -D

echo "Lanzando Nginx..."
nginx -g "daemon off;"
