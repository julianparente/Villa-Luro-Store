<?php
// public/views/admin_config.php

$mensaje = '';
$error = '';

// Procesar subida de imágenes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload_dir = 'public/img/uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $imagenes_a_actualizar = ['logo', 'login_image', 'about_image'];

    foreach ($imagenes_a_actualizar as $clave) {
        if (isset($_FILES[$clave]) && $_FILES[$clave]['error'] === UPLOAD_ERR_OK) {
            $upload_result = upload_image($_FILES[$clave], $upload_dir);
            
            if ($upload_result['success']) {
                $target_path = $upload_result['path'];
                $stmt = $pdo->prepare("INSERT INTO configuracion (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?");
                $stmt->execute([$clave, $target_path, $target_path]);
                $mensaje = "Imágenes actualizadas correctamente.";
            } else {
                $error = $upload_result['error'];
            }
        }
    }
}

// Obtener valores actuales
$config = [];
$stmt = $pdo->query("SELECT * FROM configuracion");
while ($row = $stmt->fetch()) {
    $config[$row['clave']] = $row['valor'];
}
?>

<header class="mb-10">
    <h1 class="text-4xl font-bold text-gray-800">Configuración de Imágenes</h1>
    <p class="text-gray-500 mt-2">Personalice las imágenes principales del sitio.</p>
</header>

<?php if ($mensaje): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6"><?= $mensaje ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6"><?= $error ?></div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
    <form method="post" enctype="multipart/form-data" class="space-y-8">
        
        <!-- Logo -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center border-b border-gray-100 pb-8">
            <div class="md:col-span-1">
                <label class="block font-bold text-gray-700 mb-2">Logo del Sitio</label>
                <p class="text-xs text-gray-500">Aparece en el encabezado y pie de página.</p>
            </div>
            <div class="md:col-span-1 flex justify-center bg-gray-50 p-4 rounded border border-gray-200">
                <img id="preview-logo" src="<?= $config['logo'] ?? 'public/img/logo.png' ?>" alt="Logo Actual" class="h-16 object-contain">
            </div>
            <div class="md:col-span-1">
                <input type="file" name="logo" onchange="previewImage(this, 'preview-logo')" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-luxury-matte file:text-white hover:file:bg-gray-700"/>
            </div>
        </div>

        <!-- Imagen Login -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center border-b border-gray-100 pb-8">
            <div class="md:col-span-1">
                <label class="block font-bold text-gray-700 mb-2">Imagen de Acceso</label>
                <p class="text-xs text-gray-500">Imagen lateral en la página de Login.</p>
            </div>
            <div class="md:col-span-1 flex justify-center bg-gray-50 p-4 rounded border border-gray-200">
                <img id="preview-login" src="<?= $config['login_image'] ?? 'public/img/login.png' ?>" alt="Login Actual" class="h-32 object-cover rounded">
            </div>
            <div class="md:col-span-1">
                <input type="file" name="login_image" onchange="previewImage(this, 'preview-login')" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-luxury-matte file:text-white hover:file:bg-gray-700"/>
            </div>
        </div>

        <!-- Imagen Quienes Somos -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center border-b border-gray-100 pb-8">
            <div class="md:col-span-1">
                <label class="block font-bold text-gray-700 mb-2">Imagen "Quiénes Somos"</label>
                <p class="text-xs text-gray-500">Imagen principal en la sección de historia.</p>
            </div>
            <div class="md:col-span-1 flex justify-center bg-gray-50 p-4 rounded border border-gray-200">
                <img id="preview-about" src="<?= $config['about_image'] ?? 'public/img/about-us-1.jpg' ?>" alt="About Actual" class="h-32 object-cover rounded">
            </div>
            <div class="md:col-span-1">
                <input type="file" name="about_image" onchange="previewImage(this, 'preview-about')" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-luxury-matte file:text-white hover:file:bg-gray-700"/>
            </div>
        </div>

        <div class="pt-4 text-right">
            <button type="submit" class="bg-luxury-matte text-white px-8 py-3 rounded shadow hover:bg-black transition-colors font-bold uppercase tracking-widest text-xs">Guardar Cambios</button>
        </div>
    </form>
</div>

<script>
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>