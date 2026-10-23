<?php

/**
 * ============================================================================
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : src/Dao/UsuarioDao.php
 * Autores  : Juan Luis Torres Leal
 *            Angela María Constain Escobar
 * ============================================================================
 */

declare(strict_types=1);

namespace Autenticacion\Dao;

use Autenticacion\Modelo\Usuario;

/**
 * Contrato de acceso a datos (patrón DAO) para la entidad Usuario.
 *
 * Implementaciones intercambiables:
 *  - UsuarioDaoPdo:    extensión PDO (MySQL o SQLite).
 *  - UsuarioDaoMysqli: extensión nativa mysqli (solo MySQL).
 *
 * @author Juan Luis Torres Leal
 * @author Angela María Constain Escobar
 */
interface UsuarioDao
{
    /**
     * Busca un usuario por su nombre de inicio de sesión.
     *
     * @return Usuario|null El usuario, o null si no existe.
     */
    public function buscarPorNombreUsuario(string $nombreUsuario): ?Usuario;

    /**
     * Inserta un usuario nuevo y le asigna su identificador.
     */
    public function guardar(Usuario $usuario): Usuario;

    /**
     * Registra la fecha y hora del último inicio de sesión exitoso.
     */
    public function actualizarUltimoAcceso(int $idUsuario): void;

    /**
     * @return int Cantidad total de usuarios registrados.
     */
    public function contarUsuarios(): int;
}
