<?php

/**
 * ============================================================================
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : src/Seguridad/CifradorContrasena.php
 * Autores  : Juan Luis Torres Leal
 *            Angela María Constain Escobar
 * ============================================================================
 */

declare(strict_types=1);

namespace Autenticacion\Seguridad;

/**
 * Genera y verifica hashes de contraseña con las funciones nativas de PHP
 * password_hash() / password_verify() (algoritmo bcrypt).
 *
 * Buenas prácticas aplicadas:
 *  - Sal aleatoria generada automáticamente e incluida en el hash.
 *  - Factor de costo 12 (4096 rondas).
 *  - password_verify() compara en tiempo constante.
 *
 * @author Juan Luis Torres Leal
 * @author Angela María Constain Escobar
 */
final class CifradorContrasena
{
    /** Factor de costo de bcrypt. */
    private const COSTO = 12;

    /** Clase utilitaria: no se instancia. */
    private function __construct()
    {
    }

    /**
     * Calcula el hash de una contraseña.
     *
     * @param string $contrasena Contraseña en texto plano.
     * @return string Hash bcrypt (60 caracteres, incluye algoritmo, costo y sal).
     */
    public static function calcularHash(string $contrasena): string
    {
        return password_hash($contrasena, PASSWORD_BCRYPT, ['cost' => self::COSTO]);
    }

    /**
     * Verifica si una contraseña coincide con el hash almacenado.
     *
     * @param string $contrasena   Contraseña ingresada.
     * @param string $hashEsperado Hash guardado en la base de datos.
     */
    public static function verificar(string $contrasena, string $hashEsperado): bool
    {
        return password_verify($contrasena, $hashEsperado);
    }
}
