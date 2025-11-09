-- Script Base de Datos

CREATE DATABASE IF NOT EXISTS gema_sas CHARACTER
SET
    utf8mb4 COLLATE utf8mb4_unicode_ci;

USE gema_sas;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    nombre VARCHAR(255),
    apellido VARCHAR(255),
    codigo TINYINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY ux_email_codigo (email, codigo)
);