<?php
// public/views/mi-cuenta.php

// La seguridad y la carga de datos del usuario ahora se manejan en index.php.
// La variable $user ya está disponible y validada en este punto.

// 2. OBTENER DATOS INICIALES
$usuario_id = $_SESSION['usuario_id'];
$section = $_GET['section'] ?? 'datos'; // Sección por defecto: datos, pedidos

// 3. LÓGICA DE FORMULARIOS (POST)
$mensaje = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Actualizar datos personales
    if (isset($_POST['update_details'])) {
        $nombre = trim($_POST['nombre'] ?? '');
        if (!$nombre) {
            $error = 'El nombre es obligatorio.';
        } else {
            // El email no se puede cambiar, solo actualizamos el nombre.
            $stmt_update = $pdo->prepare("UPDATE usuarios SET nombre = ? WHERE id = ?");
            $stmt_update->execute([$nombre, $usuario_id]);
            $user['nombre'] = $nombre; // Actualizar datos en la vista
            $mensaje = 'Su nombre ha sido actualizado correctamente.';
        }
        $section = 'datos'; // Mantenerse en la sección de datos
    }

    // Actualizar contraseña
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (!$current_password || !$new_password || !$confirm_password) {
            $error = 'Todos los campos de contraseña son obligatorios.';
        } elseif (!password_verify($current_password, $user['password'])) {
            $error = 'La contraseña actual es incorrecta.';
        } elseif (strlen($new_password) < 6) {
            $error = 'La nueva contraseña debe tener al menos 6 caracteres.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'La nueva contraseña y su confirmación no coinciden.';
        } else {
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt_pass_update = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
            $stmt_pass_update->execute([$new_hash, $usuario_id]);
            $mensaje = 'Su contraseña ha sido cambiada con éxito.';
        }
        $section = 'datos'; // Mantenerse en la sección de datos
    }
}

// 4. OBTENER DATOS PARA LAS VISTAS
$pedidos = [];

// Obtener pedidos (para dashboard y sección de pedidos)
if ($section === 'pedidos') {
    $stmt_pedidos = $pdo->prepare("SELECT id, fecha, total, estado FROM pedidos WHERE usuario_id = ? ORDER BY fecha DESC");
    $stmt_pedidos->execute([$usuario_id]);
    $pedidos = $stmt_pedidos->fetchAll();
}
?>

