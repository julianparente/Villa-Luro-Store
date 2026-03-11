<?php
// public/views/admin_promociones.php

$precio_combo = 30000;
// La lógica de acciones se ha movido a index.php para evitar errores de "headers already sent".
// Las variables $error y $mensaje se declaran en index.php.
// Obtener productos en promoción
$promo_perfumes = $pdo->query("SELECT p.id, p.nombre, p.imagen_url, m.nombre as marca_nombre FROM perfumes p JOIN marcas m ON p.marca_id = m.id WHERE p.en_promocion = 1")->fetchAll();
$status = $_GET['status'] ?? '';
?>

<header class="mb-10">
    <h1 class="text-4xl font-bold text-gray-800">Gestión de la Oferta Semanal</h1>
    <p class="text-gray-500 mt-2">Administre los 3 perfumes que componen el combo especial.</p>
</header>

<!-- Mensajes de estado -->
<?php if ($status === 'removed'): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">Perfume quitado de la promoción correctamente.</div>
<?php endif; ?>

<?php if (isset($error) && $error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Info del Combo -->
    <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 sticky top-6 text-center">
            <h2 class="text-xl font-bold text-gray-800 mb-2">Combo Semanal</h2>
            <p class="font-serif text-5xl text-luxury-gold mb-4"><?= format_currency($precio_combo) ?></p>
            <p class="text-sm text-gray-500 mb-4">Este es el precio final para los clientes que añadan los 3 perfumes seleccionados al carrito.</p>
            <a href="index.php?page=admin_productos" class="w-full block bg-luxury-matte text-white px-4 py-3 rounded-lg shadow-md hover:bg-black transition-colors font-semibold text-sm">
                <i class="fas fa-plus mr-2"></i> Añadir/Editar Perfumes
            </a>
        </div>
    </div>

    <!-- Lista de Perfumes en Promo -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50 font-semibold text-gray-700">
                Perfumes actualmente en la oferta (<?= count($promo_perfumes) ?>/3)
            </div>
            <?php if (empty($promo_perfumes)): ?>
                <div class="p-16 text-center text-gray-500">
                    <i class="fas fa-tags fa-3x text-gray-300 mb-4"></i>
                    <p class="font-semibold">No hay perfumes en la oferta semanal.</p>
                    <p class="text-sm mt-1">Vaya a la <a href="index.php?page=admin_productos" class="text-blue-600 hover:underline">gestión de perfumes</a> para añadir productos a la promoción.</p>
                </div>
            <?php else: ?>
                <table class="w-full text-left">
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($promo_perfumes as $perfume): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-4 w-20">
                                    <img src="<?= htmlspecialchars($perfume['imagen_url']) ?>" class="w-16 h-16 object-cover rounded border border-gray-200">
                                </td>
                                <td class="p-4">
                                    <p class="font-bold text-gray-800"><?= htmlspecialchars($perfume['nombre']) ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($perfume['marca_nombre']) ?></p>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="index.php?page=admin_promociones&action=remove&id=<?= $perfume['id'] ?>" class="text-red-500 hover:text-red-700" title="Quitar de la promoción">
                                        <i class="fas fa-times-circle fa-lg"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>