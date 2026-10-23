/*
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : vite.config.js
 * Autores  : Juan Luis Torres Leal / Angela María Constain Escobar
 *
 * Configuración de Vite. El proxy redirige /api hacia el backend PHP,
 * así el navegador ve un solo origen durante el desarrollo.
 *
 * Por defecto apunta a Apache de XAMPP (puerto 80). Si usa el servidor
 * embebido de PHP en otro puerto, cree un archivo .env con:
 *   VITE_URL_BACKEND=http://localhost:8080
 */
import { defineConfig, loadEnv } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig(({ mode }) => {
  const variables = loadEnv(mode, process.cwd(), '');
  const urlBackend = variables.VITE_URL_BACKEND || 'http://localhost';

  return {
    plugins: [react()],
    server: {
      port: 5173,
      proxy: {
        '/api': {
          target: urlBackend,
          changeOrigin: true,
          rewrite: (ruta) => `/servicio-autenticacion${ruta}`,
        },
      },
    },
  };
});
