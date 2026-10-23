<?php

/**
 * ============================================================================
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : config/basedatos.php
 * Autores  : Juan Luis Torres Leal
 *            Angela María Constain Escobar
 * ============================================================================
 *
 * Parámetros de conexión a la base de datos.
 *
 * Cada valor puede sobrescribirse con una variable de entorno del mismo
 * nombre en mayúsculas (por ejemplo BD_PERFIL=sqlite).
 */

declare(strict_types=1);

return [
    // Perfil activo: 'mysql' (principal) | 'sqlite' (demostración, sin instalar nada)
    'bd.perfil' => 'mysql',

    // Estrategia de acceso a datos: 'pdo' | 'mysqli'
    'bd.estrategia.acceso' => 'pdo',

    // ---- Perfil MySQL / MariaDB (XAMPP) -------------------------------------
    'bd.mysql.servidor' => '127.0.0.1',
    'bd.mysql.puerto' => '3306',
    'bd.mysql.nombre' => 'bd_autenticacion',
    'bd.mysql.usuario' => 'root',
    'bd.mysql.contrasena' => '',

    // ---- Perfil SQLite (demostración) ---------------------------------------
    // Archivo que se crea automáticamente con los usuarios de prueba.
    'bd.sqlite.archivo' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bd_autenticacion_demo.sqlite',

    // Orígenes autorizados para CORS (cliente React en desarrollo)
    'cors.origenes' => 'http://localhost:5173,http://127.0.0.1:5173,http://localhost:4173,http://localhost:3000',
];
