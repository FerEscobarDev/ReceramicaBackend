#!/usr/bin/env bash
set -e

echo "🚀 Iniciando aplicación Laravel Receramica..."

# Función para esperar a que la base de datos esté disponible
wait_for_db() {
    echo "⏳ Esperando conexión a MySQL..."
    max_attempts=30
    attempt=0
    
    until php artisan db:show 2>/dev/null || [ $attempt -eq $max_attempts ]; do
        attempt=$((attempt + 1))
        echo "Intento $attempt/$max_attempts - Base de datos no disponible, reintentando en 5s..."
        sleep 5
    done
    
    if [ $attempt -eq $max_attempts ]; then
        echo "❌ Error: No se pudo conectar a la base de datos después de $max_attempts intentos"
        echo "⚠️  Continuando de todas formas... (la aplicación puede fallar)"
    else
        echo "✅ Conexión a base de datos establecida"
    fi
}

# Esperar a la base de datos
wait_for_db

# Crear symlink de storage si no existe
if [ ! -L /var/www/html/public/storage ]; then
    echo "🔗 Creando storage:link..."
    php artisan storage:link || echo "⚠️  storage:link falló (puede que ya exista)"
fi

# Verificar que el directorio de imágenes existe
if [ ! -d /var/www/html/storage/app/public/creaciones_images ]; then
    echo "📁 Creando directorio creaciones_images..."
    mkdir -p /var/www/html/storage/app/public/creaciones_images
fi

# Optimizaciones de Laravel para producción
echo "⚡ Optimizando Laravel..."
php artisan config:cache || echo "⚠️  config:cache falló"
php artisan route:cache || echo "⚠️  route:cache falló"
php artisan view:cache || echo "⚠️  view:cache falló"

# Asegurar permisos correctos
echo "🔒 Configurando permisos..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Si existe el volumen montado, asegurar permisos
if [ -d /var/www/html/storage/app/public/creaciones_images ]; then
    chown -R www-data:www-data /var/www/html/storage/app/public/creaciones_images
    chmod -R 775 /var/www/html/storage/app/public/creaciones_images
fi

echo "✨ Aplicación lista!"
echo "📊 Información del sistema:"
echo "   - PHP Version: $(php -v | head -n 1)"
echo "   - Laravel Version: $(php artisan --version)"
echo "   - Environment: ${APP_ENV:-production}"
echo "   - Debug Mode: ${APP_DEBUG:-false}"

# Iniciar servicios con supervisord
exec "$@"
