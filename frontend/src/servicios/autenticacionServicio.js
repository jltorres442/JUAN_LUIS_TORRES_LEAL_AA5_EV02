/*
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : autenticacionServicio.js
 * Autores  : Juan Luis Torres Leal / Angela María Constain Escobar
 *
 * Capa de comunicación con la API REST del backend PHP.
 * Todas las funciones devuelven el objeto RespuestaApi del servidor:
 *   { exito, codigo, mensaje, datos?, fechaHora }
 */

/** Ruta base de la API (el proxy de Vite la redirige al backend). */
const URL_BASE_API = import.meta.env.VITE_URL_API ?? '/api';

/**
 * Ejecuta una petición HTTP y normaliza la respuesta.
 *
 * @param {string} ruta    ruta relativa del endpoint
 * @param {RequestInit} opciones opciones de fetch
 * @returns {Promise<object>} respuesta de la API
 */
async function ejecutarPeticion(ruta, opciones = {}) {
  try {
    const respuesta = await fetch(`${URL_BASE_API}${ruta}`, {
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      ...opciones,
    });
    return await respuesta.json();
  } catch {
    // Error de red o servidor apagado
    return {
      exito: false,
      codigo: 0,
      mensaje: 'No fue posible conectarse con el servidor. ¿Está en ejecución el backend?',
    };
  }
}

/**
 * Envía las credenciales al endpoint de inicio de sesión.
 *
 * @param {string} usuario    nombre de usuario
 * @param {string} contrasena contraseña
 * @returns {Promise<object>} respuesta de la API
 */
export function iniciarSesion(usuario, contrasena) {
  return ejecutarPeticion('/autenticacion/iniciar-sesion.php', {
    method: 'POST',
    body: JSON.stringify({ usuario, contrasena }),
  });
}

/**
 * Consulta el estado del servicio y de la base de datos.
 *
 * @returns {Promise<object>} respuesta de la API
 */
export function consultarEstado() {
  return ejecutarPeticion('/estado.php');
}
