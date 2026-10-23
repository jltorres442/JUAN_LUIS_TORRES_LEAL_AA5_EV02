/*
 * Proyecto : Servicio Web de Autenticación (PHP)
 * Archivo  : validarPropiedades.js
 * Autores  : Juan Luis Torres Leal / Angela María Constain Escobar
 *
 * Validación mínima de propiedades (props) en modo desarrollo, sin
 * depender de la librería prop-types.
 */

const esDesarrollo = import.meta.env.DEV;

const PropTypesLigeros = {
  /**
   * Advierte en consola si la propiedad no es una función.
   * @param {string} nombre nombre de la propiedad
   * @param {*} valor valor recibido
   */
  funcion(nombre, valor) {
    if (esDesarrollo && typeof valor !== 'function') {
      console.warn(`La propiedad "${nombre}" debe ser una función.`);
    }
  },

  /**
   * Advierte en consola si la propiedad no es un objeto.
   * @param {string} nombre nombre de la propiedad
   * @param {*} valor valor recibido
   */
  objeto(nombre, valor) {
    if (esDesarrollo && (valor === null || typeof valor !== 'object')) {
      console.warn(`La propiedad "${nombre}" debe ser un objeto.`);
    }
  },
};

export default PropTypesLigeros;
