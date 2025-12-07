# Guía de Validación de Configuración PHP

Esta guía explica cómo validar que la configuración de PHP esté correctamente aplicada para soportar uploads de hasta 500MB.

## Método 1: Script de Validación Web (php-config.php)

### Acceso
Después del despliegue, accede a:
```
https://ricardo-admin.receramica.com/php-config.php
```

### Qué muestra
- Tabla comparativa de valores actuales vs requeridos
- Estado visual (✅ OK / ❌ FAIL) para cada directiva:
  - `upload_max_filesize` → 500M
  - `post_max_size` → 500M
  - `memory_limit` → 512M
  - `max_file_uploads` → 50
  - `max_execution_time` → 300 segundos
  - `max_input_time` → 300 segundos
- Información sobre archivos php.ini cargados
- Versión de PHP y SAPI

### Seguridad
⚠️ **IMPORTANTE**: Este archivo solo funciona si `APP_DEBUG=true` en producción.

**Después de validar, DEBES eliminarlo**:
```bash
rm public/php-config.php
```

## Método 2: Validación vía SSH en el Contenedor

### Conectarse al contenedor
```bash
docker exec -it receramica-laravel-app bash
```

### Verificar configuración PHP
```bash
# Ver todas las directivas de upload
php -i | grep -E 'upload_max_filesize|post_max_size|memory_limit|max_file_uploads|max_execution_time|max_input_time'

# Ver archivo php.ini cargado
php -i | grep "Loaded Configuration File"

# Ver directorio de configuración adicional
php -i | grep "Scan this dir"

# Listar archivos .ini cargados
ls -la /usr/local/etc/php/conf.d/
```

### Verificar configuración de nginx
```bash
# Ver client_max_body_size
nginx -T | grep client_max_body_size

# Verificar sintaxis de nginx
nginx -t
```

### Resultados esperados
```
upload_max_filesize => 500M
post_max_size => 500M
memory_limit => 512M
max_file_uploads => 50
max_execution_time => 300
max_input_time => 300

client_max_body_size 500M;
```

## Método 3: Crear endpoint phpinfo() temporal

### Crear archivo temporal
En `routes/web.php`, agregar temporalmente:

```php
Route::get('/phpinfo-temp', function() {
    if (config('app.debug') === false) {
        abort(403);
    }
    phpinfo();
})->middleware('auth');
```

### Acceso
```
https://ricardo-admin.receramica.com/phpinfo-temp
```

Buscar las secciones:
- **Core** → Configuración de PHP
- **fileinfo** → Directivas de upload

⚠️ **Eliminar después de validar**

## Método 4: Test de Upload Real con curl

### Crear archivo de prueba grande
```bash
# Crear archivo de 100MB de prueba
dd if=/dev/zero of=test-100mb.bin bs=1M count=100
```

### Test de upload
```bash
curl -X POST https://ricardo-admin.receramica.com/api/uploadImages \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -F "images[]=@test-100mb.bin" \
  -F "images[]=@test-100mb.bin" \
  -F "images[]=@test-100mb.bin" \
  -v
```

### Respuestas esperadas

**✅ Éxito** (Status 200):
```json
{
  "success": true,
  "message": "Imágenes subidas correctamente"
}
```

**❌ Error 413** (aún hay límite):
```
< HTTP/1.1 413 Request Entity Too Large
```

**❌ Error 504** (timeout):
```
< HTTP/1.1 504 Gateway Timeout
```
Aumentar `max_execution_time` y `max_input_time`

## Método 5: Revisar Logs de Nginx y PHP-FPM

### Logs en tiempo real
```bash
# Logs de nginx
docker exec -it receramica-laravel-app tail -f /var/log/nginx/error.log

# Logs de PHP-FPM
docker exec -it receramica-laravel-app tail -f /app/storage/logs/laravel.log
```

### Errores comunes a buscar

**Error 413 en nginx:**
```
client intended to send too large body
```
→ Revisar `client_max_body_size` en nginx.conf

**Error PHP upload:**
```
Maximum upload size exceeded
```
→ Revisar `upload_max_filesize` y `post_max_size`

**Error de memoria:**
```
Allowed memory size exhausted
```
→ Aumentar `memory_limit`

**Error de timeout:**
```
Maximum execution time exceeded
```
→ Aumentar `max_execution_time`

## Checklist de Validación Completa

Después del despliegue con Docker Compose, verificar:

- [ ] Contenedor `receramica-laravel-app` está corriendo
- [ ] Acceder a `https://ricardo-admin.receramica.com/php-config.php`
- [ ] Todas las directivas muestran ✅ OK
- [ ] Archivos php.ini cargados muestran `/usr/local/etc/php/conf.d/uploads.ini`
- [ ] Test de upload con archivo real de 100-200MB exitoso
- [ ] Eliminar `public/php-config.php` después de validar
- [ ] Logs de nginx y PHP-FPM sin errores 413/504

## Configuración Aplicada

### Archivo: .user.ini
```ini
upload_max_filesize = 500M
post_max_size = 500M
memory_limit = 512M
max_file_uploads = 50
max_execution_time = 300
max_input_time = 300
```

### Archivo: nginx.conf (línea 26)
```nginx
client_max_body_size 500M;
```

### Dockerfile.prod (línea 34)
```dockerfile
COPY .user.ini /usr/local/etc/php/conf.d/uploads.ini
```

Esta configuración se copia al directorio `/usr/local/etc/php/conf.d/` que PHP-FPM lee automáticamente al iniciar.

## Troubleshooting

### Si php-config.php muestra valores incorrectos

1. Verificar que el Dockerfile copió correctamente:
   ```bash
   docker exec -it receramica-laravel-app cat /usr/local/etc/php/conf.d/uploads.ini
   ```

2. Reiniciar PHP-FPM:
   ```bash
   docker exec -it receramica-laravel-app supervisorctl restart php-fpm
   ```

3. Rebuild del contenedor:
   ```bash
   docker-compose -f docker-compose.prod.yml build --no-cache
   docker-compose -f docker-compose.prod.yml up -d
   ```

### Si persiste error 413 después de validar PHP

El problema puede estar en **Traefik** (reverse proxy de Dokploy):

1. Verificar middleware en Dokploy UI:
   - Ir a Application → Traefik
   - Buscar middleware `upload-limit`
   - Confirmar: `maxRequestBodyBytes: 524288000` (500MB)
   - Confirmar: `memRequestBodyBytes: 524288000` (500MB)

2. Aplicar middleware al servicio:
   - En Labels de Docker Compose
   - O en configuración de Dokploy Application

### Si hay error de timeout (504)

Aumentar timeouts en nginx.conf:
```nginx
fastcgi_read_timeout 300;
fastcgi_send_timeout 300;
```

Y en Traefik middleware (Dokploy):
```yaml
responseHeaderTimeout: 300s
```

## Próximos Pasos

1. ✅ Validar configuración con php-config.php
2. ✅ Test de upload real con múltiples imágenes
3. ✅ Revisar logs para errores
4. ⚠️ Eliminar php-config.php
5. ✅ Commit y push de cambios a producción
