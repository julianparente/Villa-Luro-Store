<?php
// carrito.php — Página del carrito de compras
// Permite actualizar cantidades, eliminar productos y ver el total

// public/views/carrito.php

// Mensaje
$mensajeCarrito = '';

// --- INICIO: Lógica de POST ---
// Se mueve al principio para que los cambios se reflejen en la misma carga de página.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        $cantidad = max(1, (int)$_POST['cantidad']);
        $item_id = (int)$_POST['item_id'];
        if (isLoggedIn()) {
            $stmt = $pdo->prepare("UPDATE carrito SET cantidad = ? WHERE id = ?");
            $stmt->execute([$cantidad, $item_id]);
        } else {
            foreach ($_SESSION['carrito'] as &$item) {
                if ($item['perfume_id'] == $item_id) {
                    $item['cantidad'] = $cantidad;
                    break;
                }
            }
        }
        $mensajeCarrito = 'Cantidad actualizada correctamente.';
    }
    if (isset($_POST['delete'])) {
        $item_id = (int)$_POST['item_id'];
        if (isLoggedIn()) {
            $stmt = $pdo->prepare("DELETE FROM carrito WHERE id = ?");
            $stmt->execute([$item_id]);
        } else {
            $carrito_filtrado = array_filter($_SESSION['carrito'], function($item) use ($item_id) {
                // Se añade una comprobación para evitar warnings si el array está malformado.
                return isset($item['perfume_id']) && $item['perfume_id'] != $item_id;
            });
            $_SESSION['carrito'] = array_values($carrito_filtrado); // Re-indexar el array para evitar huecos.
        }
        $mensajeCarrito = 'Producto eliminado del carrito.';
    }
}
// --- FIN: Lógica de POST ---

$carrito = [];
$total = 0;

if (isLoggedIn()) {
    $usuario_id = $_SESSION['usuario_id'];
    $stmt = $pdo->prepare("SELECT c.id, c.cantidad, p.nombre, p.precio, p.imagen_url, m.nombre AS marca_nombre FROM carrito c JOIN perfumes p ON c.perfume_id = p.id JOIN marcas m ON p.marca_id = m.id WHERE c.usuario_id = ?");
    $stmt->execute([$usuario_id]);
    $carrito = $stmt->fetchAll();
} elseif (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $stmt = $pdo->prepare("SELECT p.id, p.nombre, p.precio, p.imagen_url, m.nombre AS marca_nombre FROM perfumes p JOIN marcas m ON p.marca_id = m.id WHERE p.id = ?");
        $stmt->execute([$item['perfume_id']]);
        $perfume = $stmt->fetch();
        if ($perfume) {
            $perfume['cantidad'] = $item['cantidad'];
            $carrito[] = $perfume;
        }
    }
}

// Calcular total
foreach ($carrito as $item) {
    $total += $item['precio'] * $item['cantidad'];
}
?>
<div class="bg-white">
<div class="container mx-auto py-24 px-6">
    <?php if ($mensajeCarrito): ?>
        <div class="alert-success" id="carrito-msg">
            <?= $mensajeCarrito ?>
        </div>
    <?php endif; ?>
    
    <h1 class="text-5xl font-serif mb-16">Su Selección</h1>
    <?php if (empty($carrito)): ?>
        <div class="py-24 text-center border-y border-gray-200">
            <i class="fas fa-shopping-bag text-6xl text-gray-300 mb-6"></i>
            <p class="font-serif text-2xl text-gray-400 italic mb-8">Su carrito está actualmente vacío.</p>
            <a href="index.php?page=catalogo" class="inline-block bg-luxury-matte text-white px-10 py-4 text-[10px] uppercase tracking-widest font-bold hover:bg-luxury-gold transition-colors duration-300">Explorar Colección</a>
        </div>
    <?php else: ?>
        <div class="flex flex-col lg:flex-row gap-12 lg:gap-20">
            <!-- Lista de Productos -->
            <div class="lg:w-[60%]">
                <div class="border-b border-gray-200 pb-2 mb-6">
                    <div class="grid grid-cols-5 text-gray-400 uppercase text-[10px] tracking-widest font-bold">
                        <div class="col-span-2">Producto</div>
                        <div class="text-center">Cantidad</div>
                        <div class="text-right">Total</div>
                        <div></div>
                    </div>
                </div>
                <div class="space-y-8">
                    <?php foreach ($carrito as $item): ?>
                        <div class="grid grid-cols-5 items-center gap-4 group">
                            <!-- Producto -->
                            <div class="col-span-2 flex items-center gap-4">
                                <div class="w-24 h-32 bg-gray-100 overflow-hidden">
                                    <img src="<?= htmlspecialchars($item['imagen_url']) ?>" alt="<?= htmlspecialchars($item['nombre']) ?>" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <p class="font-serif text-lg"><?= htmlspecialchars($item['nombre']) ?></p>
                                    <p class="text-sm text-gray-500">$<?= number_format($item['precio'], 2) ?></p>
                                </div>
                            </div>
                            <!-- Cantidad -->
                            <div class="text-center">
                                <form method="post" class="flex items-center justify-center border border-gray-200 w-24 mx-auto">
                                    <input type="hidden" name="item_id" value="<?= isLoggedIn() ? $item['id'] : $item['id'] ?>">
                                    <input type="number" name="cantidad" value="<?= $item['cantidad'] ?>" min="1" class="w-10 text-center text-sm py-2 focus:outline-none">
                                    <button type="submit" name="update" class="px-3 py-2 text-[10px] uppercase font-bold text-gray-400 hover:text-luxury-gold transition-colors border-l border-gray-200">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </form>
                            </div>
                            <!-- Total -->
                            <div class="text-right font-semibold">
                                $<?= number_format($item['precio'] * $item['cantidad'], 2) ?>
                            </div>
                            <!-- Eliminar -->
                            <div class="text-right">
                                <form method="post">
                                    <input type="hidden" name="item_id" value="<?= isLoggedIn() ? $item['id'] : $item['id'] ?>">
                                    <button type="submit" name="delete" class="text-gray-300 hover:text-red-500 transition-colors text-sm">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Resumen de Pedido -->
            <div class="lg:w-[40%]">
                <div class="bg-luxury-bone p-10 sticky top-32">
                    <h3 class="font-serif text-3xl mb-8 border-b border-gray-200 pb-4">Resumen del Pedido</h3>
                    <div class="space-y-4 mb-10 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Subtotal</span>
                            <span class="font-semibold">$<?= number_format($total, 2) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Envío</span>
                            <span class="text-luxury-gold uppercase text-[10px] font-bold">Gratis</span>
                        </div>
                        <div class="border-t border-gray-200 pt-4 flex justify-between text-xl font-bold font-serif">
                            <span class="tracking-wider">Total</span>
                            <span class="text-luxury-gold tracking-wider">$<?= number_format($total, 2) ?></span>
                        </div>
                    </div>
                    <form method="get" action="index.php">
                        <input type="hidden" name="page" value="finalizar-compra">
                        <button type="submit" class="w-full bg-luxury-matte text-white py-4 text-[10px] uppercase tracking-[0.2em] font-bold hover:bg-luxury-gold transition-all duration-500">Finalizar Compra</button>
                    </form>
                    <p class="text-xs text-gray-400 mt-6 text-center">Aceptamos todas las tarjetas de crédito y débito.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
</div>
