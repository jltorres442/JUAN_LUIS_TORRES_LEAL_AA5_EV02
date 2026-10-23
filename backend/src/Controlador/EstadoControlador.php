<?php

/**
 * ============================================================================
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : src/Controlador/EstadoControlador.php
 * Autores  : Juan Luis Torres Leal
 *            Angela María Constain Escobar
 * ============================================================================
 */

declare(strict_types=1);

namespace Autenticacion\Controlador;

use Autenticacion\Configuracion\ConexionBaseDatos;
use Autenticacion\Configuracion\ConfiguracionBaseDatos;
use Autenticacion\Dao\FabricaUsuarioDao;
use Autenticacion\Http\ControladorApi;
use Autenticacion\Http\RespuestaApi;
use PDO;
use Throwable;

/**
 * Endpoint de diagnóstico.
 *
 * GET /api/estado.php
 *
 * Verifica que el servicio esté en línea y que la base de datos responda.
 * Es la primera prueba recomendada en Postman.
 *
 * @author Juan Luis Torres Leal
 * @author Angela María Constain Escobar
 */
final class EstadoControlador extends ControladorApi
{
    protected function metodosPermitidos(): array
    {
        return ['GET'];
    }

    protected function procesar(array $cuerpo): RespuestaApi
    {
        $configuracion = ConfiguracionBaseDatos::obtenerInstancia();
        $datos = [
            'servicio' => 'Servicio Web de Autenticación',
            'version' => '1.0.0',
            'autores' => ['Juan Luis Torres Leal', 'Angela María Constain Escobar'],
            'versionPhp' => PHP_VERSION,
            'perfilBaseDatos' => $configuracion->obtenerPerfil(),
            'estrategiaAcceso' => $configuracion->obtenerEstrategiaAcceso(),
        ];

        try {
            $conexion = ConexionBaseDatos::abrirPdo();
            $datos['motorBaseDatos'] = $conexion->getAttribute(PDO::ATTR_DRIVER_NAME) . ' '
                . $conexion->getAttribute(PDO::ATTR_SERVER_VERSION);
            $datos['usuariosRegistrados'] = FabricaUsuarioDao::crear()->contarUsuarios();
            return RespuestaApi::exitosa(200, 'Servicio en línea y base de datos conectada', $datos);
        } catch (Throwable $excepcion) {
            $datos['detalle'] = $excepcion->getMessage();
            // Causa técnica original (por ejemplo: Access denied, Unknown database)
            if ($excepcion->getPrevious() !== null) {
                $datos['causa'] = $excepcion->getPrevious()->getMessage();
            }
            return RespuestaApi::error(503, 'Servicio en línea, pero la base de datos no responde', $datos);
        }
    }
}
