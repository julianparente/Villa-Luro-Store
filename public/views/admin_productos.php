<?php
// public/views/admin_productos.php

// 2. Verificación de Consultas (Debug Mode)
try {
    // Búsqueda
    $search = $_GET['search'] ?? '';
    
    // 3. Mapeo de Columnas: Esta consulta usa columnas explícitas. Si falla, el bloque catch mostrará el error.
    // Asume una tabla 'perfumes' con 'marca_id' y una tabla 'marcas' con 'id' y 'nombre'.
    $query = "SELECT p.id, p.nombre, p.precio, p.stock, p.imagen_url, m.nombre as marca_nombre FROM perfumes p JOIN marcas m ON p.marca_id = m.id";
    $params = [];
    if ($search) {
        $query .= " WHERE p.nombre LIKE ? OR m.nombre LIKE ?";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    $query .= " ORDER BY p.id DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $perfumes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Si la consulta falla, se mostrará el error SQL exacto en pantalla.
    error_log("Error SQL en admin_productos: " . $e->getMessage());
    echo "<div class='bg-red-100 text-red-700 p-4 m-4 rounded'>Ocurrió un error al cargar los productos. Por favor, revise los registros del servidor.</div>";
}
?>
<header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
    <div>
        <h1 class="text-4xl font-bold text-gray-800">Gestión de Perfumes</h1>
        <p class="text-gray-500 mt-2">Añada, edite o elimine productos del catálogo.</p>
    </div>
    <div class="flex items-center gap-4 w-full md:w-auto">
        <form method="GET" class="relative flex-grow md:flex-grow-0">
            <input type="hidden" name="page" value="admin_productos">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar por nombre o marca..." class="w-full md:w-64 px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-luxury-matte">
            <i class="fas fa-search absolute right-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
        </form>
        <a href="index.php?page=admin_producto_form" class="bg-luxury-matte text-white px-6 py-2.5 rounded-lg shadow-md hover:bg-black transition-colors whitespace-nowrap font-semibold">
            <i class="fas fa-plus mr-2"></i> Nuevo Perfume
        </a>
    </div>
</header>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
    <table class="w-full text-left">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
            <tr>
                <th class="px-6 py-3">Imagen</th>
                <th class="px-6 py-3">Producto</th>
                <th class="px-6 py-3">Marca</th>
                <th class="px-6 py-3">Precio</th>
                <th class="px-6 py-3">Stock</th>
                <th class="px-6 py-3 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="text-sm">
             <?php if (empty($perfumes)): ?>
                <tr><td colspan="6" class="px-6 py-16 text-center text-gray-500">
                    <div class="text-center">
                        <i class="fas fa-search fa-3x text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-semibold">
                            <?php if ($search): ?>
                                No se encontraron productos para "<?= htmlspecialchars($search) ?>".
                            <?php else: ?>
                                No hay productos cargados.
                            <?php endif; ?>
                        </h3>
                        <p class="text-sm text-gray-400 mt-1">
                            <?php if (!$search): ?>
                                <a href="index.php?page=admin_producto_form" class="text-blue-600 hover:underline font-semibold">Agregue el primer producto</a> para comenzar.
                            <?php else: ?>
                                Intente con otra búsqueda o agregue un nuevo producto.
                            <?php endif; ?>
                        </p>
                    </div>
                </td></tr>
            <?php else: foreach($perfumes as $index => $perfume): ?>
                <tr class="<?= $index % 2 === 0 ? 'bg-white' : 'bg-gray-50/50' ?> hover:bg-yellow-50/50 transition-colors">
                    <td class="px-6 py-4"><img src="<?= htmlspecialchars($perfume['imagen_url']) ?>" alt="<?= htmlspecialchars($perfume['nombre']) ?>" class="h-16 w-16 object-cover rounded-md border border-gray-200"></td>
                    <td class="px-6 py-4 font-semibold text-gray-800"><?= htmlspecialchars($perfume['nombre']) ?></td>
                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($perfume['marca_nombre']) ?></td>
                    <td class="px-6 py-4 text-gray-600 font-medium"><?= format_currency($perfume['precio']) ?></td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold <?= $perfume['stock'] > 10 ? 'bg-green-100 text-green-800' : ($perfume['stock'] > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                            <?= $perfume['stock'] ?> unidades
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-4">
                            <a href="index.php?page=admin_producto_form&id=<?= $perfume['id'] ?>" class="text-gray-500 hover:text-blue-600 transition-colors" title="Editar"><i class="fas fa-pencil-alt fa-lg"></i></a>
                            <a href="index.php?page=admin_producto_delete&id=<?= $perfume['id'] ?>&token=<?= generate_csrf_token() ?>" class="text-gray-500 hover:text-red-600 transition-colors delete-btn" title="Eliminar"><i class="fas fa-trash-alt fa-lg"></i></a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            Swal.fire({
                title: '¿Está seguro?',
                text: "¡Esta acción no se puede revertir!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#1A1A1A',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, ¡eliminar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        });
    });
});
</script>