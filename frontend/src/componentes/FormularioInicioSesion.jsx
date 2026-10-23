/*
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : FormularioInicioSesion.jsx
 * Autores  : Juan Luis Torres Leal / Angela María Constain Escobar
 *
 * Formulario controlado que envía usuario y contraseña al backend y
 * muestra "Autenticación satisfactoria" o el error devuelto.
 */
import { useState } from 'react';
import PropTypesLigeros from '../utilidades/validarPropiedades.js';
import { iniciarSesion } from '../servicios/autenticacionServicio.js';

/**
 * @param {{ alAutenticar: (usuario: object) => void }} propiedades
 */
export default function FormularioInicioSesion({ alAutenticar }) {
  const [usuario, setUsuario] = useState('');
  const [contrasena, setContrasena] = useState('');
  const [mostrarContrasena, setMostrarContrasena] = useState(false);
  const [enviando, setEnviando] = useState(false);
  /** Mensaje de resultado: { tipo: 'exito' | 'error', texto } */
  const [mensaje, setMensaje] = useState(null);

  PropTypesLigeros.funcion('alAutenticar', alAutenticar);

  /**
   * Maneja el envío del formulario.
   * @param {React.FormEvent} evento
   */
  const manejarEnvio = async (evento) => {
    evento.preventDefault();

    // Validación en el cliente antes de llamar al servidor
    if (!usuario.trim() || !contrasena) {
      setMensaje({ tipo: 'error', texto: 'Ingrese el usuario y la contraseña.' });
      return;
    }

    setEnviando(true);
    setMensaje(null);
    const respuesta = await iniciarSesion(usuario.trim(), contrasena);
    setEnviando(false);

    if (respuesta.exito) {
      setMensaje({ tipo: 'exito', texto: respuesta.mensaje });
      // Pequeña pausa para que el usuario vea el mensaje de éxito
      setTimeout(() => alAutenticar(respuesta.datos), 900);
    } else {
      setMensaje({ tipo: 'error', texto: respuesta.mensaje });
      setContrasena('');
    }
  };

  return (
    <form className="formulario" onSubmit={manejarEnvio} noValidate>
      <label htmlFor="campo-usuario">Usuario</label>
      <input
        id="campo-usuario"
        type="text"
        autoComplete="username"
        value={usuario}
        onChange={(evento) => setUsuario(evento.target.value)}
        placeholder="jtorres"
        disabled={enviando}
        autoFocus
      />

      <label htmlFor="campo-contrasena">Contraseña</label>
      <div className="grupo-contrasena">
        <input
          id="campo-contrasena"
          type={mostrarContrasena ? 'text' : 'password'}
          autoComplete="current-password"
          value={contrasena}
          onChange={(evento) => setContrasena(evento.target.value)}
          placeholder="••••••••"
          disabled={enviando}
        />
        <button
          type="button"
          className="boton-secundario"
          onClick={() => setMostrarContrasena((valorActual) => !valorActual)}
          aria-label={mostrarContrasena ? 'Ocultar contraseña' : 'Mostrar contraseña'}
        >
          {mostrarContrasena ? 'Ocultar' : 'Ver'}
        </button>
      </div>

      <button type="submit" className="boton-principal" disabled={enviando}>
        {enviando ? 'Verificando…' : 'Iniciar sesión'}
      </button>

      {mensaje && (
        <p className={`mensaje mensaje-${mensaje.tipo}`} role="alert">
          {mensaje.texto}
        </p>
      )}
    </form>
  );
}
