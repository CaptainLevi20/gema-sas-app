<?php
// ==========================
// CONFIGURACIÓN DE CORS
// ==========================
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

// ==========================
// VALIDACIÓN DE ARCHIVO
// ==========================
if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No se envió ningún archivo.']);
    exit;
}

$file = $_FILES['file']['tmp_name'];
if (!file_exists($file)) {
    http_response_code(400);
    echo json_encode(['error' => 'El archivo no se pudo leer.']);
    exit;
}

$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if (count($lines) === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'El archivo está vacío.']);
    exit;
}

// ==========================
// VALIDACIÓN DE CADA LÍNEA
// ==========================
$validRows = [];
$errors = [];
$lineNumber = 0;

foreach ($lines as $line) {
    $lineNumber++;
    $parts = array_map('trim', explode(',', $line));

    // Validar cantidad de columnas
    if (count($parts) !== 4) {
        $errors[] = "Línea $lineNumber: formato incorrecto (se esperaban 4 columnas)";
        continue;
    }

    list($email, $nombre, $apellido, $codigo) = $parts;

    // Validar email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Línea $lineNumber: email inválido o vacío";
        continue;
    }

    // Validar código
    if (!in_array((int) $codigo, [1, 2, 3])) {
        $errors[] = "Línea $lineNumber: código inválido ($codigo). Debe ser 1, 2 o 3.";
        continue;
    }

    // Guardar línea válida (sin verificar duplicados internos)
    $validRows[] = [
        'email' => $email,
        'nombre' => $nombre ?: null,
        'apellido' => $apellido ?: null,
        'codigo' => (int) $codigo
    ];
}

// ==========================
// SI HAY ERRORES → RECHAZAR
// ==========================
if (count($errors) > 0) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Archivo inválido. Corrige los siguientes errores:',
        'detalles' => $errors
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// ==========================
// GUARDAR EN LA BASE DE DATOS
// ==========================
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (email, nombre, apellido, codigo) VALUES (?, ?, ?, ?)");

    $inserted = 0;
    foreach ($validRows as $row) {
        $stmt->execute([
            $row['email'],
            $row['nombre'],
            $row['apellido'],
            $row['codigo']
        ]);
        $inserted++;
    }

    $pdo->commit();

    echo json_encode([
        'mensaje' => 'Archivo procesado correctamente.',
        'insertados' => $inserted,
        'total' => count($validRows)
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Error al insertar en la base de datos.', 'detalle' => $e->getMessage()]);
}
