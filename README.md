# Servicio Web de Autenticación (PHP)

**Autores:** Juan Luis Torres Leal · Angela María Constain Escobar
**Versión:** 1.1.0 (probada en XAMPP con PHP 8.2 y MariaDB 10.4)

Servicio web de inicio de sesión: recibe un **usuario** y una **contraseña**; si son correctos responde
**"Autenticación satisfactoria"**, de lo contrario devuelve **"Error en la autenticación"**.

| Capa | Tecnología |
|---|---|
| API REST | PHP 8.1+ sin librerías externas, un archivo por endpoint |
| Acceso a datos | PDO **y** mysqli con sentencias preparadas (intercambiables) |
| Base de datos | MySQL / MariaDB (XAMPP). Modo demostración con SQLite |
| Página de inicio de sesión | React 18 + JSX (Vite), ya compilada dentro del backend |
| Pruebas | Colección de Postman (10 peticiones), hoja de pruebas en Excel y pruebas unitarias en PHP |


---

## 1. Estructura del proyecto

```
servicio-autenticacion-php/
├── instalar-en-xampp.bat                 Copia backend\ a C:\xampp\htdocs\servicio-autenticacion
├── backend/                              Lo que se publica en Apache
│   ├── index.html + assets/              Página de inicio de sesión (React compilado)
│   ├── api/                              ARCHIVOS ENDPOINT
│   │   ├── index.html                    Lista de endpoints
│   │   ├── estado.php                    GET  diagnóstico
│   │   └── autenticacion/
│   │       ├── iniciar-sesion.php        POST inicio de sesión
│   │       └── registro.php              POST registro de usuarios
│   ├── arranque.php                      Cargador de clases PSR-4 y manejo de errores
│   ├── config/basedatos.php              Parámetros de conexión
│   ├── enrutador.php                     Solo para el servidor embebido (php -S)
│   ├── .htaccess                         URLs sin .php y protección de carpetas
│   ├── src/                              Espacio de nombres Autenticacion\
│   │   ├── Configuracion/                ConfiguracionBaseDatos, ConexionBaseDatos
│   │   ├── Controlador/                  InicioSesionControlador, RegistroControlador, EstadoControlador
│   │   ├── Dao/                          UsuarioDao (interfaz), UsuarioDaoPdo, UsuarioDaoMysqli, FabricaUsuarioDao
│   │   ├── Excepcion/                    AccesoDatosExcepcion, ValidacionExcepcion
│   │   ├── Http/                         ControladorApi (base: CORS, 405, JSON), RespuestaApi
│   │   ├── Modelo/                       Usuario (entidad)
│   │   ├── Seguridad/                    CifradorContrasena (bcrypt)
│   │   └── Servicio/                     AutenticacionServicio (lógica de negocio)
│   ├── pruebas/AutenticacionServicioPrueba.php
│   └── herramientas/generar_hash.php
├── base-datos/script_bd_autenticacion.sql
├── frontend/                             Código fuente React + JSX (Vite)
├── postman/ServicioAutenticacion.postman_collection.json
└── documentacion/pruebas-postman-10-peticiones.xlsx
```

Arquitectura por capas: **Endpoint → Controlador → Servicio → DAO → Base de datos**.

---

## 2. Instalación con XAMPP (Windows)

1. **Inicie Apache y MySQL** en el panel de XAMPP. Los dos deben quedar en verde. Si alguno no arranca, vea la
   sección 7.
