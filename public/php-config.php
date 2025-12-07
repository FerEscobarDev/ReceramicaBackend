<?php
/**
 * Script de validación de configuración PHP
 * Acceder en: https://ricardo-admin.receramica.com/php-config.php
 * IMPORTANTE: Eliminar después de verificar en producción
 */

// Solo permitir en modo debug o desde IP específica
if (getenv('APP_ENV') === 'production' && getenv('APP_DEBUG') !== 'true') {
    http_response_code(403);
    die('Access denied');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Configuración PHP - Receramica</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 8px; max-width: 800px; margin: 0 auto; }
        h1 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        h2 { color: #666; margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background: #4CAF50; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .ok { color: #4CAF50; font-weight: bold; }
        .warning { color: #ff9800; font-weight: bold; }
        .error { color: #f44336; font-weight: bold; }
        .info { background: #e3f2fd; padding: 15px; border-left: 4px solid #2196F3; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Validación de Configuración PHP</h1>

        <div class="info">
            <strong>⚠️ Seguridad:</strong> Este archivo debe ser eliminado después de la verificación en producción.
        </div>

        <h2>📊 Configuración de Uploads</h2>
        <table>
            <tr>
                <th>Directiva</th>
                <th>Valor Actual</th>
                <th>Requerido</th>
                <th>Estado</th>
            </tr>
            <?php
            $config = [
                'upload_max_filesize' => ['current' => ini_get('upload_max_filesize'), 'required' => '500M'],
                'post_max_size' => ['current' => ini_get('post_max_size'), 'required' => '500M'],
                'memory_limit' => ['current' => ini_get('memory_limit'), 'required' => '512M'],
                'max_file_uploads' => ['current' => ini_get('max_file_uploads'), 'required' => '50'],
                'max_execution_time' => ['current' => ini_get('max_execution_time'), 'required' => '300'],
                'max_input_time' => ['current' => ini_get('max_input_time'), 'required' => '300'],
            ];

            function parseSize($size) {
                $unit = strtoupper(substr($size, -1));
                $value = (int) $size;

                switch($unit) {
                    case 'G': return $value * 1024 * 1024 * 1024;
                    case 'M': return $value * 1024 * 1024;
                    case 'K': return $value * 1024;
                    default: return $value;
                }
            }

            function checkValue($current, $required) {
                // Para valores numéricos sin unidad
                if (is_numeric($current) && is_numeric($required)) {
                    return (int)$current >= (int)$required;
                }

                // Para valores con unidades (M, G, etc.)
                $currentBytes = parseSize($current);
                $requiredBytes = parseSize($required);

                return $currentBytes >= $requiredBytes;
            }

            foreach ($config as $directive => $values) {
                $current = $values['current'];
                $required = $values['required'];
                $isOk = checkValue($current, $required);

                $statusClass = $isOk ? 'ok' : 'error';
                $statusText = $isOk ? '✅ OK' : '❌ FAIL';

                echo "<tr>";
                echo "<td><strong>{$directive}</strong></td>";
                echo "<td>{$current}</td>";
                echo "<td>{$required}</td>";
                echo "<td class='{$statusClass}'>{$statusText}</td>";
                echo "</tr>";
            }
            ?>
        </table>

        <h2>🐘 Información PHP</h2>
        <table>
            <tr>
                <th>Información</th>
                <th>Valor</th>
            </tr>
            <tr>
                <td><strong>Versión PHP</strong></td>
                <td><?php echo PHP_VERSION; ?></td>
            </tr>
            <tr>
                <td><strong>SAPI</strong></td>
                <td><?php echo php_sapi_name(); ?></td>
            </tr>
            <tr>
                <td><strong>Archivos php.ini cargados</strong></td>
                <td><?php echo php_ini_loaded_file() ?: 'Ninguno'; ?></td>
            </tr>
            <tr>
                <td><strong>Archivos .ini adicionales</strong></td>
                <td><?php echo php_ini_scanned_files() ?: 'Ninguno'; ?></td>
            </tr>
        </table>

        <h2>📝 Configuración Completa de Uploads</h2>
        <table>
            <tr>
                <th>Directiva</th>
                <th>Valor</th>
            </tr>
            <?php
            $allUploadSettings = [
                'file_uploads',
                'upload_max_filesize',
                'post_max_size',
                'max_file_uploads',
                'memory_limit',
                'max_execution_time',
                'max_input_time',
                'max_input_vars',
                'default_socket_timeout',
            ];

            foreach ($allUploadSettings as $setting) {
                $value = ini_get($setting);
                echo "<tr>";
                echo "<td><strong>{$setting}</strong></td>";
                echo "<td>" . ($value !== false ? $value : '<em>No definido</em>') . "</td>";
                echo "</tr>";
            }
            ?>
        </table>

        <h2>🔗 Prueba de Upload</h2>
        <p>Para probar el upload real, usa tu formulario Laravel o ejecuta:</p>
        <pre style="background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto;">
curl -X POST https://ricardo-admin.receramica.com/tu-endpoint-upload \
  -F "file=@imagen-grande.jpg" \
  -H "Authorization: Bearer tu-token"</pre>

        <p style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 0.9em;">
            Generado el: <?php echo date('Y-m-d H:i:s'); ?>
        </p>
    </div>
</body>
</html>
