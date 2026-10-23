<?php

/**
 * ============================================================================
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : src/Servicio/AutenticacionServicio.php
 * Autores  : Juan Luis Torres Leal
 *            Angela María Constain Escobar
 * ============================================================================
 */

declare(strict_types=1);

namespace Autenticacion\Servicio;

use Autenticacion\Dao\UsuarioDao;
use Autenticacion\Excepcion\ValidacionExcepcion;
use Autenticacion\Modelo\Usuario;
use Autenticacion\Seguridad\CifradorContrasena;

/**
 * Lógica de negocio del inicio de sesión y del registro de usuarios.
 *
 * Separa las reglas del negocio de los endpoints (capa HTTP) y del acceso
 * a datos (DAO), siguiendo una arquitectura por capas.
 *
 * @author Juan Luis Torres Leal
 * @author Angela María Constain Escobar
 */
final class AutenticacionServicio
{
    /**
     * Hash ficticio usado cuando el usuario no existe, para que el tiempo de
     * respuesta no revele qué usuarios están registrados.
     */
    private const HASH_FICTICIO = '$2y$12$dIP3eFrIgqxQ58GRkWFo0e7D3UhIHnX/a6EB5MoUj7wB0kJ9qGvX6';

    private const PATRON_USUARIO = '/^[A-Za-z0-9._-]{3,50}$/';
    private const LONGITUD_MINIMA_CONTRASENA = 8;

    /**
     * @param UsuarioDao $usuarioDao Acceso a datos inyectado (facilita las pruebas).
     */
    public function __construct(private readonly UsuarioDao $usuarioDao)
    {
    }

    /**
     * Valida las credenciales de un usuario.
     *
     * Por seguridad no se distingue si falló el usuario o la contraseña.
     *
     * @param string $nombreUsuario Nombre de usuario.
     * @param string $contrasena    Contraseña en texto plano.
     * @return array<string, mixed>|null Datos públicos del usuario, o null si falla.
     */
    public function autenticar(string $nombreUsuario, string $contrasena): ?array
    {
        $usuario = $this->usuarioDao->buscarPorNombreUsuario(trim($nombreUsuario));

        if ($usuario === null) {
            CifradorContrasena::verificar($contrasena, self::HASH_FICTICIO); // iguala tiempos
            return null;
        }

        if (!CifradorContrasena::verificar($contrasena, $usuario->hashContrasena) || !$usuario->activo) {
            return null;
        }

        // Se devuelve el acceso anterior y luego se registra el actual.
        $datosPublicos = self::convertirADatosPublicos($usuario);
        $this->usuarioDao->actualizarUltimoAcceso((int) $usuario->idUsuario);
        return $datosPublicos;
    }

    /**
     * Registra un usuario nuevo validando sus datos.
     *
     * @param array<string, mixed> $solicitud Cuerpo JSON decodificado.
     * @return array<string, mixed> Datos públicos del usuario creado.
     * @throws ValidacionExcepcion Si los datos son inválidos (400) o el usuario existe (409).
     */
    public function registrar(array $solicitud): array
    {
        $nombreUsuario = trim((string) ($solicitud['usuario'] ?? ''));
        $contrasena = (string) ($solicitud['contrasena'] ?? '');
        $nombreCompleto = trim((string) ($solicitud['nombreCompleto'] ?? ''));
        $correo = trim((string) ($solicitud['correoElectronico'] ?? ''));

        $this->validarRegistro($nombreUsuario, $contrasena, $nombreCompleto, $correo);

        if ($this->usuarioDao->buscarPorNombreUsuario($nombreUsuario) !== null) {
            throw new ValidacionExcepcion("El usuario '{$nombreUsuario}' ya existe", 409);
        }

        $usuario = new Usuario(
            idUsuario: null,
            nombreUsuario: $nombreUsuario,
            hashContrasena: CifradorContrasena::calcularHash($contrasena),
            nombreCompleto: $nombreCompleto,
            correoElectronico: $correo !== '' ? $correo : null,
        );

        return self::convertirADatosPublicos($this->usuarioDao->guardar($usuario));
    }

    /**
     * Reglas de validación del registro.
     *
     * @throws ValidacionExcepcion Si alguna regla no se cumple.
     */
    private function validarRegistro(string $usuario, string $contrasena, string $nombre, string $correo): void
    {
        if (preg_match(self::PATRON_USUARIO, $usuario) !== 1) {
            throw new ValidacionExcepcion(
                'El usuario debe tener entre 3 y 50 caracteres (letras, números, punto, guion)'
            );
        }
        if (mb_strlen($contrasena) < self::LONGITUD_MINIMA_CONTRASENA) {
            throw new ValidacionExcepcion(
                'La contraseña debe tener al menos ' . self::LONGITUD_MINIMA_CONTRASENA . ' caracteres'
            );
        }
        if ($nombre === '') {
            throw new ValidacionExcepcion('El nombre completo es obligatorio');
        }
        if ($correo !== '' && filter_var($correo, FILTER_VALIDATE_EMAIL) === false) {
            throw new ValidacionExcepcion('El correo electrónico no es válido');
        }
    }

    /**
     * Convierte la entidad en datos públicos: nunca incluye el hash.
     *
     * @return array<string, mixed>
     */
    private static function convertirADatosPublicos(Usuario $usuario): array
    {
        return [
            'idUsuario' => $usuario->idUsuario,
            'usuario' => $usuario->nombreUsuario,
            'nombreCompleto' => $usuario->nombreCompleto,
            'correoElectronico' => $usuario->correoElectronico,
            'ultimoAcceso' => $usuario->ultimoAcceso,
        ];
    }
}
