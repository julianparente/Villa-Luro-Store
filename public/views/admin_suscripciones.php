<?php
// public/views/admin_suscripciones.php

// Manejo de eliminación
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM suscripciones WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: index.php?page=admin_suscripciones&status=deleted');
        exit;
    } catch (PDOException $e) {
        $error = "Error al eliminar: " . $e->getMessage();
    }
}

// Obtener suscriptores
$subs = $pdo->query("SELECT * FROM suscripciones ORDER BY id DESC")->fetchAll();
$status = $_GET['status'] ?? '';
?>

<header class="mb-10">
    <h1 class="text-4xl font-bold text-gray-800">Gestión de Suscriptores</h1>
    <p class="text-gray-500 mt-2">Administre la lista de correos suscritos al newsletter.</p>
</header>

<!-- Mensajes de estado -->
<?php if ($status === 'deleted'): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">Suscriptor eliminado correctamente.</div>
<?php endif; ?>

<?php if (isset($error) && $error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
        <h3 class="font-bold text-lg text-gray-800">Lista de Correos</h3>
        <span class="bg-luxury-matte text-white text-xs font-bold px-3 py-1 rounded-full"><?= count($subs) ?> Suscriptores</span>
    </div>
    
    <table class="w-full text-left">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
            <tr>
                <th class="px-6 py-3">ID</th>
                <th class="px-6 py-3">Email</th>
                <th class="px-6 py-3 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="text-sm divide-y divide-gray-100">
            <?php foreach ($subs as $sub): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 text-gray-500">#<?= $sub['id'] ?></td>
                    <td class="px-6 py-4 font-semibold text-gray-800">
                        <a href="mailto:<?= htmlspecialchars($sub['email']) ?>" class="hover:text-luxury-gold transition-colors">
                            <?= htmlspecialchars($sub['email']) ?>
                        </a>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button onclick="confirmDelete(<?= $sub['id'] ?>)" class="text-red-500 hover:text-red-700 transition-colors" title="Eliminar">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($subs)): ?>
                <tr>
                    <td colspan="3" class="px-6 py-10 text-center text-gray-500">No hay suscriptores registrados aún.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function confirmDelete(id) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción eliminará al suscriptor de la lista de forma permanente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#1A1A1A',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `index.php?page=admin_suscripciones&action=delete&id=${id}`;
        }
    })
}
</script>