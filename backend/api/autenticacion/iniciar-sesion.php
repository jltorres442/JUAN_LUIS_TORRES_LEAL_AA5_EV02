<?php

/**
 * ============================================================================
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Endpoint : POST /api/autenticacion/iniciar-sesion.php
 * Autores  : Juan Luis Torres Leal
 *            Angela María Constain Escobar
 * ============================================================================
 *
 * Recibe { "usuario": "...", "contrasena": "..." } y responde:
 *   200 -> "Autenticación satisfactoria"
 *   401 -> "Error en la autenticación: usuario o contraseña incorrectos"
 */

declare(strict_types=1);

require dirname(__DIR__, 2) . '/arranque.php';

(new Autenticacion\Controlador\InicioSesionControlador())->atender();
