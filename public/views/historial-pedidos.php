<?php
// public/views/historial-pedidos.php
// Ahora funciona como una página de DETALLE de un pedido específico.

$pedido_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$pedido_id) {
    // Si no hay ID, redirigir a la vista general de pedidos en "Mi Cuenta"
    header('Location: index.php?page=mi-cuenta&section=pedidos');
    exit;
}

// Obtener detalles del pedido, asegurándose de que pertenece al usuario actual
$stmt_pedido = $pdo->prepare("SELECT * FROM pedidos WHERE id = ? AND usuario_id = ?");
$stmt_pedido->execute([$pedido_id, $_SESSION['usuario_id']]);
$pedido = $stmt_pedido->fetch();

// Si el pedido no existe o no pertenece al usuario, redirigir
if (!$pedido) {
    header('Location: index.php?page=mi-cuenta&section=pedidos');
    exit;
}

// Obtener los productos de ese pedido
$stmt_items = $pdo->prepare(
    "SELECT pi.cantidad, pi.precio_unitario, p.nombre, p.imagen_url, m.nombre as marca_nombre 
     FROM pedido_items pi 
     JOIN perfumes p ON pi.perfume_id = p.id 
     JOIN marcas m ON p.marca_id = m.id 
     WHERE pi.pedido_id = ?"
);
$stmt_items->execute([$pedido_id]);
$items = $stmt_items->fetchAll();
?>

<div class="bg-luxury-bone min-h-[80vh]">
    <div class="container mx-auto py-16 px-6">
        <div class="max-w-4xl mx-auto">
            <a href="index.php?page=mi-cuenta&section=pedidos" class="text-sm text-gray-500 hover:text-luxury-gold mb-8 inline-block">
                <i class="fas fa-arrow-left mr-2"></i> Volver a Mis Pedidos
            </a>

            <div class="bg-white p-8 md:p-12 shadow-sm border border-gray-100 rounded-lg">
                <!-- Cabecera del Pedido -->
                <div class="flex flex-col md:flex-row justify-between items-start border-b border-gray-200 pb-6 mb-8">
                    <div>
                        <h1 class="font-serif text-3xl md:text-4xl">Detalles del Pedido #<?= $pedido['id'] ?></h1>
                        <p class="text-sm text-gray-500 mt-2">Realizado el: <?= date('d \d\e F, Y', strtotime($pedido['fecha'])) ?></p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <span class="px-4 py-2 text-sm font-bold uppercase rounded-full <?= $pedido['estado'] === 'Enviado' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                            <?= htmlspecialchars($pedido['estado']) ?>
                        </span>
                    </div>
                </div>

                <!-- Lista de Productos -->
                <div class="mb-8">
                    <h2 class="font-serif text-2xl mb-6">Artículos del Pedido</h2>
                    <div class="space-y-6">
                        <?php foreach ($items as $item): ?>
                        <div class="flex items-center gap-6">
                            <div class="w-20 h-24 bg-gray-100 rounded-md overflow-hidden flex-shrink-0">
                                <img src="<?= htmlspecialchars($item['imagen_url']) ?>" alt="<?= htmlspecialchars($item['nombre']) ?>" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-grow">
                                <p class="font-semibold"><?= htmlspecialchars($item['nombre']) ?></p>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($item['marca_nombre']) ?></p>
                                <p class="text-sm text-gray-600 mt-1">
                                    <?= $item['cantidad'] ?> x <span class="font-semibold">$<?= number_format($item['precio_unitario'], 2) ?></span>
                                </p>
                            </div>
                            <div class="font-semibold text-right">
                                $<?= number_format($item['precio_unitario'] * $item['cantidad'], 2) ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Resumen y Datos de Envío -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 border-t border-gray-200 pt-8">
                    <div>
                        <h3 class="font-serif text-xl mb-4">Datos de Envío</h3>
                        <div class="text-sm text-gray-600 leading-relaxed">
                            <p class="font-semibold"><?= htmlspecialchars($pedido['nombre']) ?></p>
                            <p><?= htmlspecialchars($pedido['direccion']) ?></p>
                            <p><?= htmlspecialchars($pedido['email']) ?></p>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-serif text-xl mb-4">Resumen de Pago</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Subtotal</span>
                                <span>$<?= number_format($pedido['total'], 2) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Envío</span>
                                <span class="font-semibold">Gratis</span>
                            </div>
                            <div class="border-t border-gray-200 mt-2 pt-2 flex justify-between font-bold text-lg">
                                <span>Total</span>
                                <span>$<?= number_format($pedido['total'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
