# Guía de Despliegue en Dokploy

Esta guía describe cómo desplegar la aplicación Laravel Receramica en Dokploy usando Docker Compose.

## Requisitos Previos

- Servidor con Dokploy v0.26.0 o superior instalado
- Base de datos MySQL ya configurada en Dokploy
- Acceso al repositorio Git del proyecto
- Dominio configurado: `https://ricardo-admin.receramica.com`

## Configuración en Dokploy

### 1. Crear Nuevo Servicio

1. En Dokploy, navega a tu proyecto
2. Crea un nuevo servicio de tipo **"Docker Compose"**
3. Conecta tu repositorio Git
4. Selecciona la rama `develop`

### 2. Configurar Compose Path

En la configuración del servicio:
- **Compose Path**: `./docker-compose.prod.yml`

### 3. Configurar Variables de Entorno

En la sección de **Environment Variables**, configura las siguientes variables:

```env
APP_NAME=Receramica
APP_ENV=production
APP_KEY=base64:TU_APP_KEY_AQUI
APP_DEBUG=false
APP_URL=https://ricardo-admin.receramica.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=receramica-receramica-db-zs9qhl
DB_PORT=3306
DB_DATABASE=u373237400_receramica
DB_USERNAME=u373237400_ricardo
DB_PASSWORD=7m$C&7Gouz

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

> **⚠️ IMPORTANTE**: Genera una nueva `APP_KEY` ejecutando:
> ```bash
> php artisan key:generate --show
> ```
> Y copia el valor generado a la variable `APP_KEY` en Dokploy.

### 4. Configurar Volumen

En la sección **Volumes** (pestaña Advanced):

- **Mount Type**: `BIND`
- **Host Path**: `/var/lib/docker/creaciones_images`
- **Mount Path**: `/var/www/html/storage/app/public/creaciones_images`

### 5. Configurar Dominio

En la sección **Domains**:
1. Añade el dominio: `ricardo-admin.receramica.com`
2. Habilita **SSL/TLS** (Dokploy gestionará automáticamente Let's Encrypt)

### 6. Habilitar Isolated Deployment (Opcional)

En la pestaña **Advanced**:
- Habilita **"Enable Isolated Deployment"** para crear una red dedicada

## Despliegue

1. Haz clic en **"Deploy"**
2. Dokploy comenzará a:
   - Clonar el repositorio
   - Construir la imagen Docker (puede tardar varios minutos)
   - Crear el contenedor
   - Ejecutar el entrypoint script

3. Monitorea los logs en tiempo real desde la pestaña **Logs**

## Verificación Post-Despliegue

### 1. Verificar que el contenedor está corriendo

En los logs deberías ver:
```
✨ Aplicación lista!
📊 Información del sistema:
   - PHP Version: ...
   - Laravel Version: ...
   - Environment: production
   - Debug Mode: false
```

### 2. Verificar acceso a la aplicación

Accede a `https://ricardo-admin.receramica.com` y verifica:
- La página carga correctamente
- Los assets (CSS, JS) se cargan
- El login funciona

### 3. Probar upload de archivos

1. Inicia sesión en la aplicación
2. Navega a la sección de productos
3. Intenta subir una imagen de ~8MB
4. Verifica que se procesa correctamente

## Troubleshooting

### Error: "Connection refused" a la base de datos

**Causa**: El contenedor no puede conectarse a MySQL.

**Solución**:
1. Verifica que el host de BD es correcto: `receramica-receramica-db-zs9qhl`
2. Confirma que las credenciales son correctas
3. Asegúrate de que ambos servicios están en la misma red de Dokploy

### Error: "413 Request Entity Too Large"

**Causa**: Límites de carga no configurados correctamente.

**Solución**:
1. Verifica que el contenedor se construyó con el `Dockerfile.prod` correcto
2. Revisa los logs para confirmar que Nginx y PHP-FPM iniciaron correctamente
3. Reconstruye la imagen: `docker-compose -f docker-compose.prod.yml build --no-cache`

### Error: "storage/logs/laravel.log could not be opened"

**Causa**: Permisos incorrectos en el directorio storage.

**Solución**:
El entrypoint script debería configurar los permisos automáticamente. Si persiste:
```bash
docker exec receramica-app chown -R www-data:www-data /var/www/html/storage
docker exec receramica-app chmod -R 775 /var/www/html/storage
```

### Las imágenes subidas desaparecen después de reiniciar

**Causa**: El volumen BIND no está configurado correctamente.

**Solución**:
1. Verifica que el volumen está configurado en Dokploy
2. Confirma que el directorio `/var/lib/docker/creaciones_images` existe en el host
3. Verifica los permisos del directorio en el host

## Comandos Útiles

### Ver logs del contenedor
```bash
docker logs -f receramica-app
```

### Acceder al contenedor
```bash
docker exec -it receramica-app bash
```

### Ejecutar comandos de Artisan
```bash
docker exec receramica-app php artisan <comando>
```

### Limpiar caché de Laravel
```bash
docker exec receramica-app php artisan cache:clear
docker exec receramica-app php artisan config:clear
docker exec receramica-app php artisan route:clear
docker exec receramica-app php artisan view:clear
```

### Reconstruir la imagen
```bash
docker-compose -f docker-compose.prod.yml build --no-cache
docker-compose -f docker-compose.prod.yml up -d
```

## Actualizaciones

Para desplegar una nueva versión:

1. Haz push de tus cambios a la rama `develop`
2. En Dokploy, haz clic en **"Redeploy"**
3. Dokploy reconstruirá la imagen y reiniciará el contenedor

## Configuración de Límites

La aplicación está configurada con los siguientes límites:

- **Tamaño máximo de archivo individual**: 10MB
- **Tamaño máximo del body total**: 100MB
- **Número máximo de archivos simultáneos**: 30
- **Tiempo máximo de ejecución**: 300 segundos (5 minutos)
- **Memoria límite PHP**: 256MB

Estos valores están configurados en:
- `docker/8.3/php.ini` (PHP)
- `docker/8.3/nginx.conf` (Nginx)

## Soporte

Para problemas o preguntas:
1. Revisa los logs del contenedor
2. Verifica la configuración de variables de entorno
3. Confirma que la base de datos es accesible
