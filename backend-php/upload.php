<?php
// CONFIGURACIÓN DE CORS
// Permite solicitudes desde el frontend Next.js (http://localhost:3000)
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Manejo de la solicitud OPTIONS previa al POST
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
// Incluir la conexión PDO segura desde config.php
require_once 'config.php';

// 1. VALIDACIÓN DE ARCHIVO
if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No se envió ningún archivo.']);
    exit;
}

$file = $_FILES['file']['tmp_name'];
if (!file_exists($file) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'El archivo no se pudo leer o hubo un error de carga.']);
    exit;
}

// Lee el archivo, ignorando saltos de línea y líneas vacías
$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if (count($lines) === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'El archivo está vacío.']);
    exit;
}

// 2. VALIDACIÓN DE CADA LÍNEA
$validRows = [];
$errors = [];
$lineNumber = 0;

foreach ($lines as $line) {
    $lineNumber++;
    // Usa 'explode' con el separador de COMA (,)
    $data = array_map('trim', explode(',', $line));

    // VALIDACIÓN CRÍTICA 1: Formato de columnas
    if (count($data) !== 4) {
        $errors[] = "Línea $lineNumber: Se esperaban 4 columnas, se encontraron " . count($data) . ".";
        continue;
    }

    list($email, $nombre, $apellido, $codigo) = $data;

    // VALIDACIÓN CRÍTICA 2: Formato de Email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Línea $lineNumber: El email '{$email}' no tiene un formato válido.";
    }

    // VALIDACIÓN CRÍTICA 3: Código de Estado
    $validCodes = ['1', '2', '3'];
    if (!in_array($codigo, $validCodes)) {
        $errors[] = "Línea $lineNumber: El código de estado '{$codigo}' es inválido. Debe ser 1, 2 o 3.";
        continue;
    }

    // Guardar línea válida (si pasa todas las validaciones previas)
    $validRows[] = [
        'email' => $email,
        // Guarda NULL si el nombre/apellido está vacío.
        'nombre' => $nombre ?: null,
        'apellido' => $apellido ?: null,
        'codigo' => (int) $codigo
    ];
}

// 3. SI HAY ERRORES → RECHAZAR EL LOTE
if (count($errors) > 0) {
    http_response_code(400); // Código de error de cliente
    echo json_encode([
        'error' => 'El formato interno del archivo no es válido. Por favor, revisa la documentación',
        'detalles' => $errors
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// 4. GUARDAR EN LA BASE DE DATOS (Transacción)
try {
    // Si alguna inserción falla, TODO el proceso se revierte.
    $pdo->beginTransaction();

    // Usar INSERT IGNORE para omitir duplicados (definidos por ux_email_codigo)
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (email, nombre, apellido, codigo) VALUES (?, ?, ?, ?)");

    $insertedCount = 0;
    foreach ($validRows as $row) {
        $stmt->execute([
            $row['email'],
            $row['nombre'],
            $row['apellido'],
            $row['codigo']
        ]);
        // Contar solo las filas que realmente se insertaron (no ignoradas)
        if ($stmt->rowCount() > 0) {
            $insertedCount++;
        }
    }

    // Confirmar los cambios (todo el lote es válido).
    $pdo->commit();

    // Respuesta de éxito
    echo json_encode([
        'success' => true,
        'message' => 'Archivo procesado correctamente.',
        'total_registros_archivo' => count($validRows),
        'registros_insertados' => $insertedCount
    ]);

} catch (PDOException $e) {
    // Manejo de errores de base de datos
    $pdo->rollBack(); // Asegurar el rollback si algo falla
    http_response_code(500);
    echo json_encode(['error' => 'Error al guardar los datos en la base de datos.', 'details' => $e->getMessage()]);
}
?>