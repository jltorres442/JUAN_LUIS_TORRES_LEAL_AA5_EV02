<?php

/**
 * ============================================================================
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : src/Controlador/RegistroControlador.php
 * Autores  : Juan Luis Torres Leal
 *            Angela María Constain Escobar
 * ============================================================================
 */

declare(strict_types=1);

namespace Autenticacion\Controlador;

use Autenticacion\Dao\FabricaUsuarioDao;
use Autenticacion\Excepcion\ValidacionExcepcion;
use Autenticacion\Http\ControladorApi;
use Autenticacion\Http\RespuestaApi;
use Autenticacion\Servicio\AutenticacionServicio;

/**
 * Endpoint de registro de usuarios.
 *
 * POST /api/autenticacion/registro.php
 * {
 *   "usuario": "nuevo.usuario",
 *   "contrasena": "ClaveSegura1",
 *   "nombreCompleto": "Nuevo Usuario",
 *   "correoElectronico": "nuevo@correo.com"
 * }
 *
 * Respuestas: 201 creado, 400 datos inválidos, 409 usuario existente.
 *
 * @author Juan Luis Torres Leal
 * @author Angela María Constain Escobar
 */
final class RegistroControlador extends ControladorApi
{
    protected function metodosPermitidos(): array
    {
        return ['POST'];
    }

    protected function procesar(array $cuerpo): RespuestaApi
    {
        $servicio = new AutenticacionServicio(FabricaUsuarioDao::crear());

        try {
            $usuarioCreado = $servicio->registrar($cuerpo);
            return RespuestaApi::exitosa(201, 'Usuario registrado correctamente', $usuarioCreado);
        } catch (ValidacionExcepcion $excepcion) {
            return RespuestaApi::error($excepcion->obtenerCodigoHttp(), $excepcion->getMessage());
        }
    }
}
