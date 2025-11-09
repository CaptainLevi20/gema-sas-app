<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// list.php — muestra los usuarios agrupados por estado
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [
        'activos' => [],
        'inactivos' => [],
        'espera' => []
    ];

    foreach ($usuarios as $u) {
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

    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener datos: ' . $e->getMessage()]);
}
?>