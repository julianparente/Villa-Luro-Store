<?php
// public/views/admin_producto_form.php

// La lógica de POST se ha movido a index.php para evitar errores de "headers already sent".
// Las variables $error y $form_data_on_error (si existe) vienen de index.php.

$is_edit_mode = isset($_GET['id']);
$perfume_id = $is_edit_mode ? (int)$_GET['id'] : null;

// Estructura por defecto
$perfume = [
    'nombre' => '', 'marca_id' => '', 'precio' => '', 'stock' => '', 
    'categoria' => 'unisex', 'descripcion' => '', 'imagen_url' => '', 'en_promocion' => 0, 'precio_lista' => ''
];

// Obtener marcas para el dropdown
$marcas = $pdo->query("SELECT id, nombre FROM marcas ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

// Si hubo un error en el POST, repoblar el formulario con los datos enviados.
if (isset($form_data_on_error)) {
    $perfume = array_merge($perfume, $form_data_on_error);
} 
// Si no, si estamos en modo edición, cargar los datos desde la BD.
elseif ($is_edit_mode) {
    $stmt = $pdo->prepare("SELECT * FROM perfumes WHERE id = ?");
    $stmt->execute([$perfume_id]);
    $db_perfume = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$db_perfume) {
        redirect('index.php?page=admin_productos');
    }
    $perfume = $db_perfume;
}
?>

<header class="mb-10">
    <h1 class="text-4xl font-bold text-gray-800"><?= $is_edit_mode ? 'Editar Perfume' : 'Nuevo Perfume' ?></h1>
    <p class="text-gray-500 mt-2">Complete los detalles del producto a continuación.</p>
</header>

