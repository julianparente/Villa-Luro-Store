<?php
// public/views/admin_dashboard.php

// 1. Forzar Conexión Global y Verificación Estricta
if (!isset($pdo)) {
    $db_path = __DIR__ . '/../../config/db.php';
    if (file_exists($db_path)) {
        require_once $db_path;
    }
    // Verificación estricta de la conexión después de intentar incluir el archivo.
    if (!isset($pdo)) {
        die("<div style='font-family: sans-serif; padding: 2rem; margin: 2rem; background-color: #ffe3e3; border: 2px solid #b30000; color: #b30000;'><h1>Error Crítico de Conexión</h1><p>La variable de conexión a la base de datos <strong>\$pdo no existe</strong>. Esto significa que el archivo <code>config/db.php</code> no se pudo incluir o no está creando la conexión correctamente.</p></div>");
    }
}

// 2. Verificación de Consultas (Debug Mode)
try {
    // 3. Mapeo de Columnas: Las siguientes consultas usan nombres de columna estándar.
    // Si alguna falla, el bloque catch mostrará el error SQL exacto.
    $total_ventas = $pdo->query("SELECT SUM(total) FROM pedidos")->fetchColumn() ?? 0;
    $total_clientes = $pdo->query("SELECT COUNT(id) FROM usuarios WHERE id != 1")->fetchColumn() ?? 0;
    $total_productos = $pdo->query("SELECT COUNT(id) FROM perfumes")->fetchColumn() ?? 0;
    $total_suscripciones = $pdo->query("SELECT COUNT(id) FROM suscripciones")->fetchColumn() ?? 0;
    // Últimos Movimientos
    $ultimos_pedidos = $pdo->query("SELECT id, nombre, fecha, total, estado FROM pedidos ORDER BY fecha DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Si cualquier consulta falla, se mostrará el error SQL exacto en pantalla.
    die("<div style='font-family: sans-serif; padding: 2rem; margin: 2rem; background-color: #ffe3e3; border: 2px solid #b30000; color: #b30000;'><h1>Error de Consulta SQL</h1><p>La consulta a la base de datos falló. Este es el error devuelto por MySQL:</p><pre style='background-color: #fff; padding: 1rem; border: 1px solid #ccc; white-space: pre-wrap; word-wrap: break-word;'>" . htmlspecialchars($e->getMessage()) . "</pre></div>");
}
?>

<header class="mb-10">
    <h1 class="text-4xl font-bold text-gray-800">Resumen</h1>
    <p class="text-gray-500 mt-2">Una vista general del estado de su tienda.</p>
</header>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 flex items-center gap-6">
        <div class="bg-luxury-gold/10 text-luxury-gold p-4 rounded-full"><i class="fas fa-dollar-sign fa-2x"></i></div>
        <div>
            <p class="text-gray-500 text-sm font-semibold mb-1">Total de Ventas</p>
            <p class="text-3xl font-bold text-gray-800">$<?= number_format($total_ventas, 2) ?></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 flex items-center gap-6">
        <div class="bg-blue-100 text-blue-600 p-4 rounded-full"><i class="fas fa-users fa-2x"></i></div>
        <div>
            <p class="text-gray-500 text-sm font-semibold mb-1">Usuarios Registrados</p>
            <p class="text-3xl font-bold text-gray-800"><?= $total_clientes ?></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 flex items-center gap-6">
        <div class="bg-purple-100 text-purple-600 p-4 rounded-full"><i class="fas fa-wine-bottle fa-2x"></i></div>
        <div>
            <p class="text-gray-500 text-sm font-semibold mb-1">Productos en Catálogo</p>
            <p class="text-3xl font-bold text-gray-800"><?= $total_productos ?></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 flex items-center gap-6">
        <div class="bg-green-100 text-green-600 p-4 rounded-full"><i class="fas fa-envelope-open-text fa-2x"></i></div>
        <div>
            <p class="text-gray-500 text-sm font-semibold mb-1">Suscripciones</p>
            <p class="text-3xl font-bold text-gray-800"><?= $total_suscripciones ?></p>
        </div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <h3 class="font-bold text-lg text-gray-800">Últimos Movimientos</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
                <tr>
                    <th class="px-6 py-3">ID Pedido</th>
                    <th class="px-6 py-3">Cliente</th>
                    <th class="px-6 py-3">Fecha</th>
                    <th class="px-6 py-3">Estado</th>
                    <th class="px-6 py-3 text-right">Monto</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if (empty($ultimos_pedidos)): ?>
                    <tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">No hay pedidos recientes.</td></tr>
                <?php else: foreach($ultimos_pedidos as $index => $pedido): ?>
                    <tr class="<?= $index % 2 === 0 ? 'bg-white' : 'bg-gray-50/50' ?>">
                        <td class="px-6 py-4 font-semibold text-gray-700">#<?= $pedido['id'] ?></td>
                        <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($pedido['nombre']) ?></td>
                        <td class="px-6 py-4 text-gray-600"><?= date('d/m/Y', strtotime($pedido['fecha'])) ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs font-bold <?= $pedido['estado'] === 'Enviado' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                <?= htmlspecialchars($pedido['estado']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-gray-700">$<?= number_format($pedido['total'], 2) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>