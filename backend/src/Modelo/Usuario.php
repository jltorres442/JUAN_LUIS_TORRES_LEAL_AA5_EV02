<?php

/**
 * ============================================================================
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : src/Modelo/Usuario.php
 * Autores  : Juan Luis Torres Leal
 *            Angela María Constain Escobar
 * ============================================================================
 */

declare(strict_types=1);

namespace Autenticacion\Modelo;

/**
 * Entidad que representa una fila de la tabla `usuarios`.
 *
 * La contraseña nunca se guarda en texto plano: se almacena su hash
 * (bcrypt mediante password_hash), que ya incluye la sal.
 *
 * @author Juan Luis Torres Leal
 * @author Angela María Constain Escobar
 */
final class Usuario
{
    /**
     * @param int|null    $idUsuario         Identificador (null antes de guardarse).
     * @param string      $nombreUsuario     Nombre para iniciar sesión (único).
     * @param string      $hashContrasena    Hash bcrypt de la contraseña.
     * @param string      $nombreCompleto    Nombre para mostrar.
     * @param string|null $correoElectronico Correo de contacto.
     * @param bool        $activo            Indica si puede iniciar sesión.
     * @param string|null $fechaCreacion     Fecha de registro (Y-m-d H:i:s).
     * @param string|null $ultimoAcceso      Último inicio de sesión exitoso.
     */
    public function __construct(
        public ?int $idUsuario,
        public string $nombreUsuario,
        public string $hashContrasena,
        public string $nombreCompleto,
        public ?string $correoElectronico = null,
        public bool $activo = true,
        public ?string $fechaCreacion = null,
        public ?string $ultimoAcceso = null,
    ) {
    }

    /**
     * Construye la entidad a partir de una fila asociativa de la base de datos
     * (mapeo objeto-relacional manual).
     *
     * @param array<string, mixed> $fila Fila con los nombres de columna en snake_case.
     */
    public static function desdeFila(array $fila): self
    {
        return new self(
            idUsuario: (int) $fila['id_usuario'],
            nombreUsuario: (string) $fila['nombre_usuario'],
            hashContrasena: (string) $fila['hash_contrasena'],
            nombreCompleto: (string) $fila['nombre_completo'],
            correoElectronico: $fila['correo_electronico'] !== null ? (string) $fila['correo_electronico'] : null,
            activo: (bool) (int) $fila['activo'],
            fechaCreacion: $fila['fecha_creacion'] !== null ? (string) $fila['fecha_creacion'] : null,
            ultimoAcceso: $fila['ultimo_acceso'] !== null ? (string) $fila['ultimo_acceso'] : null,
        );
    }
}
