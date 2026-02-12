<?php
// public/views/restablecer-password.php

$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);
$error = '';
$mensaje = '';
$show_form = false;

if (!$token) {
    $error = "Token no válido o ausente.";
} else {
    $token_hash = hash('sha256', $token);
    
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ?");
    $stmt->execute([$token_hash]);
    $reset_request = $stmt->fetch();

    // El token es válido si existe y fue creado en la última hora (3600 segundos)
    if ($reset_request && (strtotime($reset_request['created_at']) > (time() - 3600))) {
        $show_form = true;
    } else {
        $error = "El enlace de recuperación es inválido o ha expirado. Por favor, solicite uno nuevo.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $show_form) {
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (!$password || !$password2) {
        $error = "Ambos campos de contraseña son obligatorios.";
    } elseif (strlen($password) < 6) {
        $error = "La nueva contraseña debe tener al menos 6 caracteres.";
    } elseif ($password !== $password2) {
        $error = "Las contraseñas no coinciden.";
    } else {
        // Todo correcto, procedemos a actualizar la contraseña
        $new_password_hash = password_hash($password, PASSWORD_DEFAULT);
        $email = $reset_request['email'];

        // Actualizar la contraseña en la tabla de usuarios
        $stmt_update = $pdo->prepare("UPDATE usuarios SET password = ? WHERE email = ?");
        $stmt_update->execute([$new_password_hash, $email]);

        // Eliminar el token para que no pueda ser reutilizado
        $stmt_delete = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt_delete->execute([$email]);

        $mensaje = "¡Tu contraseña ha sido restablecida con éxito!";
        $show_form = false; // Ocultar el formulario tras el éxito
    }
}
?>

<div class="min-h-[80vh] flex items-center justify-center bg-luxury-bone py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-lg shadow-sm">
        <div>
            <h2 class="mt-6 text-center text-3xl font-serif text-luxury-matte">
                Restablecer Contraseña
            </h2>
        </div>

        <?php if ($mensaje): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 text-sm" role="alert"><?= $mensaje ?></div>
            <div class="text-sm text-center">
                <a href="index.php?page=login" class="font-medium text-luxury-gold hover:underline">Ir a Iniciar Sesión</a>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 text-sm" role="alert"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($show_form): ?>
            <form class="mt-8 space-y-6" method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="password" class="sr-only">Nueva Contraseña</label>
                        <input id="password" name="password" type="password" required class="appearance-none rounded-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-luxury-gold focus:border-luxury-gold sm:text-sm" placeholder="Nueva Contraseña">
                    </div>
                    <div>
                        <label for="password2" class="sr-only">Confirmar Nueva Contraseña</label>
                        <input id="password2" name="password2" type="password" required class="appearance-none rounded-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-luxury-gold focus:border-luxury-gold sm:text-sm" placeholder="Confirmar Nueva Contraseña">
                    </div>
                </div>
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold uppercase tracking-widest rounded-md text-white bg-luxury-matte hover:bg-luxury-gold hover:text-luxury-matte focus:outline-none transition-colors">
                    Guardar Nueva Contraseña
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>