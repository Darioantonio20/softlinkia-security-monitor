#!/bin/sh

# Usar el puerto de Railway o el 80 por defecto
PORT=${PORT:-80}
echo "Configurando Nginx para escuchar en el puerto: $PORT"

# Reemplazar el puerto 80 en la configuración de Nginx por el puerto de Railway
sed -i "s/listen 80;/listen $PORT;/g" /etc/nginx/sites-available/default

# Esperar a la DB
echo "Esperando a la base de datos..."
sleep 5

# Migraciones y Cache
php artisan migrate --force --seed
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Iniciar PHP-FPM en segundo plano
echo "Iniciando PHP-FPM..."
php-fpm -D

# Iniciar Nginx en PRIMER plano (para que el contenedor no se apague)
echo "Iniciando Nginx en el puerto $PORT..."
nginx -g "daemon off;"
