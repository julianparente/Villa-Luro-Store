<?php
// public/views/admin_pedidos.php

// 1. Manejo de actualización de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $pedido_id = (int)$_POST['pedido_id'];
    $nuevo_estado = $_POST['estado'];
    
    try {
        $stmt = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        $stmt->execute([$nuevo_estado, $pedido_id]);
        $success_msg = "Estado del pedido #$pedido_id actualizado a '$nuevo_estado'.";
        
        // Si estamos en la vista de detalle, recargamos con el mensaje
        if (isset($_GET['action']) && $_GET['action'] === 'view') {
             header("Location: index.php?page=admin_pedidos&action=view&id=$pedido_id&msg=" . urlencode($success_msg));
             exit;
        }
    } catch (PDOException $e) {
        $error_msg = "Error al actualizar: " . $e->getMessage();
    }
}

$msg = $_GET['msg'] ?? null;

// 2. Vista de Detalle de Pedido
if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ?");
    $stmt->execute([$id]);
    $pedido = $stmt->fetch();

    if (!$pedido) {
        echo "<div class='bg-red-100 text-red-700 p-4 rounded'>Pedido no encontrado.</div>";
    } else {
        // Obtener items
        $stmtItems = $pdo->prepare("
            SELECT pi.*, p.nombre as perfume_nombre, p.imagen_url, m.nombre as marca_nombre 
            FROM pedido_items pi 
            JOIN perfumes p ON pi.perfume_id = p.id 
            JOIN marcas m ON p.marca_id = m.id 
            WHERE pi.pedido_id = ?
        ");
        $stmtItems->execute([$id]);
        $items = $stmtItems->fetchAll();
?>
    <div class="mb-6">
        <a href="index.php?page=admin_pedidos" class="text-gray-500 hover:text-luxury-matte flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Volver a la lista
        </a>
    </div>

    <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Pedido #<?= $pedido['id'] ?></h1>
            <p class="text-gray-500 mt-1">Realizado el <?= date('d/m/Y H:i', strtotime($pedido['fecha'])) ?></p>
        </div>
        
        <!-- Formulario de cambio de estado -->
        <form method="POST" class="flex items-center gap-2 bg-white p-2 rounded-lg shadow-sm border border-gray-200">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="pedido_id" value="<?= $pedido['id'] ?>">
            <label for="estado" class="text-sm font-semibold text-gray-600 pl-2">Estado:</label>
            <select name="estado" id="estado" class="border-none focus:ring-0 text-sm font-bold text-luxury-matte cursor-pointer bg-transparent">
                <?php 
                $estados = ['Pendiente de Pago', 'Pendiente', 'Procesando', 'Enviado', 'Entregado', 'Cancelado'];
                foreach ($estados as $est): 
                ?>
                    <option value="<?= $est ?>" <?= $pedido['estado'] === $est ? 'selected' : '' ?>><?= $est ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-luxury-matte text-white px-4 py-1 rounded text-xs uppercase font-bold hover:bg-luxury-gold transition-colors">Actualizar</button>
        </form>
    </header>

    <?php if ($msg): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Columna Izquierda: Items -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-100 bg-gray-50 font-semibold text-gray-700">Artículos</div>
                <table class="w-full text-left">
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="p-4 w-20">
                                    <img src="<?= htmlspecialchars($item['imagen_url']) ?>" class="w-16 h-16 object-cover rounded border border-gray-200">
                                </td>
                                <td class="p-4">
                                    <p class="font-bold text-gray-800"><?= htmlspecialchars($item['perfume_nombre']) ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($item['marca_nombre']) ?></p>
                                </td>
                                <td class="p-4 text-right text-sm">
                                    <?= $item['cantidad'] ?> x $<?= number_format($item['precio_unitario'], 2) ?>
                                </td>
                                <td class="p-4 text-right font-bold text-gray-800">
                                    $<?= number_format($item['cantidad'] * $item['precio_unitario'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="p-4 text-right font-bold text-gray-600">Total</td>
                            <td class="p-4 text-right font-bold text-xl text-luxury-gold">$<?= number_format($pedido['total'], 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Columna Derecha: Info Cliente -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-6">
                <h3 class="font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">Datos del Cliente</h3>
                
                <div class="space-y-4 text-sm">
                    <div>
                        <p class="text-gray-500 text-xs uppercase tracking-wider mb-1">Nombre</p>
                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($pedido['nombre']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase tracking-wider mb-1">Email</p>
                        <p class="text-gray-800"><a href="mailto:<?= htmlspecialchars($pedido['email']) ?>" class="hover:text-luxury-gold"><?= htmlspecialchars($pedido['email']) ?></a></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase tracking-wider mb-1">Dirección de Envío</p>
                        <p class="text-gray-800 leading-relaxed"><?= nl2br(htmlspecialchars($pedido['direccion'])) ?></p>
                    </div>
                    <?php if ($pedido['usuario_id']): ?>
                    <div class="pt-4 border-t border-gray-100">
                        <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">Usuario Registrado (ID: <?= $pedido['usuario_id'] ?>)</span>
                    </div>
                    <?php else: ?>
                    <div class="pt-4 border-t border-gray-100">
                        <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full">Invitado</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php
    }
} else {
    // 3. Vista de Lista de Pedidos
    $stmt = $pdo->query("SELECT * FROM pedidos ORDER BY fecha DESC");
    $pedidos = $stmt->fetchAll();
    
    $statusColors = [
        'Pendiente de Pago' => 'bg-orange-100 text-orange-800',
        'Pendiente' => 'bg-yellow-100 text-yellow-800',
        'Procesando' => 'bg-blue-100 text-blue-800',
        'Enviado' => 'bg-purple-100 text-purple-800',
        'Entregado' => 'bg-green-100 text-green-800',
        'Cancelado' => 'bg-red-100 text-red-800',
    ];
?>
    <header class="mb-10">
        <h1 class="text-4xl font-bold text-gray-800">Gestión de Pedidos</h1>
        <p class="text-gray-500 mt-2">Administre y procese los pedidos de la tienda.</p>
    </header>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
                <tr>
                    <th class="px-6 py-3">ID</th>
                    <th class="px-6 py-3">Fecha</th>
                    <th class="px-6 py-3">Cliente</th>
                    <th class="px-6 py-3">Estado</th>
                    <th class="px-6 py-3 text-right">Total</th>
                    <th class="px-6 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-gray-100">
                <?php foreach ($pedidos as $p): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-bold text-gray-700">#<?= $p['id'] ?></td>
                        <td class="px-6 py-4 text-gray-500"><?= date('d/m/Y', strtotime($p['fecha'])) ?></td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-800"><?= htmlspecialchars($p['nombre']) ?></div>
                            <div class="text-xs text-gray-400"><?= htmlspecialchars($p['email']) ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <?php $colorClass = $statusColors[$p['estado']] ?? 'bg-gray-100 text-gray-800'; ?>
                            <span class="px-2 py-1 rounded-full text-xs font-bold <?= $colorClass ?>">
                                <?= htmlspecialchars($p['estado']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-gray-700">$<?= number_format($p['total'], 2) ?></td>
                        <td class="px-6 py-4 text-center">
                            <a href="index.php?page=admin_pedidos&action=view&id=<?= $p['id'] ?>" class="text-luxury-matte hover:text-luxury-gold font-semibold text-xs uppercase tracking-wide border border-gray-300 px-3 py-1 rounded hover:border-luxury-gold transition-colors">
                                Ver Detalles
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($pedidos)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">No hay pedidos registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php } ?>