<?php

/**
 * ============================================================================
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : src/Configuracion/ConexionBaseDatos.php
 * Autores  : Juan Luis Torres Leal
 *            Angela María Constain Escobar
 * ============================================================================
 */

declare(strict_types=1);

namespace Autenticacion\Configuracion;

use Autenticacion\Excepcion\AccesoDatosExcepcion;
use Autenticacion\Seguridad\CifradorContrasena;
use mysqli;
use mysqli_sql_exception;
use PDO;
use PDOException;

/**
 * Proveedor de conexiones a la base de datos.
 *
 * - abrirPdo():    conexión PDO (MySQL o SQLite), reutilizada en la petición.
 * - abrirMysqli(): conexión con la extensión nativa mysqli (solo MySQL).
 *
 * En el perfil SQLite crea la tabla y los usuarios de prueba la primera vez.
 *
 * @author Juan Luis Torres Leal
 * @author Angela María Constain Escobar
 */
final class ConexionBaseDatos
{
    /** Conexión PDO compartida durante la petición. */
    private static ?PDO $conexionPdo = null;

    /** Clase utilitaria: no se instancia. */
    private function __construct()
    {
    }

    /**
     * Abre (o reutiliza) la conexión PDO del perfil activo.
     *
     * @throws AccesoDatosExcepcion Si no es posible conectarse.
     */
    public static function abrirPdo(): PDO
    {
        if (self::$conexionPdo !== null) {
            return self::$conexionPdo;
        }

        $configuracion = ConfiguracionBaseDatos::obtenerInstancia();
        $opciones = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,       // Errores como excepciones
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,               // Sentencias preparadas reales
        ];

        try {
            if ($configuracion->esPerfilDemostracion()) {
                $archivo = $configuracion->obtenerValorPerfil('archivo');
                self::$conexionPdo = new PDO('sqlite:' . $archivo, null, null, $opciones);
                self::prepararBaseDemostracion(self::$conexionPdo);
            } else {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                    $configuracion->obtenerValorPerfil('servidor'),
                    $configuracion->obtenerValorPerfil('puerto'),
                    $configuracion->obtenerValorPerfil('nombre')
                );
                self::$conexionPdo = new PDO(
                    $dsn,
                    $configuracion->obtenerValorPerfil('usuario'),
                    $configuracion->obtenerValorPerfil('contrasena'),
                    $opciones
                );
            }
        } catch (PDOException $excepcion) {
            throw new AccesoDatosExcepcion('No fue posible conectarse a la base de datos', $excepcion);
        }

        return self::$conexionPdo;
    }

    /**
     * Abre una conexión mysqli (requiere el perfil MySQL).
     *
     * @throws AccesoDatosExcepcion Si no es posible conectarse.
     */
    public static function abrirMysqli(): mysqli
    {
        $configuracion = ConfiguracionBaseDatos::obtenerInstancia();
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Errores como excepciones

        try {
            $conexion = new mysqli(
                $configuracion->obtenerValorPerfil('servidor'),
                $configuracion->obtenerValorPerfil('usuario'),
                $configuracion->obtenerValorPerfil('contrasena'),
                $configuracion->obtenerValorPerfil('nombre'),
                (int) $configuracion->obtenerValorPerfil('puerto')
            );
            $conexion->set_charset('utf8mb4');
            return $conexion;
        } catch (mysqli_sql_exception $excepcion) {
            throw new AccesoDatosExcepcion('No fue posible conectarse a la base de datos', $excepcion);
        }
    }

    /**
     * Crea la tabla y los usuarios de prueba en SQLite si aún no existen.
     * Equivale al script base-datos/script_bd_autenticacion.sql de MySQL.
     */
    private static function prepararBaseDemostracion(PDO $conexion): void
    {
        $conexion->exec(
            'CREATE TABLE IF NOT EXISTS usuarios (
                id_usuario         INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre_usuario     VARCHAR(50)  NOT NULL UNIQUE,
                hash_contrasena    VARCHAR(255) NOT NULL,
                nombre_completo    VARCHAR(120) NOT NULL,
                correo_electronico VARCHAR(120) NULL,
                activo             INTEGER      NOT NULL DEFAULT 1,
                fecha_creacion     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                ultimo_acceso      DATETIME     NULL
            )'
        );

        $cantidad = (int) $conexion->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
        if ($cantidad > 0) {
            return;
        }

        $usuariosPrueba = [
            ['jtorres', 'Clave123*', 'Juan Luis Torres Leal', 'juan.torres@correo.com'],
            ['aconstain', 'Clave456*', 'Angela María Constain Escobar', 'angela.constain@correo.com'],
            ['admin', 'Admin2026*', 'Administrador del Sistema', 'admin@correo.com'],
        ];
        $sentencia = $conexion->prepare(
            'INSERT INTO usuarios (nombre_usuario, hash_contrasena, nombre_completo, correo_electronico)
             VALUES (:usuario, :hash, :nombre, :correo)'
        );
        foreach ($usuariosPrueba as [$usuario, $contrasena, $nombre, $correo]) {
            $sentencia->execute([
                ':usuario' => $usuario,
                ':hash' => CifradorContrasena::calcularHash($contrasena),
                ':nombre' => $nombre,
                ':correo' => $correo,
            ]);
        }
    }
}