<?php if (isset($error) && $error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert"><?= $error ?></div>
<?php endif; ?>

<div class="bg-white p-8 rounded-lg shadow-sm border border-gray-200">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <?php if ($is_edit_mode): ?>
            <input type="hidden" name="id" value="<?= $perfume_id ?>">
        <?php endif; ?>
        <input type="hidden" name="current_imagen_url" value="<?= htmlspecialchars($perfume['imagen_url']) ?>">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Columna Izquierda -->
            <div class="space-y-6">
                <div>
                    <label for="nombre" class="block text-sm font-bold text-gray-700 mb-1">Nombre del Producto</label>
                    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($perfume['nombre']) ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-luxury-matte">
                </div>
                <div>
                    <label for="marca_id" class="block text-sm font-bold text-gray-700 mb-1">Marca</label>
                    <select id="marca_id" name="marca_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-luxury-matte">
                        <option value="">Seleccione una marca</option>
                        <?php foreach ($marcas as $marca): ?>
                            <option value="<?= $marca['id'] ?>" <?= ($perfume['marca_id'] ?? '') == $marca['id'] ? 'selected' : '' ?>><?= htmlspecialchars($marca['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div>
                        <label for="precio-lista" class="block text-sm font-bold text-gray-700 mb-1">Precio Lista</label>
                        <input type="number" step="0.01" id="precio-lista" name="precio_lista" value="<?= htmlspecialchars((string)(($perfume['precio_lista'] ?? null) ?: '20000')) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-luxury-matte">
                    </div>
                    <div>
                        <label for="porcentaje-off" class="block text-sm font-bold text-gray-700 mb-1">% OFF</label>
                        <input type="number" id="porcentaje-off" value="<?= htmlspecialchars((string)($perfume['porcentaje_off'] ?? '25')) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-luxury-matte">
                    </div>
                    <div>
                        <label for="precio-final" class="block text-sm font-bold text-gray-700 mb-1">Precio Final</label>
                        <input type="number" step="0.01" id="precio-final" name="precio" value="<?= htmlspecialchars((string)(($perfume['precio'] ?? null) ?: '15000')) ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-luxury-gold font-bold text-luxury-gold">
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="stock" class="block text-sm font-bold text-gray-700 mb-1">Stock</label>
                        <input type="number" id="stock" name="stock" value="<?= htmlspecialchars($perfume['stock']) ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-luxury-matte">
                    </div>
                </div>
                <div>
                    <label for="categoria" class="block text-sm font-bold text-gray-700 mb-1">Categoría</label>
                    <select id="categoria" name="categoria" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-luxury-matte">
                        <option value="masculino" <?= ($perfume['categoria'] ?? '') == 'masculino' ? 'selected' : '' ?>>Masculino</option>
                        <option value="femenino" <?= ($perfume['categoria'] ?? '') == 'femenino' ? 'selected' : '' ?>>Femenino</option>
                        <option value="unisex" <?= ($perfume['categoria'] ?? '') == 'unisex' ? 'selected' : '' ?>>Unisex</option>
                    </select>
                </div>
                 <div>
                    <label for="descripcion" class="block text-sm font-bold text-gray-700 mb-1">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-luxury-matte"><?= htmlspecialchars($perfume['descripcion']) ?></textarea>
                </div>
                <div class="border-t border-gray-200 pt-6">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="en_promocion" value="1" class="h-5 w-5 text-luxury-gold rounded border-gray-300 focus:ring-luxury-gold" <?= !empty($perfume['en_promocion']) ? 'checked' : '' ?>>
                        <span class="font-bold text-gray-700">Pertenece a la Oferta Semanal</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-2 ml-8">Marque esta casilla para incluir el perfume en el combo de 3 por $30.000.</p>
                </div>
            </div>

            <!-- Columna Derecha -->
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Imagen del Producto</label>
                    <div class="w-full p-4 border-2 border-dashed border-gray-300 rounded-lg text-center">
                        <?php if (!empty($perfume['imagen_url'])): ?>
                            <img src="<?= htmlspecialchars($perfume['imagen_url']) ?>" alt="Imagen actual" class="h-40 mx-auto mb-4 rounded-md">
                        <?php endif; ?>
                        <label for="imagen_file" class="cursor-pointer text-blue-600 hover:underline">Subir un archivo</label>
                        <input type="file" id="imagen_file" name="imagen_file" class="hidden">
                        <p class="text-xs text-gray-500 mt-1">O pegue una URL a continuación.</p>
                    </div>
                </div>
                <div>
                    <label for="imagen_url_input" class="block text-sm font-bold text-gray-700 mb-1">URL de la Imagen</label>
                    <input type="text" id="imagen_url_input" name="imagen_url_input" placeholder="https://ejemplo.com/imagen.jpg" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-luxury-matte">
                </div>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end gap-4">
            <a href="index.php?page=admin_productos" class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-300 transition-colors font-semibold">Cancelar</a>
            <button type="submit" class="bg-luxury-matte text-white px-8 py-2.5 rounded-lg shadow-md hover:bg-black transition-colors font-semibold">
                <i class="fas fa-save mr-2"></i> <?= $is_edit_mode ? 'Guardar Cambios' : 'Crear Producto' ?>
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const precioListaInput = document.getElementById('precio-lista');
    const porcentajeOffInput = document.getElementById('porcentaje-off');
    const precioFinalInput = document.getElementById('precio-final');

    function calcularPrecioFinal() {
        const precioLista = parseFloat(precioListaInput.value) || 0;
        const descuento = parseFloat(porcentajeOffInput.value) || 0;

        if (precioLista > 0) {
            const precioFinal = precioLista * (1 - (descuento / 100));
            precioFinalInput.value = precioFinal.toFixed(2);
        }
    }

    function calcularDescuento() {
        const precioLista = parseFloat(precioListaInput.value) || 0;
        const precioFinal = parseFloat(precioFinalInput.value) || 0;

        if (precioLista > 0 && precioFinal > 0 && precioFinal < precioLista) {
            const descuento = (1 - (precioFinal / precioLista)) * 100;
            porcentajeOffInput.value = descuento.toFixed(2);
        } else {
            porcentajeOffInput.value = 0;
        }
    }

    // Listeners
    precioListaInput.addEventListener('input', calcularPrecioFinal);
    porcentajeOffInput.addEventListener('input', calcularPrecioFinal);
    precioFinalInput.addEventListener('input', calcularDescuento);

    // Cálculo inicial al cargar la página para sincronizar los valores.
    // Si hay un precio de lista y un precio final, calculamos el descuento real.
    // De lo contrario, calculamos el precio final a partir del descuento por defecto.
    if (parseFloat(precioListaInput.value) > 0 && parseFloat(precioFinalInput.value) > 0) {
        calcularDescuento();
    } else {
        calcularPrecioFinal();
    }
});
</script>