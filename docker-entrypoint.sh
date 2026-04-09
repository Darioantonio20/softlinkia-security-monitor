#!/bin/sh

# Usar el puerto de Railway o el 80 por defecto
PORT=${PORT:-80}
echo "Configurando Nginx para el puerto: $PORT"
sed -i "s/listen 80;/listen $PORT;/g" /etc/nginx/sites-available/default

# Reemplazar el puerto en Nginx si es necesario (limpieza extra)
sed -i "s/listen [0-9]\{2,5\};/listen $PORT;/g" /etc/nginx/sites-available/default

# Bucle de espera para la Base de Datos
echo "Esperando a que la base de datos responda..."
MAX_TRIES=30
COUNT=0
while [ $COUNT -lt $MAX_TRIES ]; do
    # Intentar una simple consulta de PHP para verificar la conexión
    php -r "
    try {
        new PDO('mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
        exit(0);
    } catch (Exception \$e) {
        exit(1);
    }
    "
    if [ $? -eq 0 ]; then
        echo "¡Base de datos detectada!"
        break
    fi
    echo "Base de datos no disponible... reintentando en 2s ($COUNT/$MAX_TRIES)"
    sleep 2
    COUNT=$((COUNT+1))
done

# Ejecutar procesos obligatorios
echo "Ejecutando migraciones..."
php artisan migrate --force --seed

echo "Optimizando caché..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Arrancar servicios
echo "Iniciando PHP-FPM..."
php-fpm -D

echo "Iniciando Nginx puerto $PORT..."
nginx -g "daemon off;"
