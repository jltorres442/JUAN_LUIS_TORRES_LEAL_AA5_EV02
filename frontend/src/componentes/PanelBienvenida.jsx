/*
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : PanelBienvenida.jsx
 * Autores  : Juan Luis Torres Leal / Angela María Constain Escobar
 *
 * Se muestra tras una autenticación satisfactoria con los datos
 * públicos que devolvió el servidor.
 */
import PropTypesLigeros from '../utilidades/validarPropiedades.js';

/**
 * Formatea una fecha ISO para mostrarla en español (Colombia).
 * @param {string|null} fechaIso
 * @returns {string}
 */
function formatearFecha(fechaIso) {
  if (!fechaIso) return 'Primer inicio de sesión';
  return new Date(fechaIso).toLocaleString('es-CO', { dateStyle: 'long', timeStyle: 'short' });
}

/**
 * @param {{ usuario: object, alCerrarSesion: () => void }} propiedades
 */
export default function PanelBienvenida({ usuario, alCerrarSesion }) {
  PropTypesLigeros.objeto('usuario', usuario);
  PropTypesLigeros.funcion('alCerrarSesion', alCerrarSesion);

  return (
    <div className="panel-bienvenida">
      <p className="mensaje mensaje-exito">Autenticación satisfactoria</p>
      <h2>Bienvenido(a), {usuario.nombreCompleto}</h2>

      <dl className="detalle-usuario">
        <dt>Usuario</dt>
        <dd>{usuario.usuario}</dd>
        <dt>Correo</dt>
        <dd>{usuario.correoElectronico ?? '—'}</dd>
        <dt>Acceso anterior</dt>
        <dd>{formatearFecha(usuario.ultimoAcceso)}</dd>
      </dl>

      <button type="button" className="boton-principal" onClick={alCerrarSesion}>
        Cerrar sesión
      </button>
    </div>
  );
}
