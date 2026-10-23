<?php

/**
 * ============================================================================
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : src/Http/RespuestaApi.php
 * Autores  : Juan Luis Torres Leal
 *            Angela María Constain Escobar
 * ============================================================================
 */

declare(strict_types=1);

namespace Autenticacion\Http;

/**
 * Envoltorio estándar para todas las respuestas JSON de la API.
 *
 * {
 *   "exito": true,
 *   "codigo": 200,
 *   "mensaje": "Autenticación satisfactoria",
 *   "datos": { ... },
 *   "fechaHora": "2026-10-20T21:56:00-05:00"
 * }
 *
 * @author Juan Luis Torres Leal
 * @author Angela María Constain Escobar
 */
final class RespuestaApi
{
    /**
     * @param bool       $exito   Indica si la operación fue correcta.
     * @param int        $codigo  Código de estado HTTP.
     * @param string     $mensaje Mensaje legible para el cliente.
     * @param mixed|null $datos   Carga útil opcional (se omite si es null).
     */
    private function __construct(
        public readonly bool $exito,
        public readonly int $codigo,
        public readonly string $mensaje,
        public readonly mixed $datos = null,
    ) {
    }

    /** Crea una respuesta exitosa. */
    public static function exitosa(int $codigo, string $mensaje, mixed $datos = null): self
    {
        return new self(true, $codigo, $mensaje, $datos);
    }

    /** Crea una respuesta de error. */
    public static function error(int $codigo, string $mensaje, mixed $datos = null): self
    {
        return new self(false, $codigo, $mensaje, $datos);
    }

    /**
     * Envía la respuesta: código HTTP, cabecera JSON y cuerpo.
     */
    public function enviar(): void
    {
        $cuerpo = [
            'exito' => $this->exito,
            'codigo' => $this->codigo,
            'mensaje' => $this->mensaje,
        ];
        if ($this->datos !== null) {
            $cuerpo['datos'] = $this->datos;
        }
        $cuerpo['fechaHora'] = date('c');

        http_response_code($this->codigo);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($cuerpo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
