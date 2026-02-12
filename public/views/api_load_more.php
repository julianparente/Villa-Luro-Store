<?php
// public/views/api_load_more.php

if (!isset($pdo)) {
    // Salvaguarda en caso de acceso directo (no debería ocurrir)
    require_once __DIR__ . '/../../config/session.php';
    require_once __DIR__ . '/../../config/db.php';
}

header('Content-Type: application/json');

// --- 1. Obtener parámetros ---
$limit = 15;
$offset = filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT);
if ($offset === false || $offset < 0) {
    $offset = 0;
}

// Filtros (deben coincidir con los del catálogo)
$categoria = filter_input(INPUT_GET, 'categoria', FILTER_SANITIZE_STRING) ?: '';
$marca = filter_input(INPUT_GET, 'marca', FILTER_VALIDATE_INT) ?: '';
$min_price = filter_input(INPUT_GET, 'min_price', FILTER_VALIDATE_FLOAT) ?: '';
$max_price = filter_input(INPUT_GET, 'max_price', FILTER_VALIDATE_FLOAT) ?: '';

// --- 2. Construir la consulta ---
$query = "SELECT p.*, m.nombre AS marca_nombre FROM perfumes p JOIN marcas m ON p.marca_id = m.id WHERE 1";
$params = [];

if ($categoria) {
    $query .= " AND p.categoria = ?";
    $params[] = $categoria;
}
if ($marca) {
    $query .= " AND p.marca_id = ?";
    $params[] = $marca;
}
if ($min_price !== '') {
    $query .= " AND p.precio >= ?";
    $params[] = $min_price;
}
if ($max_price !== '') {
    $query .= " AND p.precio <= ?";
    $params[] = $max_price;
}

$query .= " ORDER BY p.nombre LIMIT ? OFFSET ?";

// --- 3. Ejecutar y devolver JSON ---
try {
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $perfumes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'perfumes' => $perfumes]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de base de datos.']);
    error_log("API Load More Error: " . $e->getMessage());
}

exit;