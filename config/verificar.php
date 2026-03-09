<?php
// public/views/verificar.php

$token = $_GET['token'] ?? '';
$mensaje = '';
$tipo_mensaje = ''; // 'success' o 'error'

if (!$token) {
    $mensaje = "Token de verificación no proporcionado.";
    $tipo_mensaje = 'error';
} else {
    try {
        // 1. Buscar el usuario con ese token y que NO esté activo aún
        $stmt = $pdo->prepare("SELECT id, nombre FROM usuarios WHERE token = ? AND activo = 0");
        $stmt->execute([$token]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            // 2. Activar usuario y limpiar el token
            $update = $pdo->prepare("UPDATE usuarios SET activo = 1, token = NULL WHERE id = ?");
            $update->execute([$usuario['id']]);

            $mensaje = "¡Cuenta verificada con éxito! Ya puedes iniciar sesión.";
            $tipo_mensaje = 'success';
        } else {
            // Verificar si ya estaba activo o el token es inválido
            $mensaje = "El enlace de verificación es inválido o la cuenta ya ha sido activada.";
            $tipo_mensaje = 'error';
        }
    } catch (PDOException $e) {
        error_log("Error en verificación: " . $e->getMessage());
        $mensaje = "Ocurrió un error al procesar la solicitud.";
        $tipo_mensaje = 'error';
    }
}
?>

<div class="min-h-[60vh] flex items-center justify-center bg-luxury-bone py-12 px-4">
    <div class="max-w-md w-full bg-white p-8 rounded-lg shadow-sm text-center">
        <?php if ($tipo_mensaje === 'success'): ?>
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check text-2xl text-green-600"></i>
            </div>
            <h2 class="text-2xl font-serif text-luxury-matte mb-2">¡Verificación Exitosa!</h2>
            <p class="text-gray-600 mb-6"><?= htmlspecialchars($mensaje) ?></p>
            <a href="index.php?page=login" class="inline-block bg-luxury-matte text-white px-6 py-3 text-xs uppercase tracking-widest font-bold hover:bg-luxury-gold transition-colors">
                Ir a Iniciar Sesión
            </a>
        <?php else: ?>
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-times text-2xl text-red-600"></i>
            </div>
            <h2 class="text-2xl font-serif text-luxury-matte mb-2">Error de Verificación</h2>
            <p class="text-gray-600 mb-6"><?= htmlspecialchars($mensaje) ?></p>
            <a href="index.php" class="text-luxury-gold hover:underline text-sm">Volver al inicio</a>
        <?php endif; ?>
    </div>
</div>