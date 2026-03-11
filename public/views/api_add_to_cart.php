<?php
// public/views/api_add_to_cart.php

// Asegurar que la conexión a la BD y la sesión estén disponibles
if (!isset($pdo)) {
    require_once __DIR__ . '/../../config/session.php';
    require_once __DIR__ . '/../../config/db.php';
}

// Establecer cabecera JSON
header('Content-Type: application/json');

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

// Obtener ID del perfume
$perfume_ids_input = $_POST['perfume_id'] ?? null;
$perfume_ids = [];

if (is_array($perfume_ids_input)) {
    foreach ($perfume_ids_input as $id) {
        if (filter_var($id, FILTER_VALIDATE_INT)) {
            $perfume_ids[] = (int)$id;
        }
    }
} elseif (filter_var($perfume_ids_input, FILTER_VALIDATE_INT)) {
    $perfume_ids[] = (int)$perfume_ids_input;
}

if (empty($perfume_ids)) {
    echo json_encode(['success' => false, 'message' => 'Producto inválido.']);
    exit;
}

try {
    foreach ($perfume_ids as $perfume_id) {
        $cantidad = 1; // Cantidad por defecto al añadir

        // 1. Verificar existencia y stock del producto
        $stmt = $pdo->prepare("SELECT stock, nombre FROM perfumes WHERE id = ?");
        $stmt->execute([$perfume_id]);
        $perfume = $stmt->fetch();

        if (!$perfume || $perfume['stock'] < $cantidad) {
            // Si uno falla, continuamos con el siguiente, pero podríamos registrar un error.
            continue;
        }

        // 2. Añadir al carrito (Lógica para usuario logueado vs invitado)
        $is_logged = function_exists('isLoggedIn') ? isLoggedIn() : isset($_SESSION['usuario_id']);

        if ($is_logged) {
            $usuario_id = $_SESSION['usuario_id'];
            
            // Verificar si el producto ya está en el carrito del usuario
            $stmtCheck = $pdo->prepare("SELECT id, cantidad FROM carrito WHERE usuario_id = ? AND perfume_id = ?");
            $stmtCheck->execute([$usuario_id, $perfume_id]);
            $item = $stmtCheck->fetch();

            if ($item) {
                // Actualizar cantidad
                $nueva_cantidad = $item['cantidad'] + $cantidad;
                if ($nueva_cantidad > $perfume['stock']) {
                    $nueva_cantidad = $perfume['stock'];
                }
                $stmtUpdate = $pdo->prepare("UPDATE carrito SET cantidad = ? WHERE id = ?");
                $stmtUpdate->execute([$nueva_cantidad, $item['id']]);
            } else {
                // Insertar nuevo item
                $stmtInsert = $pdo->prepare("INSERT INTO carrito (usuario_id, perfume_id, cantidad) VALUES (?, ?, ?)");
                $stmtInsert->execute([$usuario_id, $perfume_id, $cantidad]);
            }

        } else {
            // Lógica para invitados (Sesión)
            if (!isset($_SESSION['carrito'])) {
                $_SESSION['carrito'] = [];
            }

            $found = false;
            foreach ($_SESSION['carrito'] as &$item) {
                if ($item['perfume_id'] == $perfume_id) {
                    $nueva_cantidad = $item['cantidad'] + $cantidad;
                    if ($nueva_cantidad > $perfume['stock']) {
                        $nueva_cantidad = $perfume['stock'];
                    }
                    $item['cantidad'] = $nueva_cantidad;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $_SESSION['carrito'][] = ['perfume_id' => $perfume_id, 'cantidad' => $cantidad];
            }
        }
    }

    // Calcular conteo total después de añadir todos los productos
    if ($is_logged) {
        $stmtCount = $pdo->prepare("SELECT SUM(cantidad) FROM carrito WHERE usuario_id = ?");
        $stmtCount->execute([$_SESSION['usuario_id']]);
        $cart_count = (int)$stmtCount->fetchColumn();
    } else {
        // Calcular total de items en sesión
        $cart_count = array_sum(array_column($_SESSION['carrito'], 'cantidad'));
    }

    // 3. Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => '¡Producto añadido al carrito!',
        'cart_count' => $cart_count
    ]);

} catch (PDOException $e) {
    error_log("Error en api_add_to_cart: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
}
exit;