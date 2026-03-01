<?php
// public/views/admin_producto_form.php

$is_edit_mode = isset($_GET['id']);
$perfume_id = $is_edit_mode ? (int)$_GET['id'] : null;
$perfume = [
    'nombre' => '', 'marca_id' => '', 'precio' => '', 'stock' => '', 
    'categoria' => 'unisex', 'descripcion' => '', 'imagen_url' => ''
];
$form_error = '';
$form_success = '';

// Obtener marcas para el dropdown
$marcas = $pdo->query("SELECT id, nombre FROM marcas ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

if ($is_edit_mode) {
    $stmt = $pdo->prepare("SELECT * FROM perfumes WHERE id = ?");
    $stmt->execute([$perfume_id]);
    $perfume = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$perfume) {
        // Si no se encuentra el perfume, redirigir
        redirect('index.php?page=admin_productos');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y sanear datos
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die("Error de seguridad: Token CSRF inválido.");
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $marca_id = (int)($_POST['marca_id'] ?? 0);
    $precio = filter_var($_POST['precio'] ?? 0, FILTER_VALIDATE_FLOAT);
    $stock = filter_var($_POST['stock'] ?? 0, FILTER_VALIDATE_INT);
    $categoria = in_array($_POST['categoria'], ['masculino', 'femenino', 'unisex']) ? $_POST['categoria'] : 'unisex';
    $descripcion = trim($_POST['descripcion'] ?? '');
    $imagen_url_input = trim($_POST['imagen_url_input'] ?? '');
    $imagen_url = $perfume['imagen_url']; // Mantener la imagen actual por defecto

    // Validación
    if (empty($nombre) || empty($marca_id) || $precio === false || $stock === false) {
        $form_error = 'Nombre, Marca, Precio y Stock son campos obligatorios y deben ser válidos.';
    } else {
        // Gestión de la imagen
        if (isset($_FILES['imagen_file']) && $_FILES['imagen_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'public/img/perfumes/';
            $upload_result = upload_image($_FILES['imagen_file'], $upload_dir);

            if ($upload_result['success']) {
                $imagen_url = $upload_result['path'];
            } else {
                $form_error = $upload_result['error'];
            }
        } elseif (!empty($imagen_url_input)) {
            $imagen_url = $imagen_url_input;
        }

        if (empty($form_error)) {
            try {
                if ($is_edit_mode) {
                    // Actualizar
                    $sql = "UPDATE perfumes SET nombre = ?, marca_id = ?, precio = ?, stock = ?, categoria = ?, descripcion = ?, imagen_url = ? WHERE id = ?";
                    $params = [$nombre, $marca_id, $precio, $stock, $categoria, $descripcion, $imagen_url, $perfume_id];
                } else {
                    // Insertar
                    $sql = "INSERT INTO perfumes (nombre, marca_id, precio, stock, categoria, descripcion, imagen_url) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $params = [$nombre, $marca_id, $precio, $stock, $categoria, $descripcion, $imagen_url];
                }
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                // Redirigir a la lista de productos
                redirect('index.php?page=admin_productos&status=' . ($is_edit_mode ? 'updated' : 'created'));

            } catch (PDOException $e) {
                $form_error = "Error de base de datos: " . $e->getMessage();
            }
        }
    }
    // Si hay error, re-poblar el array $perfume con los datos del POST para no perderlos
    $perfume = $_POST;
    $perfume['imagen_url'] = $imagen_url;
}
?>

<header class="mb-10">
    <h1 class="text-4xl font-bold text-gray-800"><?= $is_edit_mode ? 'Editar Perfume' : 'Nuevo Perfume' ?></h1>
    <p class="text-gray-500 mt-2">Complete los detalles del producto a continuación.</p>
</header>

<?php if ($form_error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert"><?= $form_error ?></div>
<?php endif; ?>

<div class="bg-white p-8 rounded-lg shadow-sm border border-gray-200">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
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
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label for="precio" class="block text-sm font-bold text-gray-700 mb-1">Precio</label>
                        <input type="number" step="0.01" id="precio" name="precio" value="<?= htmlspecialchars($perfume['precio']) ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-luxury-matte">
                    </div>
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