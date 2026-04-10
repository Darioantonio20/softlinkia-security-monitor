#!/bin/sh

# Configurar puerto dinámico
PORT=${PORT:-80}
echo "Servicio Softlinkia en puerto: $PORT"
sed -i "s/listen 80;/listen $PORT;/g" /etc/nginx/sites-available/default
sed -i "s/listen [0-9]\{2,5\};/listen $PORT;/g" /etc/nginx/sites-available/default

# Esperar a la DB
echo "🔍 Iniciando diagnóstico de conexión..."
echo "Host detectado: ${DB_HOST:-'No definido'}"
echo "Puerto detectado: ${DB_PORT:-'3306'}"
echo "Usuario detectado: ${DB_USERNAME:-'No definido'}"

MAX_TRIES=30
COUNT=0
while [ $COUNT -lt $MAX_TRIES ]; do
    # Intentar conexión con diagnóstico
    php -r "
    \$host = getenv('DB_HOST');
    \$port = getenv('DB_PORT') ?: '3306';
    \$db   = getenv('DB_DATABASE');
    \$user = getenv('DB_USERNAME');
    \$pass = getenv('DB_PASSWORD');
    
    try {
        \$dsn = \"mysql:host=\$host;port=\$port;dbname=\$db\";
        new PDO(\$dsn, \$user, \$pass, [PDO::ATTR_TIMEOUT => 2]);
        exit(0);
    } catch (Exception \$e) {
        fwrite(STDERR, \"PDO Error: \" . \$e->getMessage() . PHP_EOL);
        exit(1);
    }
    "
    if [ $? -eq 0 ]; then
        echo "✅ ¡Base de datos lista!"
        break
    fi
    echo "⏳ Base de datos no disponible todavía... reintentando ($COUNT/$MAX_TRIES)"
    sleep 3
    COUNT=$((COUNT+1))
done

# Validar APP_KEY
if [ -z "$APP_KEY" ]; then
    echo "APP_KEY no detectada. Generando una temporal..."
    php artisan key:generate --force
fi

# Procesos de despliegue
echo "Sincronizando esquema de base de datos..."
php artisan migrate --force --seed || echo "Aviso: Error en la migración, continuando de todas formas..."

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
