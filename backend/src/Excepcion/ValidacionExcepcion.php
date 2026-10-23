<?php

/**
 * ============================================================================
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : src/Excepcion/ValidacionExcepcion.php
 * Autores  : Juan Luis Torres Leal
 *            Angela María Constain Escobar
 * ============================================================================
 */

declare(strict_types=1);

namespace Autenticacion\Excepcion;

use InvalidArgumentException;

/**
 * Error de reglas de negocio. Lleva el código HTTP que debe devolverse
 * (400 datos inválidos, 409 conflicto por usuario existente).
 *
 * @author Juan Luis Torres Leal
 * @author Angela María Constain Escobar
 */
final class ValidacionExcepcion extends InvalidArgumentException
{
    /**
     * @param string $mensaje    Mensaje legible para el cliente.
     * @param int    $codigoHttp Código HTTP asociado.
     */
    public function __construct(string $mensaje, private readonly int $codigoHttp = 400)
    {
        parent::__construct($mensaje);
    }

    /** @return int Código HTTP a devolver. */
    public function obtenerCodigoHttp(): int
    {
        return $this->codigoHttp;
    }
}