<div class="bg-luxury-bone min-h-[80vh]">
    <div class="container mx-auto py-16 px-6">
        <!-- Encabezado de Bienvenida -->
        <div class="mb-12">
            <h1 class="font-serif text-4xl md:text-5xl text-luxury-matte">Hola, <?= htmlspecialchars(explode(' ', $user['nombre'])[0]) ?></h1>
            <p class="text-gray-500 mt-2 font-light">Bienvenido a su panel personal. Desde aquí puede gestionar sus pedidos y datos.</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-12">
            <!-- Barra Lateral de Navegación -->
            <aside class="lg:w-1/4">
                <div class="sticky top-28 bg-white p-6 shadow-sm border border-gray-100 rounded-lg">
                    <nav class="space-y-1">
                        <a href="index.php?page=mi-cuenta&section=datos" class="flex items-center px-4 py-3 text-sm font-semibold rounded-md transition-colors <?= $section === 'datos' ? 'bg-luxury-matte text-white' : 'text-gray-600 hover:bg-gray-100' ?>">
                            <i class="fas fa-user-edit w-6 text-center opacity-75"></i>
                            <span>Mis Datos</span>
                        </a>
                        <a href="index.php?page=mi-cuenta&section=pedidos" class="flex items-center px-4 py-3 text-sm font-semibold rounded-md transition-colors <?= $section === 'pedidos' ? 'bg-luxury-matte text-white' : 'text-gray-600 hover:bg-gray-100' ?>">
                            <i class="fas fa-box w-6 text-center opacity-75"></i>
                            <span>Mis Pedidos</span>
                        </a>
                        <div class="border-t border-gray-100 my-2 !mt-4"></div>
                        <a href="index.php?page=logout" class="flex items-center px-4 py-3 text-sm font-semibold text-red-500 hover:bg-red-50 rounded-md transition-colors">
                            <i class="fas fa-sign-out-alt w-6 text-center opacity-75"></i>
                            <span>Cerrar Sesión</span>
                        </a>
                    </nav>
                </div>
            </aside>

            <!-- Área de Contenido Principal -->
            <main class="lg:w-3/4">
                <div class="bg-white p-8 md:p-12 shadow-sm border border-gray-100 rounded-lg">

                    <?php if ($mensaje): ?>
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 text-sm mb-8" role="alert"><?= $mensaje ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 text-sm mb-8" role="alert"><?= $error ?></div>
                    <?php endif; ?>

                    <?php if ($section === 'pedidos'): ?>
                        <h2 class="font-serif text-3xl mb-8">Historial de Pedidos</h2>
                        <?php if (empty($pedidos)): ?>
                            <div class="text-center py-16 border border-dashed border-gray-200 rounded-lg">
                                <i class="fas fa-receipt text-5xl text-gray-300 mb-4"></i>
                                <p class="text-gray-500 font-light mb-6">No tiene pedidos en su historial.</p>
                                <a href="index.php?page=catalogo" class="bg-luxury-matte text-white px-8 py-3 text-xs uppercase tracking-widest font-bold hover:bg-luxury-gold transition-colors">Explorar Catálogo</a>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="border-b border-gray-200">
                                        <tr>
                                            <th class="py-3 px-4 font-semibold text-gray-500 uppercase tracking-wider text-xs">Pedido</th>
                                            <th class="py-3 px-4 font-semibold text-gray-500 uppercase tracking-wider text-xs">Fecha</th>
                                            <th class="py-3 px-4 font-semibold text-gray-500 uppercase tracking-wider text-xs">Estado</th>
                                            <th class="py-3 px-4 font-semibold text-gray-500 uppercase tracking-wider text-xs text-right">Total</th>
                                            <th class="py-3 px-4"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pedidos as $pedido): ?>
                                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                                            <td class="py-4 px-4 font-semibold">#<?= $pedido['id'] ?></td>
                                            <td class="py-4 px-4 text-gray-600"><?= date('d M, Y', strtotime($pedido['fecha'])) ?></td>
                                            <td class="py-4 px-4">
                                                <span class="px-3 py-1 text-xs font-bold uppercase rounded-full <?= $pedido['estado'] === 'Enviado' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                                    <?= htmlspecialchars($pedido['estado']) ?>
                                                </span>
                                            </td>
                                            <td class="py-4 px-4 text-right font-semibold">$<?= number_format($pedido['total'], 2) ?></td>
                                            <td class="py-4 px-4 text-right">
                                                <a href="index.php?page=historial-pedidos&id=<?= $pedido['id'] ?>" class="text-luxury-gold hover:underline text-xs font-bold">Ver Detalles</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                    <?php elseif ($section === 'datos'): ?>
                        <h2 class="font-serif text-3xl mb-8">Mis Datos Personales</h2>
                        <form method="post" class="mb-12 border-b border-gray-100 pb-12">
                            <input type="hidden" name="update_details" value="1">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="nombre" class="text-xs uppercase tracking-widest text-gray-500">Nombre Completo</label>
                                    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($user['nombre']) ?>" required class="w-full bg-transparent border-b border-gray-300 py-2 mt-1 text-sm focus:outline-none focus:border-luxury-gold transition-colors">
                                </div>
                                <div>
                                    <label for="email" class="text-xs uppercase tracking-widest text-gray-500">Correo Electrónico (no modificable)</label>
                                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full bg-gray-100 cursor-not-allowed border-b border-gray-300 py-2 mt-1 text-sm focus:outline-none" disabled>
                                </div>
                            </div>
                            <div class="mt-8 text-right">
                                <button type="submit" class="bg-luxury-matte text-luxury-gold px-8 py-3 text-xs uppercase tracking-widest font-bold hover:bg-black transition-colors">Guardar Cambios</button>
                            </div>
                        </form>

                        <h2 class="font-serif text-3xl mb-8">Cambiar Contraseña</h2>
                        <form method="post">
                            <input type="hidden" name="update_password" value="1">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="current_password" class="text-xs uppercase tracking-widest text-gray-500">Contraseña Actual</label>
                                    <input type="password" id="current_password" name="current_password" required class="w-full bg-transparent border-b border-gray-300 py-2 mt-1 text-sm focus:outline-none focus:border-luxury-gold transition-colors">
                                </div>
                                <div>
                                    <label for="new_password" class="text-xs uppercase tracking-widest text-gray-500">Nueva Contraseña</label>
                                    <input type="password" id="new_password" name="new_password" required class="w-full bg-transparent border-b border-gray-300 py-2 mt-1 text-sm focus:outline-none focus:border-luxury-gold transition-colors">
                                </div>
                                <div>
                                    <label for="confirm_password" class="text-xs uppercase tracking-widest text-gray-500">Confirmar Nueva Contraseña</label>
                                    <input type="password" id="confirm_password" name="confirm_password" required class="w-full bg-transparent border-b border-gray-300 py-2 mt-1 text-sm focus:outline-none focus:border-luxury-gold transition-colors">
                                </div>
                            </div>
                            <div class="mt-8 text-right">
                                <button type="submit" class="bg-luxury-matte text-luxury-gold px-8 py-3 text-xs uppercase tracking-widest font-bold hover:bg-black transition-colors">Cambiar Contraseña</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</div>
