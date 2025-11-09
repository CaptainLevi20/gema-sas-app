<?php
// config.php — CONFIGURACIÓN DE CONEXIÓN A MYSQL
// Las credenciales se leen del entorno (ENV) para seguridad y portabilidad con Docker.

// Lee las variables inyectadas por docker-compose.yml
$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');

// === 1. VALIDACIÓN DE ENTORNO ===
// Verifica que Docker Compose haya cargado las variables.
if (!$host || !$user || !$dbname) {
    http_response_code(500);
    // Notificar error de configuración de servidor
    die(json_encode(['error' => '❌ Error de configuración: Faltan variables de entorno para la DB.']));
}

// === 2. CONEXIÓN PDO ===
try {
    // Nota: $host es 'db', el nombre del servicio MySQL dentro de la red Docker.
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    // Detener la ejecución con un error de servidor
    die(json_encode(['error' => '❌ Error de conexión a la base de datos: La base de datos no está disponible.']));
}
?>