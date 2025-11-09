-- Script Base de Datos para la solución GEMA SAS
-- Garantiza la idempotencia: puede ejecutarse varias veces sin fallar.

CREATE DATABASE IF NOT EXISTS gema_sas CHARACTER
SET
    utf8mb4 COLLATE utf8mb4_unicode_ci;

USE gema_sas;

CREATE TABLE IF NOT EXISTS users (
    -- ID autoincremental, clave primaria
    id INT AUTO_INCREMENT PRIMARY KEY,
    -- Email, obligatorio. La longitud estándar es 255
    email VARCHAR(255) NOT NULL,
    -- Nombre y apellido son opcionales (NULL) si no vienen en el archivo.
    nombre VARCHAR(255),
    apellido VARCHAR(255),
    -- Código de estado (1: Activo, 2: Inactivo, 3: Espera)
    codigo TINYINT NOT NULL,
    -- Timestamp para la fecha de carga
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Evita que el mismo 'email' se cargue más de una vez con el mismo 'codigo' de estado.
    UNIQUE KEY ux_email_codigo (email, codigo)
);