#!/bin/bash
set -e

echo "🚀 Iniciando aplicación Laravel..."

# Crear symlink de storage (importante hacerlo en runtime, no en build)
echo "📁 Creando symlink de storage..."
php artisan storage:link || true

# Verificar permisos
echo "🔐 Ajustando permisos..."
chown -R www-data:www-data /app/storage /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache

# Verificar conexión a base de datos
echo "🗄️ Verificando conexión a base de datos..."
php artisan migrate:status || echo "⚠️ No se pudo verificar migraciones (normal en primer inicio)"

echo "✅ Inicialización completa. Iniciando servicios..."

# Iniciar supervisord (PHP-FPM + nginx)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
