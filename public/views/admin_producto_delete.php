<?php
// public/views/admin_producto_delete.php

$perfume_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$perfume_id) {
    header('Location: index.php?page=admin_productos&status=error');
    exit;
}

try {
    // 1. Obtener la URL de la imagen antes de borrar el registro
    $stmt_img = $pdo->prepare("SELECT imagen_url FROM perfumes WHERE id = ?");
    $stmt_img->execute([$perfume_id]);
    $imagen_url = $stmt_img->fetchColumn();

    // 2. Eliminar el registro de la base de datos
    $stmt = $pdo->prepare("DELETE FROM perfumes WHERE id = ?");
    $stmt->execute([$perfume_id]);

    // 3. Si la imagen era un archivo local, eliminarlo del servidor
    if ($imagen_url && file_exists($imagen_url) && strpos($imagen_url, 'public/img/perfumes/') === 0) {
        unlink($imagen_url);
    }

    header('Location: index.php?page=admin_productos&status=deleted');
    exit;

} catch (PDOException $e) {
    // En un entorno real, registraríamos el error: error_log($e->getMessage());
    header('Location: index.php?page=admin_productos&status=error');
    exit;
}