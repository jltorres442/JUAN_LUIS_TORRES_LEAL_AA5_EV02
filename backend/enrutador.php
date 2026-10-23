<?php

/**
 * ============================================================================
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : enrutador.php
 * Autores  : Juan Luis Torres Leal
 *            Angela María Constain Escobar
 * ============================================================================
 *
 * Enrutador para el servidor embebido de PHP (no se usa con Apache/XAMPP).
 * Reproduce la URL de XAMPP (/servicio-autenticacion/...) y permite omitir
 * la extensión .php, igual que el .htaccess.
 *
 * Uso (desde la carpeta backend):
 *   php -S localhost:8080 enrutador.php
 */

declare(strict_types=1);

$prefijo = '/servicio-autenticacion';
$ruta = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if (str_starts_with($ruta, $prefijo)) {
    $ruta = substr($ruta, strlen($prefijo)) ?: '/';
}

// Bloquea el acceso a carpetas internas
if (preg_match('#^/(src|config|pruebas|herramientas)(/|$)#', $ruta) === 1) {
    http_response_code(403);
    return true;
}

$archivo = __DIR__ . $ruta;
if (!str_ends_with($archivo, '.php') && is_file($archivo . '.php')) {
    $archivo .= '.php';
}
if ($ruta === '/' ) {
    $archivo = __DIR__ . '/index.html';
}

if (is_file($archivo) && str_ends_with($archivo, '.php') && str_starts_with(realpath($archivo), __DIR__ . '/api/')) {
    require $archivo;
    return true;
}
if (is_dir($archivo) && is_file($archivo . '/index.html')) {
    $archivo .= '/index.html';
}

// Archivos estáticos: página de inicio de sesión (React compilado) y sus recursos
$tiposContenido = [
    'html' => 'text/html; charset=UTF-8',
    'js' => 'text/javascript; charset=UTF-8',
    'css' => 'text/css; charset=UTF-8',
    'ico' => 'image/x-icon',
    'svg' => 'image/svg+xml',
];
$extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
if (is_file($archivo) && isset($tiposContenido[$extension])) {
    header('Content-Type: ' . $tiposContenido[$extension]);
    readfile($archivo);
    return true;
}

http_response_code(404);
header('Content-Type: application/json; charset=UTF-8');
echo json_encode([
    'exito' => false,
    'codigo' => 404,
    'mensaje' => 'Recurso no encontrado. Endpoints: GET /api/estado.php, '
        . 'POST /api/autenticacion/iniciar-sesion.php, POST /api/autenticacion/registro.php',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
return true;
