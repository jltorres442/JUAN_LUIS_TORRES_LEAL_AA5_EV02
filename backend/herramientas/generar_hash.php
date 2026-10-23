<?php

/**
 * ============================================================================
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : herramientas/generar_hash.php
 * Autores  : Juan Luis Torres Leal
 *            Angela María Constain Escobar
 * ============================================================================
 *
 * Utilidad de consola para generar el hash de una contraseña y poder
 * insertar usuarios manualmente en la base de datos.
 *
 * Uso:  php herramientas/generar_hash.php MiClave
 */

declare(strict_types=1);

require dirname(__DIR__) . '/arranque.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

if ($argc < 2) {
    fwrite(STDERR, "Uso: php herramientas/generar_hash.php <contrasena>\n");
    exit(1);
}

echo 'hash_contrasena = ' . Autenticacion\Seguridad\CifradorContrasena::calcularHash($argv[1]) . PHP_EOL;
