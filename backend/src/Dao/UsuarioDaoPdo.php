<?php

/**
 * ============================================================================
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : src/Dao/UsuarioDaoPdo.php
 * Autores  : Juan Luis Torres Leal
 *            Angela María Constain Escobar
 * ============================================================================
 */

declare(strict_types=1);

namespace Autenticacion\Dao;

use Autenticacion\Configuracion\ConexionBaseDatos;
use Autenticacion\Excepcion\AccesoDatosExcepcion;
use Autenticacion\Modelo\Usuario;
use PDO;
use PDOException;

/**
 * Implementación de UsuarioDao con PDO.
 *
 * Todas las sentencias son preparadas con parámetros con nombre, lo que
 * previene la inyección SQL.
 *
 * @author Juan Luis Torres Leal
 * @author Angela María Constain Escobar
 */
final class UsuarioDaoPdo implements UsuarioDao
{
    private const SQL_BUSCAR_POR_NOMBRE =
        'SELECT id_usuario, nombre_usuario, hash_contrasena, nombre_completo,
                correo_electronico, activo, fecha_creacion, ultimo_acceso
         FROM usuarios
         WHERE nombre_usuario = :nombreUsuario';

    private const SQL_INSERTAR =
        'INSERT INTO usuarios (nombre_usuario, hash_contrasena, nombre_completo,
                               correo_electronico, activo, fecha_creacion)
         VALUES (:nombreUsuario, :hashContrasena, :nombreCompleto,
                 :correoElectronico, :activo, :fechaCreacion)';

    private const SQL_ACTUALIZAR_ULTIMO_ACCESO =
        'UPDATE usuarios SET ultimo_acceso = :ultimoAcceso WHERE id_usuario = :idUsuario';

    private const SQL_CONTAR = 'SELECT COUNT(*) FROM usuarios';

    /** Conexión PDO a utilizar. */
    private PDO $conexion;

    /**
     * @param PDO|null $conexion Conexión a usar; por defecto la del perfil activo.
     */
    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? ConexionBaseDatos::abrirPdo();
    }

    public function buscarPorNombreUsuario(string $nombreUsuario): ?Usuario
    {
        try {
            $sentencia = $this->conexion->prepare(self::SQL_BUSCAR_POR_NOMBRE);
            $sentencia->execute([':nombreUsuario' => $nombreUsuario]);
            $fila = $sentencia->fetch(PDO::FETCH_ASSOC);
            return $fila === false ? null : Usuario::desdeFila($fila);
        } catch (PDOException $excepcion) {
            throw new AccesoDatosExcepcion('Error consultando el usuario', $excepcion);
        }
    }

    public function guardar(Usuario $usuario): Usuario
    {
        try {
            $usuario->fechaCreacion ??= date('Y-m-d H:i:s');
            $sentencia = $this->conexion->prepare(self::SQL_INSERTAR);
            $sentencia->execute([
                ':nombreUsuario' => $usuario->nombreUsuario,
                ':hashContrasena' => $usuario->hashContrasena,
                ':nombreCompleto' => $usuario->nombreCompleto,
                ':correoElectronico' => $usuario->correoElectronico,
                ':activo' => $usuario->activo ? 1 : 0,
                ':fechaCreacion' => $usuario->fechaCreacion,
            ]);
            $usuario->idUsuario = (int) $this->conexion->lastInsertId();
            return $usuario;
        } catch (PDOException $excepcion) {
            throw new AccesoDatosExcepcion('Error guardando el usuario', $excepcion);
        }
    }

    public function actualizarUltimoAcceso(int $idUsuario): void
    {
        try {
            $sentencia = $this->conexion->prepare(self::SQL_ACTUALIZAR_ULTIMO_ACCESO);
            $sentencia->execute([
                ':ultimoAcceso' => date('Y-m-d H:i:s'),
                ':idUsuario' => $idUsuario,
            ]);
        } catch (PDOException $excepcion) {
            throw new AccesoDatosExcepcion('Error actualizando el último acceso', $excepcion);
        }
    }

    public function contarUsuarios(): int
    {
        try {
            return (int) $this->conexion->query(self::SQL_CONTAR)->fetchColumn();
        } catch (PDOException $excepcion) {
            throw new AccesoDatosExcepcion('Error contando usuarios', $excepcion);
        }
    }
}
