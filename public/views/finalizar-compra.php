<?php
// finalizar-compra.php — Confirmación y procesamiento de pedido
// Valida stock, guarda pedido y limpia carrito

// public/views/finalizar-compra.php

// Incluir configuración de mailer para notificaciones
$mailer_config = __DIR__ . '/../../config/mailer_config.php';
if (file_exists($mailer_config)) {
    require_once $mailer_config;
}

// Obtener productos del carrito
$carrito = [];
$total = 0;
if (isLoggedIn()) {
    $usuario_id = $_SESSION['usuario_id'];
    $stmt = $pdo->prepare("SELECT c.perfume_id, c.cantidad, p.nombre, p.precio, p.imagen_url, m.nombre AS marca_nombre FROM carrito c JOIN perfumes p ON c.perfume_id = p.id JOIN marcas m ON p.marca_id = m.id WHERE c.usuario_id = ?");
    $stmt->execute([$usuario_id]);
    $carrito = $stmt->fetchAll();
} elseif (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $stmt = $pdo->prepare("SELECT p.id as perfume_id, p.nombre, p.precio, p.imagen_url, m.nombre AS marca_nombre FROM perfumes p JOIN marcas m ON p.marca_id = m.id WHERE p.id = ?");
        $stmt->execute([$item['perfume_id']]);
        $perfume = $stmt->fetch();
        if ($perfume) {
            $perfume['cantidad'] = $item['cantidad'];
            $carrito[] = $perfume;
        }
    }
}
foreach ($carrito as $item) {
    $total += $item['precio'] * $item['cantidad'];
}

$mensaje = '';
$pedido_realizado = false; // Bandera para controlar la vista de éxito
$datos_pedido = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($carrito)) {
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $tipo_envio = $_POST['tipo_envio'] ?? 'domicilio_amba';

    if ($tipo_envio === 'coordinar') {
        $direccion = 'A coordinar con el vendedor (WhatsApp)';
    } else {
        $direccion = trim($_POST['direccion'] ?? '');
    }
    
    // Validación de teléfono (Argentina)
    $es_valido_arg = validate_argentina_phone($telefono);

    $stockError = false;
    // Validar stock antes de procesar
    foreach ($carrito as $item) {
        $perfume_id = $item['perfume_id'];
        $stmtStock = $pdo->prepare("SELECT stock FROM perfumes WHERE id = ?");
        $stmtStock->execute([$perfume_id]);
        $stock = $stmtStock->fetchColumn();
        if ($item['cantidad'] > $stock) {
            $stockError = true;
            break;
        }
    }
    if ($stockError) {
        $mensaje = 'Uno o más productos no tienen suficiente stock para completar el pedido.';
    } elseif (!$nombre || !$telefono || ($tipo_envio !== 'coordinar' && !$direccion)) {
        $mensaje = 'Por favor, completa todos los datos requeridos.';
    } elseif (!$es_valido_arg) {
        $mensaje = 'El número de teléfono no es válido. Ingrese código de área + número (Ej: 11 1234 5678).';
    } else {
        // Guardar pedido
        if ($tipo_envio === 'domicilio_amba') $direccion .= ' (AMBA)';
        if ($tipo_envio === 'domicilio_interior') $direccion .= ' (Interior)';

        $usuario_id = isLoggedIn() ? $_SESSION['usuario_id'] : null;
        $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, fecha, total, estado, nombre, direccion, email) VALUES (?, NOW(), ?, ?, ?, ?, ?)");
        $stmt->execute([$usuario_id, $total, 'Pendiente de Pago', $nombre, $direccion, $telefono]);
        $pedido_id = $pdo->lastInsertId();
        // Guardar productos y actualizar stock
        foreach ($carrito as $item) {
            $perfume_id = $item['perfume_id'];
            $stmtItem = $pdo->prepare("INSERT INTO pedido_items (pedido_id, perfume_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
            $stmtItem->execute([$pedido_id, $perfume_id, $item['cantidad'], $item['precio']]);
            // Actualizar stock
            $stmtStockUpdate = $pdo->prepare("UPDATE perfumes SET stock = stock - ? WHERE id = ?");
            $stmtStockUpdate->execute([$item['cantidad'], $perfume_id]);
        }
        // Limpiar carrito
        if (isLoggedIn()) {
            $stmt = $pdo->prepare("DELETE FROM carrito WHERE usuario_id = ?");
            $stmt->execute([$_SESSION['usuario_id']]);
        } else {
            unset($_SESSION['carrito']);
        }

        $mensaje = '¡Gracias por tu compra! Tu pedido ha sido recibido.';
        $pedido_realizado = true;
        $datos_pedido = ['id' => $pedido_id, 'total' => $total];
    }
}
?>