2. **Copie el backend** de una de estas dos formas:
   - Doble clic en `instalar-en-xampp.bat`.
   - O copie a mano la carpeta `backend` a `C:\xampp\htdocs\` y renómbrela a **`servicio-autenticacion`**.

3. **Cree la base de datos:** abra <http://localhost/phpmyadmin> → pestaña **SQL** → pegue el contenido de
   `base-datos/script_bd_autenticacion.sql` → **Continuar**. Si pregunta por `DROP DATABASE`, responda
   **Aceptar**.
4. **Compruebe el servicio:** <http://localhost/servicio-autenticacion/api/estado.php> debe responder
   `"codigo": 200` y `"usuariosRegistrados": 3`.
5. **Abra la página de inicio de sesión:** <http://localhost/servicio-autenticacion/>

**Usuarios de prueba**

| Usuario | Contraseña |
|---|---|
| `jtorres` | `Clave123*` |
| `aconstain` | `Clave456*` |
| `admin` | `Admin2026*` |

Las contraseñas se guardan con `password_hash()` (bcrypt, costo 12). Para crear el hash de una contraseña nueva:
`php herramientas\generar_hash.php MiClave`

### Configuración de la base de datos

`config/basedatos.php` trae los valores de XAMPP: servidor `127.0.0.1`, puerto `3306`, usuario `root` sin
contraseña. Cámbielos solo si su MySQL es distinto. Para cambiar la estrategia de acceso:
`'bd.estrategia.acceso' => 'pdo'` o `'mysqli'`.

### Alternativa sin XAMPP (servidor embebido de PHP)

```bat
cd backend
set BD_PERFIL=sqlite
php -S localhost:8080 enrutador.php
```

Con `BD_PERFIL=sqlite` se crea sola una base de demostración con los mismos usuarios. La página queda en
`http://localhost:8080/servicio-autenticacion/` y la API en `http://localhost:8080/servicio-autenticacion/api`
(cambie `urlBase` en Postman).

---

## 3. Endpoints de la API REST

URL base: `http://localhost/servicio-autenticacion/api`
Con Apache también funcionan sin la extensión (`/autenticacion/iniciar-sesion`).

### `POST /autenticacion/iniciar-sesion.php`

```json
{ "usuario": "jtorres", "contrasena": "Clave123*" }
```

**200 OK**
```json
{
  "exito": true,
  "codigo": 200,
  "mensaje": "Autenticación satisfactoria",
  "datos": {
    "idUsuario": 1,
    "usuario": "jtorres",
    "nombreCompleto": "Juan Luis Torres Leal",
    "correoElectronico": "juan.torres@correo.com",
    "ultimoAcceso": null
  },
  "fechaHora": "2026-10-22T19:35:00-05:00"
}
```

**401 Unauthorized**
```json
{ "exito": false, "codigo": 401, "mensaje": "Error en la autenticación: usuario o contraseña incorrectos", "fechaHora": "..." }
```

| Código | Caso |
|---|---|
| 200 | Credenciales correctas |
| 400 | JSON mal formado o campos vacíos |
| 401 | Usuario o contraseña incorrectos, o usuario inactivo |
| 405 | Método HTTP distinto de POST |
| 500 | Error interno |

### `POST /autenticacion/registro.php`
```json
{ "usuario": "nuevo.usuario", "contrasena": "ClaveSegura1", "nombreCompleto": "Nuevo Usuario", "correoElectronico": "nuevo@correo.com" }
```
201 creado · 400 datos inválidos · 409 usuario ya existe

### `GET /estado.php`
Diagnóstico. Responde 200 si el servicio y la base de datos están bien. Si no, responde 503 con los campos
`detalle` y `causa` (el error exacto de MySQL).

---

## 4. Pruebas con Postman

**Con cuenta (gratuita).** Las versiones recientes de Postman piden cuenta para importar colecciones.
1. **Import** → `postman/ServicioAutenticacion.postman_collection.json`.
2. La variable `urlBase` ya apunta a `http://localhost/servicio-autenticacion/api`.
3. Ejecute cada petición, o use **Run collection** para correr las 10 pruebas automáticas.

**Sin cuenta (modo ligero).** Escriba las peticiones a mano con los datos de
`documentacion/pruebas-postman-10-peticiones.xlsx`. La hoja trae método, URL, Body y resultado esperado de cada
prueba, y calcula sola cuáles pasaron. La prueba 8 registra `angela.prueba`; si la repite, dará 409. Para
repetirla, cambie ese nombre en las pruebas 8 y 9.

| # | Prueba | Esperado |
|---|---|---|
| 1 | Estado del servicio | 200 |
| 2 | Credenciales correctas | 200 · Autenticación satisfactoria |
| 3 | Contraseña incorrecta | 401 |
| 4 | Usuario inexistente | 401 |
| 5 | Campos vacíos | 400 |
| 6 | JSON mal formado | 400 |
| 7 | Método GET no permitido | 405 |
| 8 | Registrar usuario nuevo | 201 |
| 9 | Iniciar sesión con el usuario registrado | 200 |
| 10 | Registrar usuario duplicado | 409 |

