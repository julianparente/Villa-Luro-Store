<?php
// public/views/admin_marcas.php

// Manejo de acciones (Eliminar)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        // Verificar si hay productos asociados antes de eliminar
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM perfumes WHERE marca_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            $error = "No se puede eliminar la marca porque tiene productos asociados. Elimine o mueva los productos primero.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM marcas WHERE id = ?");
            $stmt->execute([$id]);
            header('Location: index.php?page=admin_marcas&status=deleted');
            exit;
        }
    } catch (PDOException $e) {
        $error = "Error al eliminar: " . $e->getMessage();
    }
}

// Manejo de formulario (Crear/Editar)
$editing_brand = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM marcas WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $editing_brand = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $id = $_POST['id'] ?? null;

    if (empty($nombre)) {
        $error = "El nombre de la marca es obligatorio.";
    } else {
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE marcas SET nombre = ? WHERE id = ?");
                $stmt->execute([$nombre, $id]);
                header('Location: index.php?page=admin_marcas&status=updated');
            } else {
                $stmt = $pdo->prepare("INSERT INTO marcas (nombre) VALUES (?)");
                $stmt->execute([$nombre]);
                header('Location: index.php?page=admin_marcas&status=created');
            }
            exit;
        } catch (PDOException $e) {
            $error = "Error en base de datos: " . $e->getMessage();
        }
    }
}

// Obtener todas las marcas
$marcas = $pdo->query("SELECT * FROM marcas ORDER BY nombre")->fetchAll();
$status = $_GET['status'] ?? '';
?>

<header class="mb-10">
    <h1 class="text-4xl font-bold text-gray-800">Gestión de Marcas</h1>
    <p class="text-gray-500 mt-2">Administre las marcas disponibles en el catálogo.</p>
</header>

<!-- Mensajes de estado -->
<?php if ($status === 'created'): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">Marca creada correctamente.</div>
<?php elseif ($status === 'updated'): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">Marca actualizada correctamente.</div>
<?php elseif ($status === 'deleted'): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">Marca eliminada correctamente.</div>
<?php endif; ?>

<?php if (isset($error) && $error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Formulario -->
    <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 sticky top-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4"><?= $editing_brand ? 'Editar Marca' : 'Nueva Marca' ?></h2>
            <form method="POST" action="index.php?page=admin_marcas">
                <?php if ($editing_brand): ?>
                    <input type="hidden" name="id" value="<?= $editing_brand['id'] ?>">
                <?php endif; ?>
                
                <div class="mb-4">
                    <label for="nombre" class="block text-sm font-bold text-gray-700 mb-1">Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="<?= $editing_brand ? htmlspecialchars($editing_brand['nombre']) : '' ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-luxury-matte">
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-luxury-matte text-white px-4 py-2 rounded-lg shadow-md hover:bg-black transition-colors font-semibold">
                        <?= $editing_brand ? 'Actualizar' : 'Guardar' ?>
                    </button>
                    <?php if ($editing_brand): ?>
                        <a href="index.php?page=admin_marcas" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 text-center">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-3">ID</th>
                        <th class="px-6 py-3">Nombre</th>
                        <th class="px-6 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    <?php foreach ($marcas as $marca): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-gray-500">#<?= $marca['id'] ?></td>
                            <td class="px-6 py-4 font-semibold text-gray-800"><?= htmlspecialchars($marca['nombre']) ?></td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="index.php?page=admin_marcas&action=edit&id=<?= $marca['id'] ?>" class="text-blue-600 hover:text-blue-800" title="Editar"><i class="fas fa-pencil-alt"></i></a>
                                    <button onclick="confirmDelete(<?= $marca['id'] ?>)" class="text-red-500 hover:text-red-700" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($marcas)): ?>
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500">No hay marcas registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "No podrá eliminar la marca si tiene productos asociados.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#1A1A1A',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `index.php?page=admin_marcas&action=delete&id=${id}`;
        }
    })
}
</script>