<!-- SweetAlert2 CDN -->
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="bg-white min-h-screen font-sans text-luxury-matte">
    <div class="container mx-auto py-16 px-6 lg:px-12">
        
        <!-- 1. Barra de Progreso Visual -->
        <div class="flex justify-center items-center gap-4 text-[10px] uppercase tracking-[0.2em] mb-16 text-gray-400">
            <span class="hover:text-luxury-gold transition-colors cursor-pointer">Carrito</span>
            <span class="text-gray-300">/</span>
            <span class="font-bold text-luxury-matte">Pago</span>
            <span class="text-gray-300">/</span>
            <span>Confirmación</span>
        </div>

        <?php if ($mensaje): ?>
            <!-- Mensaje de Éxito o Error -->
            <div class="max-w-2xl mx-auto text-center py-12">
                <?php if ($pedido_realizado): ?>
                    <!-- Icono Animado -->
                    <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6 border border-green-100 animate-bounce">
                        <i class="fas fa-check text-2xl text-green-600"></i>
                    </div>
                    
                    <h1 class="font-serif text-4xl mb-2">¡Pedido Registrado!</h1>
                    <p class="text-gray-500 mb-8 font-light">Tu pedido <strong>#<?= $datos_pedido['id'] ?></strong> ha sido reservado. Realiza el pago para procesar el envío.</p>
                    
                    <!-- Cuadro de Datos Bancarios -->
                    <div class="bg-white border border-luxury-gold p-8 rounded-sm shadow-sm mb-8 text-left max-w-md mx-auto relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 h-full bg-luxury-gold"></div>
                        <h3 class="font-serif text-xl mb-4 text-luxury-matte">Datos para Transferencia</h3>
                        <div class="space-y-3 text-sm text-gray-600">
                            <div class="flex justify-between border-b border-gray-100 pb-2"><span>Banco:</span> <span class="font-bold text-gray-800">Banco Provincia</span></div>
                            <div class="flex justify-between border-b border-gray-100 pb-2"><span>Titular:</span> <span class="font-bold text-gray-800">Villa Luro Store S.A.</span></div>
                            <div class="flex justify-between border-b border-gray-100 pb-2"><span>CBU:</span> <span class="font-mono font-bold text-gray-800">0070123400000012345678</span></div>
                            <div class="flex justify-between border-b border-gray-100 pb-2"><span>Alias:</span> <span class="font-bold text-luxury-gold">VILLALURO.STORE</span></div>
                            <div class="flex justify-between pt-2 text-lg"><span>Total a Pagar:</span> <span class="font-bold text-luxury-matte">$<?= number_format($datos_pedido['total'], 2) ?></span></div>
                        </div>
                    </div>

                    <!-- Botón WhatsApp -->
                    <?php 
                        $wa_msg = urlencode("Hola, realicé el pedido #{$datos_pedido['id']} y quiero enviar el comprobante.");
                        $wa_link = "https://wa.me/541112345678?text=$wa_msg";
                    ?>
                    <a href="<?= $wa_link ?>" target="_blank" class="inline-flex items-center gap-2 bg-[#25D366] text-white px-8 py-4 text-xs uppercase tracking-widest font-bold hover:bg-[#20bd5a] transition-colors duration-300 rounded-full mb-6 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <i class="fab fa-whatsapp text-lg"></i> Enviar Comprobante
                    </a>
                    
                    <div class="block mt-4">
                        <a href="index.php?page=catalogo" class="text-gray-400 hover:text-luxury-gold text-xs uppercase tracking-widest border-b border-transparent hover:border-luxury-gold transition-all">Volver a la tienda</a>
                    </div>

                <?php else: ?>
                    <div class="bg-red-50 border-l-2 border-red-500 p-6 text-red-700 mb-8 text-left">
                        <p class="font-bold font-serif text-lg mb-1">Atención</p>
                        <p class="text-sm"><?= $mensaje ?></p>
                    </div>
                    <a href="index.php?page=carrito" class="text-xs uppercase tracking-widest border-b border-gray-300 pb-1 hover:text-luxury-gold hover:border-luxury-gold transition-all">Volver al carrito</a>
                <?php endif; ?>
            </div>

        <?php elseif (empty($carrito)): ?>
            <!-- Carrito Vacío -->
            <div class="text-center py-24">
                <i class="fas fa-shopping-bag text-6xl text-gray-200 mb-6"></i>
                <h2 class="font-serif text-3xl text-gray-400 mb-4">Tu bolsa está vacía</h2>
                <a href="index.php?page=catalogo" class="text-luxury-gold border-b border-luxury-gold pb-1 text-[10px] uppercase tracking-widest hover:text-luxury-matte hover:border-luxury-matte transition-all">Ir a la Colección</a>
            </div>

        <?php else: ?>
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-24">
                
                <!-- Columna Izquierda: Formulario de Envío -->
                <div class="lg:col-span-7">
                    <div class="mb-10 border-b border-gray-100 pb-4">
                        <h1 class="font-serif text-3xl text-luxury-matte">Detalles de Envío</h1>
                    </div>
                    
                    <form method="post" id="checkout-form" class="space-y-10">
                        <div class="group">
                            <label for="nombre" class="block text-[10px] uppercase tracking-widest text-gray-400 mb-2 group-focus-within:text-luxury-gold transition-colors">Nombre Completo</label>
                            <input type="text" id="nombre" name="nombre" required 
                                   class="w-full border-b border-gray-200 py-3 text-lg focus:outline-none focus:border-luxury-gold transition-colors bg-transparent placeholder-gray-300 font-light"
                                   placeholder="Ej. Juan Pérez">
                        </div>

                        <div class="group">
                            <label for="telefono" class="block text-[10px] uppercase tracking-widest text-gray-400 mb-2 group-focus-within:text-luxury-gold transition-colors">Número de Teléfono</label>
                            <input type="tel" id="telefono" name="telefono" required 
                                   class="w-full border-b border-gray-200 py-3 text-lg focus:outline-none focus:border-luxury-gold transition-colors bg-transparent placeholder-gray-300 font-light"
                                   placeholder="Ej. +54 9 11 1234-5678">
                        </div>

                        <!-- Selección de Envío -->
                        <div class="py-2">
                            <label class="block text-[10px] uppercase tracking-widest text-gray-400 mb-4">Método de Entrega</label>
                            <div class="flex flex-col gap-4">
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" name="tipo_envio" value="domicilio_amba" checked class="hidden peer" onchange="toggleAddress(true)">
                                    <span class="w-4 h-4 border border-gray-300 rounded-full mr-3 peer-checked:bg-luxury-gold peer-checked:border-luxury-gold transition-all"></span>
                                    <span class="text-sm group-hover:text-luxury-gold transition-colors">Envío a Domicilio (AMBA)</span>
                                </label>
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" name="tipo_envio" value="domicilio_interior" class="hidden peer" onchange="toggleAddress(true)">
                                    <span class="w-4 h-4 border border-gray-300 rounded-full mr-3 peer-checked:bg-luxury-gold peer-checked:border-luxury-gold transition-all"></span>
                                    <span class="text-sm group-hover:text-luxury-gold transition-colors">Envío a Domicilio (Interior)</span>
                                </label>
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" name="tipo_envio" value="coordinar" class="hidden peer" onchange="toggleAddress(false)">
                                    <span class="w-4 h-4 border border-gray-300 rounded-full mr-3 peer-checked:bg-luxury-gold peer-checked:border-luxury-gold transition-all"></span>
                                    <span class="text-sm group-hover:text-luxury-gold transition-colors">Coordinar (WhatsApp)</span>
                                </label>
                            </div>
                        </div>

                        <div class="group" id="address-container">
                            <label for="direccion" class="block text-[10px] uppercase tracking-widest text-gray-400 mb-2 group-focus-within:text-luxury-gold transition-colors">Dirección de Entrega</label>
                            <input type="text" id="direccion" name="direccion" required 
                                   class="w-full border-b border-gray-200 py-3 text-lg focus:outline-none focus:border-luxury-gold transition-colors bg-transparent placeholder-gray-300 font-light"
                                   placeholder="Calle, Número, Ciudad">
                        </div>

                        <div class="pt-8">
                            <button type="submit" class="w-full bg-luxury-matte text-white py-5 text-xs uppercase tracking-[0.2em] font-bold hover:bg-luxury-gold hover:shadow-xl transition-all duration-500 flex items-center justify-center gap-3 group">
                                <i class="fas fa-lock text-gray-500 group-hover:text-white transition-colors text-sm"></i>
                                Confirmar Pedido
                            </button>
                            <p class="text-center text-[10px] text-gray-400 mt-4 flex items-center justify-center gap-2">
                                <i class="fas fa-shield-alt"></i> Transacción Segura SSL
                            </p>
                        </div>
                    </form>
                </div>

                <!-- Columna Derecha: Resumen del Pedido -->
                <div class="lg:col-span-5">
                    <div class="bg-[#F9F7F2] p-8 lg:p-10 rounded-sm sticky top-24 border border-gray-100">
                        <h2 class="font-serif text-2xl mb-8 pb-4 border-b border-gray-200/60 text-luxury-matte">Tu Selección</h2>
                        
                        <div class="space-y-6 mb-8 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                            <?php foreach ($carrito as $item): ?>
                                <div class="flex items-center gap-5">
                                    <div class="w-16 h-16 rounded-full bg-white shadow-sm overflow-hidden flex-shrink-0 border border-gray-100 p-1">
                                        <img src="<?= htmlspecialchars($item['imagen_url']) ?>" alt="<?= htmlspecialchars($item['nombre']) ?>" class="w-full h-full object-cover rounded-full">
                                    </div>
                                    <div class="flex-grow">
                                        <h3 class="font-serif text-sm text-luxury-matte font-medium"><?= htmlspecialchars($item['nombre']) ?></h3>
                                        <p class="text-[10px] uppercase tracking-wider text-gray-500 mt-0.5"><?= htmlspecialchars($item['marca_nombre']) ?></p>
                                        <p class="text-xs text-gray-400 mt-1">Cant: <?= $item['cantidad'] ?></p>
                                    </div>
                                    <div class="text-sm font-semibold text-luxury-matte">
                                        $<?= number_format($item['precio'] * $item['cantidad'], 2) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="border-t border-gray-200/60 pt-6 space-y-3">
                            <div class="flex justify-between text-sm text-gray-600 font-light">
                                <span>Subtotal</span>
                                <span>$<?= number_format($total, 2) ?></span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600 font-light">
                                <span>Envío</span>
                                <span class="text-luxury-gold text-[10px] uppercase font-bold">Gratis</span>
                            </div>
                            <div class="flex justify-between items-end pt-6 mt-4 border-t border-gray-200/60">
                                <span class="font-serif text-lg text-luxury-matte">Total</span>
                                <span class="font-serif text-3xl font-bold text-luxury-matte">$<?= number_format($total, 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleAddress(show) {
    const container = document.getElementById('address-container');
    const input = document.getElementById('direccion');
    if (show) {
        container.classList.remove('hidden');
        input.required = true;
    } else {
        container.classList.add('hidden');
        input.required = false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkout-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validación JS de teléfono Argentina
            const telInput = document.getElementById('telefono');
            let clean = telInput.value.replace(/[^0-9]/g, '');
            if (clean.startsWith('0')) clean = clean.substring(1);
            
            if (!(clean.length === 10 || (clean.length === 12 && clean.startsWith('54')) || (clean.length === 13 && clean.startsWith('549')))) {
                Swal.fire({
                    title: 'Teléfono inválido',
                    text: 'Por favor ingrese un número válido para Argentina (Ej: 11 1234 5678)',
                    icon: 'warning',
                    confirmButtonColor: '#1A1A1A'
                });
                return;
            }
            
            // SweetAlert2 Loader
            Swal.fire({
                title: 'Procesando...',
                text: 'Estamos preparando tu pedido de lujo.',
                icon: null,
                showConfirmButton: false,
                allowOutsideClick: false,
                background: '#fff',
                color: '#1A1A1A',
                didOpen: () => {
                    Swal.showLoading();
                    // Pequeño delay para que el usuario vea la animación antes de enviar
                    setTimeout(() => {
                        form.submit();
                    }, 1500);
                }
            });
        });
    }
});
</script>
