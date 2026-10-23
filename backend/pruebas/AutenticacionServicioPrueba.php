<?php

/**
 * ============================================================================
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : pruebas/AutenticacionServicioPrueba.php
 * Autores  : Juan Luis Torres Leal
 *            Angela María Constain Escobar
 * ============================================================================
 *
 * Pruebas unitarias de AutenticacionServicio con un DAO en memoria
 * (no requiere base de datos ni librerías externas).
 *
 * Uso:  php pruebas/AutenticacionServicioPrueba.php
 */

declare(strict_types=1);

require dirname(__DIR__) . '/arranque.php';

use Autenticacion\Dao\UsuarioDao;
use Autenticacion\Excepcion\ValidacionExcepcion;
use Autenticacion\Modelo\Usuario;
use Autenticacion\Servicio\AutenticacionServicio;

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

/** DAO falso que guarda los usuarios en un arreglo. */
final class UsuarioDaoMemoria implements UsuarioDao
{
    /** @var array<string, Usuario> */
    public array $usuarios = [];
    private int $secuencia = 1;

    public function buscarPorNombreUsuario(string $nombreUsuario): ?Usuario
    {
        return $this->usuarios[$nombreUsuario] ?? null;
    }

    public function guardar(Usuario $usuario): Usuario
    {
        $usuario->idUsuario = $this->secuencia++;
        $this->usuarios[$usuario->nombreUsuario] = $usuario;
        return $usuario;
    }

    public function actualizarUltimoAcceso(int $idUsuario): void
    {
    }

    public function contarUsuarios(): int
    {
        return count($this->usuarios);
    }
}

$fallas = 0;

/** Registra el resultado de una prueba. */
function verificar(string $nombrePrueba, bool $condicion): void
{
    global $fallas;
    echo ($condicion ? '  OK    ' : '  FALLA ') . $nombrePrueba . PHP_EOL;
    if (!$condicion) {
        $fallas++;
    }
}

/** Ejecuta una función y devuelve el código HTTP de la ValidacionExcepcion (0 si no lanzó). */
function codigoExcepcion(callable $accion): int
{
    try {
        $accion();
        return 0;
    } catch (ValidacionExcepcion $excepcion) {
        return $excepcion->obtenerCodigoHttp();
    }
}

echo "Pruebas de AutenticacionServicio" . PHP_EOL;

$dao = new UsuarioDaoMemoria();
$servicio = new AutenticacionServicio($dao);
$servicio->registrar(['usuario' => 'jtorres', 'contrasena' => 'Clave123*', 'nombreCompleto' => 'Juan Luis Torres Leal']);

verificar('Credenciales correctas: autenticación satisfactoria', $servicio->autenticar('jtorres', 'Clave123*') !== null);
verificar('Contraseña incorrecta: error en la autenticación', $servicio->autenticar('jtorres', 'otraClave') === null);
verificar('Usuario inexistente: error en la autenticación', $servicio->autenticar('noexiste', 'Clave123*') === null);
verificar('La respuesta no expone el hash', !array_key_exists('hashContrasena', $servicio->autenticar('jtorres', 'Clave123*') ?? []));
verificar('Registro duplicado devuelve 409', codigoExcepcion(fn () => $servicio->registrar(
    ['usuario' => 'jtorres', 'contrasena' => 'Clave123*', 'nombreCompleto' => 'Otro']
)) === 409);
verificar('Contraseña corta devuelve 400', codigoExcepcion(fn () => $servicio->registrar(
    ['usuario' => 'nuevo', 'contrasena' => '123', 'nombreCompleto' => 'Nuevo']
)) === 400);
verificar('Correo inválido devuelve 400', codigoExcepcion(fn () => $servicio->registrar(
    ['usuario' => 'nuevo', 'contrasena' => 'Clave123*', 'nombreCompleto' => 'Nuevo', 'correoElectronico' => 'malo']
)) === 400);

$dao->usuarios['jtorres']->activo = false;
verificar('Usuario inactivo no puede iniciar sesión', $servicio->autenticar('jtorres', 'Clave123*') === null);

echo PHP_EOL . ($fallas === 0 ? 'TODAS LAS PRUEBAS PASARON' : "{$fallas} PRUEBA(S) FALLARON") . PHP_EOL;
exit($fallas === 0 ? 0 : 1);
