<?php
// public/views/api_subscribe.php

// Asegurarse de que la sesión y la BD están disponibles
if (!isset($pdo)) {
    require_once __DIR__ . '/../../config/session.php';
    require_once __DIR__ . '/../../config/db.php';
}

header('Content-Type: application/json');

// Solo permitir peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$response = ['success' => false, 'message' => 'Ocurrió un error inesperado.'];

// 1. Validar formato del email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Por favor, ingrese un correo electrónico válido.';
    echo json_encode($response);
    exit;
}

try {
    // 2. Verificar si el email ya existe para evitar duplicados
    $stmt = $pdo->prepare("SELECT id FROM suscripciones WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        $response['message'] = 'Este correo electrónico ya está suscrito.';
    } else {
        // 3. Usar sentencias preparadas para insertar el nuevo email
        $stmt = $pdo->prepare("INSERT INTO suscripciones (email) VALUES (?)");
        $stmt->execute([$email]);
        $response = ['success' => true, 'message' => '¡Gracias por suscribirte a nuestro newsletter!'];
    }
} catch (PDOException $e) {
    error_log('Error de suscripción: ' . $e->getMessage());
    $response['message'] = 'Error del servidor. Por favor, intente más tarde.';
    http_response_code(500);
}

echo json_encode($response);
exit;