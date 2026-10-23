<?php

/**
 * ============================================================================
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Endpoint : POST /api/autenticacion/registro.php
 * Autores  : Juan Luis Torres Leal
 *            Angela María Constain Escobar
 * ============================================================================
 *
 * Registra un usuario nuevo: 201 creado, 400 datos inválidos, 409 duplicado.
 */

declare(strict_types=1);

require dirname(__DIR__, 2) . '/arranque.php';

(new Autenticacion\Controlador\RegistroControlador())->atender();
