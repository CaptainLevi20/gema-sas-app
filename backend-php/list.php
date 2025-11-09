<?php
// CONFIGURACIÓN DE CORS
// Permite solicitudes GET desde el frontend Next.js
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// list.php — Muestra los usuarios agrupados por estado
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

try {
    // Consulta simple para traer todos los usuarios, ordenados por fecha de creación
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Inicializa el objeto de resultado con las tres categorías solicitadas
    $result = [
        'activos' => [],
        'inactivos' => [],
        'espera' => []
    ];

    // Agrupa los datos según el codigo antes de enviarlos al frontend.
    foreach ($usuarios as $u) {
        // El codigo se usa para categorizar el usuario
        switch ($u['codigo']) {
            case 1:
                $result['activos'][] = $u;
                break;
            case 2:
                $result['inactivos'][] = $u;
                break;
            case 3:
                $result['espera'][] = $u;
                break;
        }
    }

    // Devuelve el JSON agrupado
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener los datos de la base de datos.', 'details' => $e->getMessage()]);
}
?>