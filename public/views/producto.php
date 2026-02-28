<?php
// public/views/producto.php

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php?page=catalogo');
    exit;
}

// Obtener detalles del producto
$stmt = $pdo->prepare("SELECT p.*, m.nombre AS marca_nombre FROM perfumes p JOIN marcas m ON p.marca_id = m.id WHERE p.id = ?");
$stmt->execute([$id]);
$perfume = $stmt->fetch();

if (!$perfume) {
    echo "<div class='container mx-auto py-20 text-center'><h2 class='text-2xl font-serif'>Producto no encontrado</h2><a href='index.php?page=catalogo' class='text-luxury-gold underline'>Volver al catálogo</a></div>";
    return;
}
?>

<div class="bg-white min-h-screen">
    <!-- Breadcrumb -->
    <div class="bg-luxury-bone py-4 border-b border-gray-200">
        <div class="container mx-auto px-6 text-xs uppercase tracking-widest text-gray-500">
            <a href="index.php" class="hover:text-luxury-gold">Inicio</a>
            <span class="mx-2">/</span>
            <a href="index.php?page=catalogo" class="hover:text-luxury-gold">Colección</a>
            <span class="mx-2">/</span>
            <span class="text-luxury-matte font-bold"><?= htmlspecialchars($perfume['nombre']) ?></span>
        </div>
    </div>

    <div class="container mx-auto px-6 py-12">
        <div class="flex flex-col lg:flex-row gap-16">
            
            <!-- Columna Izquierda: Imagen -->
            <div class="lg:w-1/3">
                <div class="relative group">
                    <div class="bg-gray-50 aspect-[3/4] overflow-hidden relative shadow-sm rounded-sm">
                        <!-- Carrusel Track -->
                        <div id="carousel-track" class="flex h-full transition-transform duration-500 ease-out">
                            <?php 
                            // Simulamos una galería duplicando la imagen principal para mostrar el efecto
                            $imagenes = [$perfume['imagen_url'], $perfume['imagen_url'], $perfume['imagen_url']];
                            foreach ($imagenes as $img): 
                            ?>
                                <div class="min-w-full h-full">
                                    <img src="<?= htmlspecialchars($img) ?>" 
                                         alt="<?= htmlspecialchars($perfume['nombre']) ?>" 
                                         class="w-full h-full object-cover">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Botones de Navegación -->
                    <button id="prev-btn" class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/90 text-luxury-matte w-10 h-10 rounded-full shadow-md opacity-0 group-hover:opacity-100 transition-all hover:bg-luxury-gold hover:text-white flex items-center justify-center z-10">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button id="next-btn" class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/90 text-luxury-matte w-10 h-10 rounded-full shadow-md opacity-0 group-hover:opacity-100 transition-all hover:bg-luxury-gold hover:text-white flex items-center justify-center z-10">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>

            <!-- Columna Derecha: Detalles -->
            <div class="lg:w-1/2 flex flex-col justify-center">
                <h2 class="text-sm uppercase tracking-[0.2em] text-gray-400 mb-2"><?= htmlspecialchars($perfume['marca_nombre']) ?></h2>
                <h1 class="font-serif text-4xl md:text-5xl text-luxury-matte mb-6"><?= htmlspecialchars($perfume['nombre']) ?></h1>
                
                <div class="text-2xl text-luxury-gold font-serif mb-8">
                    $<?= number_format($perfume['precio'], 2) ?>
                </div>

                <div class="prose prose-sm text-gray-600 mb-10 font-light leading-relaxed">
                    <p><?= nl2br(htmlspecialchars($perfume['descripcion'] ?: 'Una fragancia exclusiva que evoca elegancia y sofisticación. Ideal para quienes buscan dejar una huella imborrable.')) ?></p>
                </div>

                <!-- Formulario de Añadir al Carrito -->
                <div class="border-t border-b border-gray-100 py-8 mb-10">
                    <form method="post" action="index.php?page=api_add_to_cart" class="add-to-cart-form flex gap-4">
                        <input type="hidden" name="perfume_id" value="<?= $perfume['id'] ?>">
                        
                        <?php if ($perfume['stock'] > 0): ?>
                            <button type="submit" class="flex-1 bg-luxury-matte text-white py-4 text-xs uppercase tracking-[0.2em] font-bold hover:bg-luxury-gold transition-all duration-300">
                                Añadir al Carrito
                            </button>
                        <?php else: ?>
                            <button type="button" disabled class="flex-1 bg-gray-200 text-gray-400 py-4 text-xs uppercase tracking-[0.2em] font-bold cursor-not-allowed">
                                Agotado
                            </button>
                        <?php endif; ?>
                    </form>
                    <?php if ($perfume['stock'] > 0 && $perfume['stock'] < 5): ?>
                        <p class="text-xs text-orange-500 mt-3"><i class="fas fa-exclamation-circle"></i> ¡Solo quedan <?= $perfume['stock'] ?> unidades!</p>
                    <?php endif; ?>
                </div>

                <!-- Especificaciones (Acordeón o Lista) -->
                <div>
                    <h3 class="font-serif text-xl mb-4 border-b border-gray-200 pb-2">Especificaciones</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 text-sm">
                        <div class="grid grid-cols-3 border-b border-gray-50 pb-2">
                            <dt class="text-gray-400 uppercase tracking-wider text-[10px] font-bold pt-1">Categoría</dt>
                            <dd class="col-span-2 text-gray-700 capitalize"><?= htmlspecialchars($perfume['categoria']) ?></dd>
                        </div>
                        <div class="grid grid-cols-3 border-b border-gray-50 pb-2">
                            <dt class="text-gray-400 uppercase tracking-wider text-[10px] font-bold pt-1">Marca</dt>
                            <dd class="col-span-2 text-gray-700"><?= htmlspecialchars($perfume['marca_nombre']) ?></dd>
                        </div>
                        <div class="grid grid-cols-3 border-b border-gray-50 pb-2">
                            <dt class="text-gray-400 uppercase tracking-wider text-[10px] font-bold pt-1">Presentación</dt>
                            <dd class="col-span-2 text-gray-700">Envase de Lujo (Estándar)</dd>
                        </div>
                        <div class="grid grid-cols-3 border-b border-gray-50 pb-2">
                            <dt class="text-gray-400 uppercase tracking-wider text-[10px] font-bold pt-1">Disponibilidad</dt>
                            <dd class="col-span-2 text-gray-700"><?= $perfume['stock'] > 0 ? 'En Stock' : 'Sin Stock' ?></dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lógica del Carrusel
    const track = document.getElementById('carousel-track');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const slides = track.children;
    let currentIndex = 0;

    function updateCarousel() {
        track.style.transform = `translateX(-${currentIndex * 100}%)`;
    }

    nextBtn.addEventListener('click', () => {
        currentIndex = (currentIndex + 1) % slides.length;
        updateCarousel();
    });

    prevBtn.addEventListener('click', () => {
        currentIndex = (currentIndex - 1 + slides.length) % slides.length;
        updateCarousel();
    });

    // Lógica AJAX para añadir al carrito (Misma que en catalogo.php)
    const forms = document.querySelectorAll('.add-to-cart-form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const button = this.querySelector('button[type="submit"]');
            const originalContent = button.innerHTML;

            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar contador
                    const cartCount = data.cart_count;
                    let countEl = document.getElementById('cart-count');
                    
                    if (countEl) {
                        countEl.textContent = cartCount;
                        if (cartCount > 0) countEl.classList.remove('hidden');
                    } else if (cartCount > 0) {
                        const container = document.getElementById('cart-icon-container');
                        if (container) {
                            const span = document.createElement('span');
                            span.id = 'cart-count';
                            span.className = 'absolute -top-2 -right-3 bg-luxury-gold text-white text-[9px] rounded-full h-4 w-4 flex items-center justify-center';
                            span.textContent = cartCount;
                            container.appendChild(span);
                        }
                    }
                    showToastNotification(data.message, 'success');
                } else {
                    showToastNotification(data.message || 'Error al añadir.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToastNotification('Error de conexión.', 'error');
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = originalContent;
            });
        });
    });
});
</script>