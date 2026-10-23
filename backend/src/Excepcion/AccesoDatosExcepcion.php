<?php

/**
 * ============================================================================
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : src/Excepcion/AccesoDatosExcepcion.php
 * Autores  : Juan Luis Torres Leal
 *            Angela María Constain Escobar
 * ============================================================================
 */

declare(strict_types=1);

namespace Autenticacion\Excepcion;

use RuntimeException;
use Throwable;

/**
 * Envuelve los errores de la capa de datos (PDO o mysqli) para no propagar
 * detalles técnicos hacia la respuesta de la API.
 *
 * @author Juan Luis Torres Leal
 * @author Angela María Constain Escobar
 */
final class AccesoDatosExcepcion extends RuntimeException
{
    /**
     * @param string         $mensaje Descripción del error.
     * @param Throwable|null $causa   Excepción original.
     */
    public function __construct(string $mensaje, ?Throwable $causa = null)
    {
        parent::__construct($mensaje, 0, $causa);
    }
}
