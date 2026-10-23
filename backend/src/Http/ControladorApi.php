<?php

/**
 * ============================================================================
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : src/Http/ControladorApi.php
 * Autores  : Juan Luis Torres Leal
 *            Angela María Constain Escobar
 * ============================================================================
 */

declare(strict_types=1);

namespace Autenticacion\Http;

use Autenticacion\Configuracion\ConfiguracionBaseDatos;
use JsonException;
use Throwable;

/**
 * Clase base de los endpoints (equivalente al Servlet base).
 *
 * Centraliza en un solo lugar:
 *  - Cabeceras CORS y respuesta a la petición previa OPTIONS.
 *  - Validación del método HTTP (405 en JSON si no está permitido).
 *  - Lectura del cuerpo JSON (400 si está mal formado).
 *  - Captura de cualquier error inesperado (500 en JSON).
 *
 * Cada endpoint hereda de esta clase e implementa procesar().
 *
 * @author Juan Luis Torres Leal
 * @author Angela María Constain Escobar
 */
abstract class ControladorApi
{
    /** Métodos HTTP que acepta el endpoint, por ejemplo ['POST']. */
    abstract protected function metodosPermitidos(): array;

    /**
     * Lógica propia del endpoint.
     *
     * @param array<string, mixed> $cuerpo Cuerpo JSON decodificado (vacío en GET).
     */
    abstract protected function procesar(array $cuerpo): RespuestaApi;

    /**
     * Punto de entrada: lo llama el archivo del endpoint.
     */
    final public function atender(): void
    {
        $this->aplicarCors();
        $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Petición previa (preflight) del navegador: termina con 204.
        if ($metodo === 'OPTIONS') {
            http_response_code(204);
            return;
        }

        if (!in_array($metodo, $this->metodosPermitidos(), true)) {
            header('Allow: ' . implode(', ', $this->metodosPermitidos()));
            RespuestaApi::error(405, "Método {$metodo} no permitido en " . ($_SERVER['REQUEST_URI'] ?? ''))
                ->enviar();
            return;
        }

        try {
            $cuerpo = $metodo === 'GET' ? [] : $this->leerCuerpoJson();
        } catch (JsonException) {
            RespuestaApi::error(400, 'El cuerpo de la petición no es un JSON válido')->enviar();
            return;
        }

        try {
            $this->procesar($cuerpo)->enviar();
        } catch (Throwable $excepcion) {
            error_log('[ServicioAutenticacion] ' . $excepcion);
            RespuestaApi::error(500, 'Error interno del servidor. Verifique la conexión con la base de datos.')
                ->enviar();
        }
    }

    /**
     * Lee y decodifica el cuerpo JSON de la petición.
     *
     * @return array<string, mixed>
     * @throws JsonException Si el JSON está mal formado o no es un objeto.
     */
    private function leerCuerpoJson(): array
    {
        $contenido = (string) file_get_contents('php://input');
        $datos = json_decode($contenido, true, 16, JSON_THROW_ON_ERROR);
        if (!is_array($datos)) {
            throw new JsonException('Se esperaba un objeto JSON');
        }
        return $datos;
    }

    /**
     * Agrega las cabeceras CORS si el origen está autorizado.
     */
    private function aplicarCors(): void
    {
        $origen = $_SERVER['HTTP_ORIGIN'] ?? '';
        $origenesPermitidos = ConfiguracionBaseDatos::obtenerInstancia()->obtenerOrigenesPermitidos();

        if ($origen !== '' && in_array($origen, $origenesPermitidos, true)) {
            header('Access-Control-Allow-Origin: ' . $origen);
            header('Vary: Origin');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Accept');
            header('Access-Control-Max-Age: 3600');
        }
    }
}
