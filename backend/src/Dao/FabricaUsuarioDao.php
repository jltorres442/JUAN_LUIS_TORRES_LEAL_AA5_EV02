<?php

/**
 * ============================================================================
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : src/Dao/FabricaUsuarioDao.php
 * Autores  : Juan Luis Torres Leal
 *            Angela María Constain Escobar
 * ============================================================================
 */

declare(strict_types=1);

namespace Autenticacion\Dao;

use Autenticacion\Configuracion\ConfiguracionBaseDatos;

/**
 * Fábrica (patrón Factory) que decide qué implementación de UsuarioDao
 * usar según 'bd.estrategia.acceso'.
 *
 * @author Juan Luis Torres Leal
 * @author Angela María Constain Escobar
 */
final class FabricaUsuarioDao
{
    /** Clase utilitaria: no se instancia. */
    private function __construct()
    {
    }

    /**
     * Crea el DAO configurado. mysqli solo aplica al perfil MySQL; con
     * SQLite siempre se usa PDO.
     */
    public static function crear(): UsuarioDao
    {
        $configuracion = ConfiguracionBaseDatos::obtenerInstancia();
        $usarMysqli = $configuracion->obtenerEstrategiaAcceso() === 'mysqli'
            && !$configuracion->esPerfilDemostracion();

        return $usarMysqli ? new UsuarioDaoMysqli() : new UsuarioDaoPdo();
    }
}
