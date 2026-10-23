-- =============================================================================
-- Proyecto : Servicio Web de Autenticación (PHP)
-- Archivo  : script_bd_autenticacion.sql
-- Motor    : MySQL 8.x / MariaDB 10.4+ (XAMPP, WAMP o MySQL Server)
-- Autores  : Juan Luis Torres Leal
--            Angela María Constain Escobar
-- Descripción:
--   Crea la base de datos, la tabla de usuarios y los usuarios de prueba.
--   Las contraseñas se guardan con password_hash() de PHP (bcrypt, costo 12),
--   que incluye una sal aleatoria dentro del mismo hash. NUNCA en texto plano.
--
--   Usuarios de prueba (usuario / contraseña):
--     jtorres    / Clave123*
--     aconstain  / Clave456*
--     admin      / Admin2026*
--
-- Ejecución:
--   phpMyAdmin -> pestaña SQL -> pegar y Continuar
--   o por consola: mysql -u root -p < script_bd_autenticacion.sql
--
-- IMPORTANTE: use este script (versión PHP), no el de la versión Java.
--   El de Java guarda las contraseñas con PBKDF2 y una columna sal_contrasena;
--   PHP no puede verificarlas y todo inicio de sesión devolvería 401.
--   Este script elimina y vuelve a crear la base, así que corrige ese caso.
-- =============================================================================

DROP DATABASE IF EXISTS bd_autenticacion;

CREATE DATABASE bd_autenticacion
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE bd_autenticacion;

-- -----------------------------------------------------------------------------
-- Tabla: usuarios
-- Convención: nombres en español, minúsculas y snake_case.
-- -----------------------------------------------------------------------------
CREATE TABLE usuarios (
    id_usuario          INT UNSIGNED  NOT NULL AUTO_INCREMENT  COMMENT 'Identificador único',
    nombre_usuario      VARCHAR(50)   NOT NULL                 COMMENT 'Nombre para iniciar sesión',
    hash_contrasena     VARCHAR(255)  NOT NULL                 COMMENT 'Hash bcrypt (password_hash)',
    nombre_completo     VARCHAR(120)  NOT NULL                 COMMENT 'Nombre para mostrar',
    correo_electronico  VARCHAR(120)  NULL                     COMMENT 'Correo de contacto',
    activo              TINYINT(1)    NOT NULL DEFAULT 1       COMMENT '1 = puede iniciar sesión',
    fecha_creacion      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de registro',
    ultimo_acceso       DATETIME      NULL                     COMMENT 'Último inicio de sesión exitoso',
    CONSTRAINT pk_usuarios PRIMARY KEY (id_usuario),
    CONSTRAINT uq_usuarios_nombre_usuario UNIQUE (nombre_usuario)
) ENGINE = InnoDB
  COMMENT = 'Usuarios autorizados para iniciar sesión';

-- -----------------------------------------------------------------------------
-- Datos de prueba
-- -----------------------------------------------------------------------------
INSERT INTO usuarios (nombre_usuario, hash_contrasena, nombre_completo, correo_electronico)
VALUES
    ('jtorres',
     '$2y$12$IQCv8qmow6LO9Iov.jWGJ.PXGTNMqVjTOI/FUw7yCjEU8FAiqqHc2',
     'Juan Luis Torres Leal',
     'juan.torres@correo.com'),
    ('aconstain',
     '$2y$12$a3fPvphM2YaG6/5biTuEQeF8Udw1mbAln6zOWxVcDixks90t9EHBa',
     'Angela María Constain Escobar',
     'angela.constain@correo.com'),
    ('admin',
     '$2y$12$GUzJ2SYmUThsVncWsGkxI.PcXM.1Ph..dw69KUDTMbWRWLnWxKUkK',
     'Administrador del Sistema',
     'admin@correo.com');

-- Verificación
SELECT id_usuario, nombre_usuario, nombre_completo, activo, fecha_creacion
FROM usuarios;
