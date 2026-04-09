#!/bin/sh

# Esperar un par de segundos para que la red de Railway se estabilice
echo "Esperando a la base de datos..."
sleep 5

# Ejecutar migraciones y seeders
echo "Ejecutando migraciones..."
php artisan migrate --force --seed

# Optimizar caché
echo "Optimizando configuración..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Iniciar Nginx en segundo plano
echo "Iniciando Nginx..."
nginx -g "daemon on;"

# Iniciar PHP-FPM
echo "Iniciando PHP-FPM..."
php-fpm
