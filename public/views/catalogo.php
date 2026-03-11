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

// Obtener productos en promoción para el banner
$stmt_promo = $pdo->query("SELECT p.id, p.nombre, p.imagen_url, m.nombre as marca_nombre FROM perfumes p JOIN marcas m ON p.marca_id = m.id WHERE p.en_promocion = 1 LIMIT 3");
$promo_perfumes = $stmt_promo->fetchAll();
$precio_combo = 30000;

?>

<div class="container mx-auto py-20 px-6">

    <!-- Banner de Promoción Semanal -->
    <?php if (count($promo_perfumes) === 3): ?>
    <div class="bg-luxury-matte p-8 md:p-12 rounded-lg mb-20 border border-luxury-gold shadow-lg">
        <div class="flex flex-col md:flex-row items-center gap-8">
            <div class="flex-grow text-center md:text-left">
                <span class="text-white uppercase tracking-[0.3em] text-[10px] font-bold">Oferta de la Semana</span>
                <h2 class="font-serif text-4xl mt-2 mb-4 text-luxury-gold">Combo Exclusivo</h2>
                <p class="text-gray-300 font-light max-w-lg mb-6">Lleva estos tres perfumes seleccionados y paga un precio único.</p>
                <div class="flex items-baseline justify-center md:justify-start gap-4 mb-8">
                    <span class="font-serif text-5xl text-luxury-gold font-bold"><?= format_currency($precio_combo) ?></span>
                </div>
                <form method="post" action="index.php?page=api_add_to_cart" class="add-to-cart-form">
                    <?php foreach ($promo_perfumes as $p): ?>
                        <input type="hidden" name="perfume_id[]" value="<?= $p['id'] ?>">
                    <?php endforeach; ?>
                    <button type="submit" class="bg-luxury-gold text-luxury-matte px-10 py-4 text-xs uppercase tracking-widest font-bold hover:bg-luxury-gold/80 transition-colors">Añadir Combo al Carrito</button>
                </form>
            </div>
            <div class="flex -space-x-16 justify-center">
                <?php foreach ($promo_perfumes as $p): ?>
                    <img src="<?= htmlspecialchars($p['imagen_url']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>" class="w-32 h-40 object-cover rounded-lg shadow-lg border-4 border-white transform hover:scale-110 hover:z-10 transition-transform duration-300">
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="flex flex-col lg:flex-row gap-16">
        <!-- Sidebar de Filtros -->
        <aside class="w-full lg:w-1/4">
            <div class="sticky top-32 bg-white p-8 rounded-lg shadow-sm border border-gray-200">
                <h2 class="font-serif text-3xl mb-8">Filtros</h2>
                <div class="border-t border-gray-100 pt-8">
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
            </div>
        </aside>

        <!-- Grilla de Productos -->
        <div class="w-full lg:w-3/4">
            <div id="product-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                <?php foreach ($perfumes as $perfume): ?>
                    <div class="group border border-gray-200 p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300 flex flex-col">
                        <div class="relative overflow-hidden bg-white mb-4 aspect-[3/4]">
                            <a href="index.php?page=producto&id=<?= $perfume['id'] ?>" class="block w-full h-full">
                                <img src="<?= htmlspecialchars($perfume['imagen_url']) ?>" 
                                     alt="<?= htmlspecialchars($perfume['nombre']) ?>" 
                                     class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                            </a>
                        </div>
                        
                        <div class="text-center flex-grow flex flex-col">
                            <div class="flex-grow">
                                <p class="text-[10px] uppercase tracking-[0.2em] text-gray-400 mb-1"><?= htmlspecialchars($perfume['marca_nombre']) ?></p>
                                <h2 class="font-serif text-xl mb-2 h-14 overflow-hidden">
                                    <a href="index.php?page=producto&id=<?= $perfume['id'] ?>" class="hover:text-luxury-gold transition-colors"><?= htmlspecialchars($perfume['nombre']) ?></a>
                                </h2>
                                <?php if (isset($perfume['precio_lista']) && $perfume['precio_lista'] > $perfume['precio']): 
                                    $descuento = round((($perfume['precio_lista'] - $perfume['precio']) / $perfume['precio_lista']) * 100);
                                ?>
                                    <div class="mb-2">
                                        <span class="bg-red-600 text-white text-xs font-bold px-2 py-1 rounded"><?= $descuento ?>% OFF</span>
                                    </div>
                                <?php endif; ?>
                                <div class="flex items-baseline justify-center gap-2 mb-4">
                                    <p class="text-luxury-gold font-semibold tracking-widest">$<?= number_format($perfume['precio'], 2) ?></p>
                                    <?php if (isset($perfume['precio_lista']) && $perfume['precio_lista'] > $perfume['precio']): ?>
                                        <p class="text-gray-400 line-through text-sm">$<?= number_format($perfume['precio_lista'], 2) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <form method="post" action="index.php?page=api_add_to_cart" class="add-to-cart-form mt-auto">
                                <input type="hidden" name="perfume_id" value="<?= $perfume['id'] ?>">
                                <button type="submit" 
                                        class="w-full bg-luxury-matte text-white px-4 py-3 text-[10px] uppercase tracking-widest font-bold hover:bg-luxury-gold transition-colors duration-300"
                                        <?= $perfume['stock'] < 1 ? 'disabled' : '' ?>>
                                    <?= $perfume['stock'] < 1 ? 'Agotado' : 'Añadir al Carrito' ?>
                                </button>
                            </form>
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
            <div class="group border border-gray-200 p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300 flex flex-col">
                <div class="relative overflow-hidden bg-white mb-4 aspect-[3/4]">
                    <a href="index.php?page=producto&id=${perfume.id}" class="block w-full h-full">
                        <img src="${escapeHTML(perfume.imagen_url)}" alt="${escapeHTML(perfume.nombre)}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                    </a>
                </div>
                <div class="text-center flex-grow flex flex-col">
                    <div class="flex-grow">
                        <p class="text-[10px] uppercase tracking-[0.2em] text-gray-400 mb-1">${escapeHTML(perfume.marca_nombre)}</p>
                        <h2 class="font-serif text-xl mb-2 h-14 overflow-hidden">
                            <a href="index.php?page=producto&id=${perfume.id}" class="hover:text-luxury-gold transition-colors">${escapeHTML(perfume.nombre)}</a>
                        </h2>
                        ${perfume.precio_lista && parseFloat(perfume.precio_lista) > parseFloat(perfume.precio)
                            ? `<div class="mb-2"><span class="bg-red-600 text-white text-xs font-bold px-2 py-1 rounded">${Math.round(((perfume.precio_lista - perfume.precio) / perfume.precio_lista) * 100)}% OFF</span></div>`
                            : ''}
                        <div class="flex items-baseline justify-center gap-2 mb-4">
                            <p class="text-luxury-gold font-semibold tracking-widest">$${priceFormatted}</p>
                            ${perfume.precio_lista && parseFloat(perfume.precio_lista) > parseFloat(perfume.precio)
                                ? `<p class="text-gray-400 line-through text-sm">$${new Intl.NumberFormat('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(perfume.precio_lista)}</p>`
                                : ''}
                        </div>
                    </div>
                    <form method="post" action="index.php?page=api_add_to_cart" class="add-to-cart-form mt-auto">
                        <input type="hidden" name="perfume_id" value="${perfume.id}">
                        <button type="submit" class="w-full bg-luxury-matte text-white px-4 py-3 text-[10px] uppercase tracking-widest font-bold hover:bg-luxury-gold transition-colors duration-300" ${buttonDisabled}>
                            ${buttonText}
                        </button>
                    </form>
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
    // Se cambia de productGrid a document para capturar también el formulario del Banner de Oferta
    document.addEventListener('submit', function(e) {
        if (e.target.matches('.add-to-cart-form')) {
            // Si otro script (como app.js) ya manejó el evento, no hacemos nada
            if (e.defaultPrevented) return;

            e.preventDefault();
            
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
});
</script>
