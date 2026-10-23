<?php

/**
 * ============================================================================
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : src/Dao/UsuarioDaoMysqli.php
 * Autores  : Juan Luis Torres Leal
 *            Angela María Constain Escobar
 * ============================================================================
 */

declare(strict_types=1);

namespace Autenticacion\Dao;

use Autenticacion\Configuracion\ConexionBaseDatos;
use Autenticacion\Excepcion\AccesoDatosExcepcion;
use Autenticacion\Modelo\Usuario;
use mysqli;
use mysqli_sql_exception;

/**
 * Implementación de UsuarioDao con la extensión nativa mysqli (solo MySQL).
 *
 * Usa sentencias preparadas con parámetros posicionales (?) enlazados
 * con bind_param, equivalentes a PreparedStatement de JDBC.
 *
 * @author Juan Luis Torres Leal
 * @author Angela María Constain Escobar
 */
final class UsuarioDaoMysqli implements UsuarioDao
{
    private const SQL_BUSCAR_POR_NOMBRE =
        'SELECT id_usuario, nombre_usuario, hash_contrasena, nombre_completo,
                correo_electronico, activo, fecha_creacion, ultimo_acceso
         FROM usuarios
         WHERE nombre_usuario = ?';

    private const SQL_INSERTAR =
        'INSERT INTO usuarios (nombre_usuario, hash_contrasena, nombre_completo,
                               correo_electronico, activo, fecha_creacion)
         VALUES (?, ?, ?, ?, ?, ?)';

    private const SQL_ACTUALIZAR_ULTIMO_ACCESO =
        'UPDATE usuarios SET ultimo_acceso = ? WHERE id_usuario = ?';

    private const SQL_CONTAR = 'SELECT COUNT(*) AS cantidad FROM usuarios';

    /** Conexión mysqli a utilizar. */
    private mysqli $conexion;

    /**
     * @param mysqli|null $conexion Conexión a usar; por defecto la del perfil activo.
     */
    public function __construct(?mysqli $conexion = null)
    {
        $this->conexion = $conexion ?? ConexionBaseDatos::abrirMysqli();
    }

    public function buscarPorNombreUsuario(string $nombreUsuario): ?Usuario
    {
        try {
            $sentencia = $this->conexion->prepare(self::SQL_BUSCAR_POR_NOMBRE);
            $sentencia->bind_param('s', $nombreUsuario);
            $sentencia->execute();
            $fila = $sentencia->get_result()->fetch_assoc();
            $sentencia->close();
            return $fila === null ? null : Usuario::desdeFila($fila);
        } catch (mysqli_sql_exception $excepcion) {
            throw new AccesoDatosExcepcion('Error consultando el usuario', $excepcion);
        }
    }

    public function guardar(Usuario $usuario): Usuario
    {
        try {
            $usuario->fechaCreacion ??= date('Y-m-d H:i:s');
            $activo = $usuario->activo ? 1 : 0;
            $sentencia = $this->conexion->prepare(self::SQL_INSERTAR);
            $sentencia->bind_param(
                'ssssis',
                $usuario->nombreUsuario,
                $usuario->hashContrasena,
                $usuario->nombreCompleto,
                $usuario->correoElectronico,
                $activo,
                $usuario->fechaCreacion
            );
            $sentencia->execute();
            $usuario->idUsuario = (int) $this->conexion->insert_id;
            $sentencia->close();
            return $usuario;
        } catch (mysqli_sql_exception $excepcion) {
            throw new AccesoDatosExcepcion('Error guardando el usuario', $excepcion);
        }
    }

    public function actualizarUltimoAcceso(int $idUsuario): void
    {
        try {
            $fechaActual = date('Y-m-d H:i:s');
            $sentencia = $this->conexion->prepare(self::SQL_ACTUALIZAR_ULTIMO_ACCESO);
            $sentencia->bind_param('si', $fechaActual, $idUsuario);
            $sentencia->execute();
            $sentencia->close();
        } catch (mysqli_sql_exception $excepcion) {
            throw new AccesoDatosExcepcion('Error actualizando el último acceso', $excepcion);
        }
    }

    public function contarUsuarios(): int
    {
        try {
            $resultado = $this->conexion->query(self::SQL_CONTAR);
            return (int) $resultado->fetch_assoc()['cantidad'];
        } catch (mysqli_sql_exception $excepcion) {
            throw new AccesoDatosExcepcion('Error contando usuarios', $excepcion);
        }
    }
}
