<?php

/**
 * ============================================================================
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : arranque.php
 * Autores  : Juan Luis Torres Leal
 *            Angela María Constain Escobar
 * ============================================================================
 *
 * Archivo de arranque que incluyen todos los endpoints:
 *  1. Registra el cargador automático de clases (PSR-4) del espacio de
 *     nombres "Autenticacion\" ubicado en la carpeta src/.
 *  2. Configura zona horaria, codificación y manejo de errores para que
 *     ningún error PHP se filtre como HTML en la respuesta JSON.
 */

declare(strict_types=1);

date_default_timezone_set('America/Bogota');
mb_internal_encoding('UTF-8');

// Los errores se registran en el log del servidor, nunca se muestran al cliente.
ini_set('display_errors', '0');
error_reporting(E_ALL);

/**
 * Cargador automático PSR-4: Autenticacion\Dao\UsuarioDao -> src/Dao/UsuarioDao.php
 */
spl_autoload_register(static function (string $nombreClase): void {
    $prefijo = 'Autenticacion\\';
    if (strncmp($nombreClase, $prefijo, strlen($prefijo)) !== 0) {
        return;
    }
    $rutaRelativa = str_replace('\\', DIRECTORY_SEPARATOR, substr($nombreClase, strlen($prefijo)));
    $archivo = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $rutaRelativa . '.php';
    if (is_file($archivo)) {
        require $archivo;
    }
});

// Convierte avisos y advertencias en excepciones para tratarlos de forma uniforme.
set_error_handler(static function (int $nivel, string $mensaje, string $archivo, int $linea): bool {
    if (!(error_reporting() & $nivel)) {
        return false;
    }
    throw new ErrorException($mensaje, 0, $nivel, $archivo, $linea);
});
