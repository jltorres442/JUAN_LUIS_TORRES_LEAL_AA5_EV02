/*
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : main.jsx
 * Autores  : Juan Luis Torres Leal / Angela María Constain Escobar
 *
 * Punto de entrada de React: monta el componente raíz <Aplicacion />.
 */
import React from 'react';
import { createRoot } from 'react-dom/client';
import Aplicacion from './Aplicacion.jsx';
import './estilos/estilos.css';

createRoot(document.getElementById('raiz')).render(
  <React.StrictMode>
    <Aplicacion />
  </React.StrictMode>,
);
