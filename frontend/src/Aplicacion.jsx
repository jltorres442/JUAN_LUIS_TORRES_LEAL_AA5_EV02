/*
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : Aplicacion.jsx
 * Autores  : Juan Luis Torres Leal / Angela María Constain Escobar
 *
 * Componente raíz. Decide si mostrar el formulario de inicio de sesión
 * o el panel de bienvenida, según exista un usuario autenticado.
 */
import { useState } from 'react';
import FormularioInicioSesion from './componentes/FormularioInicioSesion.jsx';
import PanelBienvenida from './componentes/PanelBienvenida.jsx';
import IndicadorEstado from './componentes/IndicadorEstado.jsx';

export default function Aplicacion() {
  /** Usuario autenticado (null mientras no haya sesión). */
  const [usuarioAutenticado, setUsuarioAutenticado] = useState(null);

  return (
    <main className="contenedor">
      <section className="tarjeta">
        <header className="encabezado">
          <h1>Servicio de Autenticación</h1>
          <IndicadorEstado />
        </header>

        {usuarioAutenticado ? (
          <PanelBienvenida
            usuario={usuarioAutenticado}
            alCerrarSesion={() => setUsuarioAutenticado(null)}
          />
        ) : (
          <FormularioInicioSesion alAutenticar={setUsuarioAutenticado} />
        )}
      </section>

      <footer className="pie">
        Juan Luis Torres Leal · Angela María Constain Escobar
      </footer>
    </main>
  );
}
