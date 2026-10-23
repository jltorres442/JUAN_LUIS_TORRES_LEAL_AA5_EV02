<?php

/**
 * ============================================================================
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Endpoint : GET /api/estado.php
 * Autores  : Juan Luis Torres Leal
 *            Angela María Constain Escobar
 * ============================================================================
 *
 * Diagnóstico: confirma que el servicio y la base de datos responden.
 */

declare(strict_types=1);

require dirname(__DIR__) . '/arranque.php';

(new Autenticacion\Controlador\EstadoControlador())->atender();
