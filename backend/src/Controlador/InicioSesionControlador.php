<?php

/**
 * ============================================================================
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : src/Controlador/InicioSesionControlador.php
 * Autores  : Juan Luis Torres Leal
 *            Angela María Constain Escobar
 * ============================================================================
 */

declare(strict_types=1);

namespace Autenticacion\Controlador;

use Autenticacion\Dao\FabricaUsuarioDao;
use Autenticacion\Http\ControladorApi;
use Autenticacion\Http\RespuestaApi;
use Autenticacion\Servicio\AutenticacionServicio;

/**
 * Endpoint de inicio de sesión.
 *
 * POST /api/autenticacion/iniciar-sesion.php
 * Cuerpo: { "usuario": "jtorres", "contrasena": "Clave123*" }
 *
 * Respuestas:
 *  - 200 "Autenticación satisfactoria" + datos del usuario.
 *  - 400 JSON inválido o campos vacíos.
 *  - 401 "Error en la autenticación".
 *  - 405 método no permitido.
 *  - 500 error interno (por ejemplo, MySQL apagado).
 *
 * @author Juan Luis Torres Leal
 * @author Angela María Constain Escobar
 */
final class InicioSesionControlador extends ControladorApi
{
    public const MENSAJE_EXITO = 'Autenticación satisfactoria';
    public const MENSAJE_FALLO = 'Error en la autenticación: usuario o contraseña incorrectos';

    protected function metodosPermitidos(): array
    {
        return ['POST'];
    }

    protected function procesar(array $cuerpo): RespuestaApi
    {
        $usuario = $cuerpo['usuario'] ?? null;
        $contrasena = $cuerpo['contrasena'] ?? null;

        // 1. Validar que lleguen ambos campos como texto no vacío
        if (!is_string($usuario) || trim($usuario) === '' || !is_string($contrasena) || $contrasena === '') {
            return RespuestaApi::error(400, "Los campos 'usuario' y 'contrasena' son obligatorios");
        }

        // 2. Autenticar contra la base de datos
        $servicio = new AutenticacionServicio(FabricaUsuarioDao::crear());
        $usuarioAutenticado = $servicio->autenticar($usuario, $contrasena);

        if ($usuarioAutenticado === null) {
            error_log("[ServicioAutenticacion] Inicio de sesión fallido: {$usuario}");
            return RespuestaApi::error(401, self::MENSAJE_FALLO);
        }

        return RespuestaApi::exitosa(200, self::MENSAJE_EXITO, $usuarioAutenticado);
    }
}
