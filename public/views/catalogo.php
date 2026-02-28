<?php
// Filtros
$categoria = $_GET['categoria'] ?? '';
$marca = $_GET['marca'] ?? '';
$limit = 15; // Límite de productos por página
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

// Obtener marcas para el filtro
$stmt = $pdo->prepare("SELECT id, nombre FROM marcas ORDER BY nombre");
$stmt->execute();
$marcas = $stmt->fetchAll();

// Construir consulta de perfumes
$query = "SELECT p.*, m.nombre AS marca_nombre FROM perfumes p JOIN marcas m ON p.marca_id = m.id WHERE 1";
$params = [];
if ($categoria) {
    $query .= " AND p.categoria = ?";
    $params[] = $categoria;
}
if ($marca) {
    $query .= " AND p.marca_id = ?";
    $params[] = $marca;
}
if ($min_price !== '') {
    $query .= " AND p.precio >= ?";
    $params[] = $min_price;
}
if ($max_price !== '') {
    $query .= " AND p.precio <= ?";
    $params[] = $max_price;
}
$query .= " ORDER BY p.nombre LIMIT ?";
$params[] = $limit;

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$perfumes = $stmt->fetchAll();
$initial_perfume_count = count($perfumes);

?>

<div class="container mx-auto py-20 px-6">
    <div class="flex flex-col lg:flex-row gap-16">
        <!-- Sidebar de Filtros -->
        <aside class="w-full lg:w-1/4">
            <div class="sticky top-32">
                <h2 class="font-serif text-3xl mb-10">Filtros</h2>
                <form method="get" id="filter-form" class="space-y-12">
                    <input type="hidden" name="page" value="catalogo">
                    
                    <div>
                        <h3 class="text-[10px] uppercase tracking-[0.3em] font-bold mb-6 text-gray-400">Género</h3>
                        <div class="space-y-3">
                            <?php foreach(['' => 'Todos', 'masculino' => 'Masculino', 'femenino' => 'Femenino', 'unisex' => 'Unisex'] as $val => $label): ?>
                                <label class="flex items-center text-xs tracking-widest cursor-pointer group">
                                    <input type="radio" name="categoria" value="<?= $val ?>" <?= $categoria==$val?'checked':'' ?> class="hidden peer">
                                    <span class="w-3 h-3 border border-gray-300 rounded-full mr-3 peer-checked:bg-luxury-gold peer-checked:border-luxury-gold transition-all"></span>
                                    <span class="group-hover:text-luxury-gold transition-colors"><?= $label ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-[10px] uppercase tracking-[0.3em] font-bold mb-6 text-gray-400">Marca</h3>
                        <select name="marca" class="w-full bg-transparent border-b border-gray-200 py-2 text-xs focus:outline-none focus:border-luxury-gold transition-colors">
                            <option value="">Todas las marcas</option>
                            <?php foreach ($marcas as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= $marca==$m['id']?'selected':'' ?>><?= htmlspecialchars($m['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <h3 class="text-[10px] uppercase tracking-[0.3em] font-bold mb-6 text-gray-400">Rango de Precio</h3>
                        <div class="flex items-center gap-4">
                            <input type="number" name="min_price" value="<?= $min_price ?>" placeholder="Min" class="w-full bg-transparent border-b border-gray-200 py-2 text-xs focus:outline-none focus:border-luxury-gold">
                            <span class="text-gray-300">—</span>
                            <input type="number" name="max_price" value="<?= $max_price ?>" placeholder="Max" class="w-full bg-transparent border-b border-gray-200 py-2 text-xs focus:outline-none focus:border-luxury-gold">
                        </div>
                    </div>

                </form>
            </div>
        </aside>

        <!-- Grilla de Productos -->
        <div class="w-full lg:w-3/4">
            <div id="product-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-20">
                <?php foreach ($perfumes as $perfume): ?>
                    <div class="group">
                        <div class="relative overflow-hidden bg-white mb-8 aspect-[3/4] shadow-sm">
                    <img src="<?= htmlspecialchars($perfume['imagen_url']) ?>" 
                         alt="<?= htmlspecialchars($perfume['nombre']) ?>" 
                         class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                    
                    <div class="absolute inset-0 bg-luxury-matte/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-center justify-center">
                        <form method="post" action="index.php?page=api_add_to_cart" class="add-to-cart-form">
                            <input type="hidden" name="perfume_id" value="<?= $perfume['id'] ?>">
                            <button type="submit" 
                                    class="bg-white text-luxury-matte px-6 py-3 text-[10px] uppercase tracking-widest font-bold hover:bg-luxury-gold hover:text-white transition-colors duration-300"
                                    <?= $perfume['stock'] < 1 ? 'disabled' : '' ?>>
                                <?= $perfume['stock'] < 1 ? 'Agotado' : 'Añadir al Carrito' ?>
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="text-center">
                    <p class="text-[10px] uppercase tracking-[0.2em] text-gray-400 mb-1"><?= htmlspecialchars($perfume['marca_nombre']) ?></p>
                    <h2 class="font-serif text-xl mb-2"><?= htmlspecialchars($perfume['nombre']) ?></h2>
                    <p class="text-luxury-gold font-semibold tracking-widest">$<?= number_format($perfume['precio'], 2) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Contenedor para Cargar Más -->
    <?php if ($initial_perfume_count >= $limit): ?>
    <div id="load-more-container" class="text-center mt-20">
        <button id="load-more-btn" class="border border-luxury-gold text-luxury-gold px-10 py-4 uppercase tracking-widest text-[10px] font-bold hover:bg-luxury-gold hover:text-white transition-all duration-300">
            Cargar más
        </button>
        <div id="loading-spinner" class="hidden">
            <i class="fas fa-spinner fa-spin text-luxury-gold text-2xl"></i>
        </div>
        <p id="end-of-collection" class="hidden font-serif text-lg text-gray-400 italic">Has visto toda nuestra colección.</p>
    </div>
    <?php endif; ?>

    <?php if (empty($perfumes)): ?>
        <div class="py-20 text-center">
            <p class="font-serif text-2xl text-gray-400 italic">No se encontraron fragancias que coincidan con su búsqueda.</p>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filter-form');
    if (filterForm) {
        // Selecciona todos los inputs que deben disparar la actualización
        const inputsToWatch = filterForm.querySelectorAll(
            'input[type="radio"], select, input[type="number"]'
        );

        inputsToWatch.forEach(input => {
            // El evento 'change' se dispara cuando se selecciona una opción o se termina de editar un campo.
            input.addEventListener('change', () => {
                filterForm.submit();
            });
        });
    }

    // --- Lógica de "Cargar Más" ---
    const loadMoreBtn = document.getElementById('load-more-btn');
    const loadingSpinner = document.getElementById('loading-spinner');
    const endOfCollectionMsg = document.getElementById('end-of-collection');
    const productGrid = document.getElementById('product-grid');
    
    let offset = <?= $initial_perfume_count ?>;
    const limit = <?= $limit ?>;

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            loadMoreBtn.classList.add('hidden');
            loadingSpinner.classList.remove('hidden');

            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('offset', offset);
            
            fetch(`index.php?page=api_load_more&${urlParams.toString()}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.perfumes.length > 0) {
                        data.perfumes.forEach(perfume => {
                            const perfumeCard = createPerfumeCard(perfume);
                            productGrid.insertAdjacentHTML('beforeend', perfumeCard);
                        });
                        
                        offset += data.perfumes.length;

                        if (data.perfumes.length < limit) {
                            loadingSpinner.classList.add('hidden');
                            endOfCollectionMsg.classList.remove('hidden');
                        } else {
                            loadingSpinner.classList.add('hidden');
                            loadMoreBtn.classList.remove('hidden');
                        }
                    } else {
                        loadingSpinner.classList.add('hidden');
                        endOfCollectionMsg.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error al cargar más productos:', error);
                    loadingSpinner.classList.add('hidden');
                    loadMoreBtn.classList.remove('hidden');
                });
        });
    }

    function createPerfumeCard(perfume) {
        const priceFormatted = new Intl.NumberFormat('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(perfume.precio);
        const isOutOfStock = perfume.stock < 1;
        const buttonText = isOutOfStock ? 'Agotado' : 'Añadir al Carrito';
        const buttonDisabled = isOutOfStock ? 'disabled' : '';

        return `
            <div class="group">
                <div class="relative overflow-hidden bg-white mb-8 aspect-[3/4] shadow-sm">
                    <img src="${escapeHTML(perfume.imagen_url)}" alt="${escapeHTML(perfume.nombre)}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                    <div class="absolute inset-0 bg-luxury-matte/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-center justify-center">
                        <form method="post" action="index.php?page=api_add_to_cart" class="add-to-cart-form">
                            <input type="hidden" name="perfume_id" value="${perfume.id}">
                            <button type="submit" class="bg-white text-luxury-matte px-6 py-3 text-[10px] uppercase tracking-widest font-bold hover:bg-luxury-gold hover:text-white transition-colors duration-300" ${buttonDisabled}>
                                ${buttonText}
                            </button>
                        </form>
                    </div>
                </div>
                <div class="text-center">
                    <p class="text-[10px] uppercase tracking-[0.2em] text-gray-400 mb-1">${escapeHTML(perfume.marca_nombre)}</p>
                    <h2 class="font-serif text-xl mb-2">${escapeHTML(perfume.nombre)}</h2>
                    <p class="text-luxury-gold font-semibold tracking-widest">$${priceFormatted}</p>
                </div>
            </div>
        `;
    }

    function escapeHTML(str) {
        const p = document.createElement('p');
        p.textContent = str;
        return p.innerHTML;
    }

    // --- Lógica para "Añadir al Carrito" con delegación de eventos ---
    // Esto asegura que los productos cargados dinámicamente también funcionen.
    // La lógica es una adaptación de la que se encuentra en app.js
    if (productGrid) {
        productGrid.addEventListener('submit', function(e) {
            if (e.target.matches('.add-to-cart-form')) {
                // Si otro script (como app.js) ya manejó el evento, no hacemos nada
                if (e.defaultPrevented) return;

                e.preventDefault();
                e.stopPropagation(); // Evita que el evento suba y dispare otros scripts globales
                const form = e.target;
                const formData = new FormData(form);
                const button = form.querySelector('button[type="submit"]');
                const originalButtonContent = button.innerHTML;

                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                fetch(form.action, { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Actualizar contador del carrito (Desktop)
                            const cartCount = data.cart_count;
                            let countEl = document.getElementById('cart-count');
                            
                            if (countEl) {
                                countEl.textContent = cartCount;
                                if (cartCount > 0) countEl.classList.remove('hidden');
                            } else if (cartCount > 0) {
                                // Crear badge si no existe
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
                            showToastNotification(data.message || 'No se pudo añadir el producto.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error al añadir al carrito:', error);
                        showToastNotification('Ocurrió un error de conexión.', 'error');
                    })
                    .finally(() => {
                        button.disabled = false;
                        button.innerHTML = originalButtonContent;
                    });
            }
        });
    }

    function showToastNotification(message, type = 'success') {
        // Eliminar toasts anteriores
        const existingToast = document.querySelector('.custom-toast');
        if (existingToast) existingToast.remove();

        const toast = document.createElement('div');
        toast.className = `custom-toast fixed bottom-5 right-5 px-6 py-4 rounded-lg shadow-xl z-50 flex items-center gap-3 transition-all duration-500 transform translate-y-10 opacity-0 ${type === 'success' ? 'bg-luxury-matte text-white border-l-4 border-luxury-gold' : 'bg-red-500 text-white'}`;
        
        const icon = type === 'success' ? '<i class="fas fa-check-circle text-luxury-gold"></i>' : '<i class="fas fa-exclamation-circle"></i>';
        
        toast.innerHTML = `${icon}<span class="font-sans text-sm font-medium">${message}</span>`;
        
        document.body.appendChild(toast);

        // Animación entrada
        requestAnimationFrame(() => toast.classList.remove('translate-y-10', 'opacity-0'));

        // Auto eliminar
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-10');
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }
});
</script>