## 5. Pruebas unitarias

```bat
cd backend
php pruebas\AutenticacionServicioPrueba.php
```

## 6. Página de inicio de sesión (React)

Ya viene compilada en `backend/`. Solo es necesario Node.js para modificarla:

```bat
cd frontend
npm install
npm run dev          :: desarrollo en http://localhost:5173 (usa la API de XAMPP)
npm run build:xampp  :: recompila la página dentro de ..\backend
```

Después de `build:xampp`, vuelva a copiar `backend` a `htdocs`. Si usa `php -S` en el puerto 8080 durante el
desarrollo, cree `frontend\.env` con `VITE_URL_BACKEND=http://localhost:8080`.

---

## 7. Solución de problemas

| Síntoma | Causa | Solución |
|---|---|---|
| MySQL no inicia en XAMPP; el registro dice `Bind on TCP/IP port … 10048` | Otro MySQL (por ejemplo un servicio de Windows como `MySQL80`) ocupa el puerto 3306 | `services.msc` → detener ese servicio y ponerlo en **Manual**; o en CMD como administrador: `net stop NOMBRE_DEL_SERVICIO` |
| Apache no inicia y `apache\logs\error.log` no registra nada nuevo | Otro programa ocupa el puerto 80 o 443 (IIS, Skype, VMware…) | `netstat -ano \| findstr ":80 :443"` y luego `tasklist /FI "PID eq N"`. Si es PID 4 (IIS): `net stop W3SVC` |
| `estado.php` → 503 con `causa: Access denied for user 'root'` | La contraseña de `config/basedatos.php` no coincide con la de MySQL | En XAMPP, root no tiene contraseña: deje `'bd.mysql.contrasena' => ''` |
| `estado.php` → 503 con `causa: Unknown database` | No se ejecutó el script SQL | Ejecute `base-datos/script_bd_autenticacion.sql` en phpMyAdmin |

| La página muestra "Servidor no disponible" | La API no conecta con MySQL | Abra `api/estado.php` y siga lo que indique el campo `causa` |
| Postman no deja usar **Import** | Postman pide cuenta para colecciones | Cree una cuenta gratuita, o use la hoja de `documentacion/` en el modo ligero |
| `http://localhost/servicio-autenticacion/` 

---

## 8. Estándares de codificación y nombramiento

- **Idioma:** identificadores, comentarios y mensajes en español, sin tildes en el código (`contrasena`).
- **PHP:** estilo **PSR-12** y carga automática **PSR-4**, con `declare(strict_types=1)` y tipos en todas las
  firmas. Clases en `PascalCase` (`AutenticacionServicio`), métodos y variables en `camelCase`
  (`buscarPorNombreUsuario`), constantes en `MAYUSCULAS_CON_GUION` (`MENSAJE_EXITO`). Sufijos por rol:
  `*Controlador`, `*Servicio`, `*Dao`, `*Excepcion`.
- **PHPDoc** en clases y métodos públicos, con `@author` de ambos autores, y cabecera de autoría en cada archivo.
- **Endpoints:** archivos en minúscula con guion (`iniciar-sesion.php`). Cada uno solo carga `arranque.php` y
  delega en su controlador.
- **SQL:** tablas y columnas en `snake_case` minúscula (`usuarios`, `nombre_usuario`), restricciones con prefijo
  (`pk_`, `uq_`).
- **React:** componentes en `PascalCase` con extensión `.jsx`, manejadores `manejar*` y propiedades de evento
  `al*`.
- **Seguridad:**
  - Contraseñas con `password_hash` y `password_verify`.
  - Sentencias preparadas (PDO y mysqli) contra inyección SQL.
  - Mismo mensaje y tiempo de respuesta si falla el usuario o la contraseña.
  - La respuesta nunca expone el hash.
  - Los errores de PHP no se muestran al cliente.
  - Las carpetas `src/`, `config/`, `pruebas/` y `herramientas/` están bloqueadas desde la web.
