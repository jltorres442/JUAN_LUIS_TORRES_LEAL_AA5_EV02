/*
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : IndicadorEstado.jsx
 * Autores  : Juan Luis Torres Leal / Angela María Constain Escobar
 *
 * Indicador que consulta GET /api/estado.php al cargar y muestra si el
 * backend y la base de datos están disponibles.
 */
import { useEffect, useState } from 'react';
import { consultarEstado } from '../servicios/autenticacionServicio.js';

export default function IndicadorEstado() {
  /** 'consultando' | 'enLinea' | 'fueraDeLinea' */
  const [estado, setEstado] = useState('consultando');
  const [detalle, setDetalle] = useState('');

  useEffect(() => {
    let componenteMontado = true;
    consultarEstado().then((respuesta) => {
      if (!componenteMontado) return;
      setEstado(respuesta.exito ? 'enLinea' : 'fueraDeLinea');
      setDetalle(respuesta.datos?.motorBaseDatos ?? respuesta.mensaje);
    });
    // Limpieza: evita actualizar el estado si el componente se desmonta
    return () => {
      componenteMontado = false;
    };
  }, []);

  const textos = {
    consultando: 'Verificando servidor…',
    enLinea: 'Servidor en línea',
    fueraDeLinea: 'Servidor no disponible',
  };

  return (
    <span className={`indicador indicador-${estado}`} title={detalle}>
      <span className="punto" aria-hidden="true" />
      {textos[estado]}
    </span>
  );
}
