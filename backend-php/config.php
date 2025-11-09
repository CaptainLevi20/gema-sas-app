<?php
// config.php — configuración de conexión a MySQL
// Las variables se cargan desde el entorno (Garantizado por docker-compose.yml)

$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');

// Si alguna variable no está definida, significa un error de configuración de Docker
if (!$host || !$user || !$dbname) {
    http_response_code(500);
    // Devolver JSON para que Next.js maneje el error
    die(json_encode(['error' => '❌ Error de configuración: Faltan variables de entorno para la DB.']));
}

try {
    // El host ahora es 'db', el nombre del servicio de Docker Compose
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Nota: Los headers de CORS en list.php y upload.php deben seguir apuntando a http://localhost:3000
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => '❌ Error de conexión a la base de datos: La base de datos no está disponible.']));
}
?>