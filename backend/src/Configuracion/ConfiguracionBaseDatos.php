<?php

/**
 * ============================================================================
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : src/Configuracion/ConfiguracionBaseDatos.php
 * Autores  : Juan Luis Torres Leal
 *            Angela María Constain Escobar
 * ============================================================================
 */

declare(strict_types=1);

namespace Autenticacion\Configuracion;

/**
 * Lee y expone los parámetros de config/basedatos.php.
 *
 * Orden de precedencia de cada valor:
 *  1. Variable de entorno (bd.perfil -> BD_PERFIL).
 *  2. Valor del archivo de configuración.
 *
 * Implementada como Singleton: la configuración es única en la petición.
 *
 * @author Juan Luis Torres Leal
 * @author Angela María Constain Escobar
 */
final class ConfiguracionBaseDatos
{
    /** Única instancia (patrón Singleton). */
    private static ?self $instancia = null;

    /** @var array<string, string> Valores cargados del archivo. */
    private array $valores;

    /** Constructor privado: carga el archivo una sola vez. */
    private function __construct()
    {
        $this->valores = require dirname(__DIR__, 2) . '/config/basedatos.php';
    }

    /**
     * Devuelve la instancia única.
     */
    public static function obtenerInstancia(): self
    {
        return self::$instancia ??= new self();
    }

    /**
     * Obtiene un valor aplicando el orden de precedencia.
     *
     * @param string $clave Nombre de la propiedad (por ejemplo 'bd.perfil').
     * @return string Valor encontrado o cadena vacía.
     */
    public function obtenerValor(string $clave): string
    {
        $variableEntorno = getenv(strtoupper(str_replace('.', '_', $clave)));
        if ($variableEntorno !== false) {
            return $variableEntorno;
        }
        return (string) ($this->valores[$clave] ?? '');
    }

    /** @return string 'mysql' o 'sqlite'. */
    public function obtenerPerfil(): string
    {
        return strtolower(trim($this->obtenerValor('bd.perfil')));
    }

    /** @return string 'pdo' o 'mysqli'. */
    public function obtenerEstrategiaAcceso(): string
    {
        return strtolower(trim($this->obtenerValor('bd.estrategia.acceso')));
    }

    /** @return bool true si el perfil es la base de demostración SQLite. */
    public function esPerfilDemostracion(): bool
    {
        return $this->obtenerPerfil() === 'sqlite';
    }

    /**
     * Valor del perfil activo: bd.<perfil>.<sufijo>.
     *
     * @param string $sufijo Parte final de la clave (servidor, usuario...).
     */
    public function obtenerValorPerfil(string $sufijo): string
    {
        return $this->obtenerValor('bd.' . $this->obtenerPerfil() . '.' . $sufijo);
    }

    /**
     * Orígenes autorizados para CORS.
     *
     * @return list<string>
     */
    public function obtenerOrigenesPermitidos(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $this->obtenerValor('cors.origenes')))));
    }
}
