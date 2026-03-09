<?php
// public/views/api_get_products.php

// Asegurar acceso a BD y Sesión (si no vienen del index)
if (!isset($pdo)) {
    require_once __DIR__ . '/../../config/session.php';
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../config/functions.php';
}

// Configuración inicial
$limit = 12; // Cantidad de productos por carga
$offset = filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT) ?: 0;

// Filtros recibidos por GET
$categoria = filter_input(INPUT_GET, 'categoria', FILTER_VALIDATE_INT);
$marca = filter_input(INPUT_GET, 'marca', FILTER_VALIDATE_INT);
$orden = $_GET['orden'] ?? 'reciente';
$busqueda = trim($_GET['busqueda'] ?? '');

// Construcción de la consulta SQL dinámica
$sql = "SELECT p.*, m.nombre as marca_nombre 
        FROM perfumes p 
        JOIN marcas m ON p.marca_id = m.id 
        WHERE 1=1";
$params = [];

if ($categoria) {
    $sql .= " AND p.categoria_id = ?";
    $params[] = $categoria;
}

if ($marca) {
    $sql .= " AND p.marca_id = ?";
    $params[] = $marca;
}

if ($busqueda) {
    $sql .= " AND (p.nombre LIKE ? OR m.nombre LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}

// Ordenamiento
switch ($orden) {
    case 'precio_asc':
        $sql .= " ORDER BY p.precio ASC";
        break;
    case 'precio_desc':
        $sql .= " ORDER BY p.precio DESC";
        break;
    case 'nombre':
        $sql .= " ORDER BY p.nombre ASC";
        break;
    default: // reciente
        $sql .= " ORDER BY p.id DESC";
        break;
}

// Paginación (OFFSET y LIMIT)
$sql .= " LIMIT $limit OFFSET $offset";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generar HTML de respuesta
    $html = '';
    foreach ($productos as $producto) {
        // Renderizamos la tarjeta de producto. 
        // NOTA: Asegúrate de que estas clases coincidan con tu diseño actual en catalogo.php
        $precio = number_format($producto['precio'], 2);
        $imagen = htmlspecialchars($producto['imagen_url']);
        $nombre = htmlspecialchars($producto['nombre']);
        $marcaNombre = htmlspecialchars($producto['marca_nombre']);
        $id = $producto['id'];

        $html .= <<<HTML
        <div class="group relative fade-in-up">
            <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-md bg-gray-200 lg:aspect-none group-hover:opacity-75 lg:h-80 relative">
                <img src="{$imagen}" alt="{$nombre}" class="h-full w-full object-cover object-center lg:h-full lg:w-full">
                <!-- Botón rápido de añadir al carrito -->
                <button onclick="addToCart({$id})" class="absolute bottom-4 right-4 bg-white p-3 rounded-full shadow-lg text-luxury-matte hover:text-luxury-gold transition-colors z-10">
                    <i class="fas fa-shopping-bag"></i>
                </button>
            </div>
            <div class="mt-4 flex justify-between">
                <div>
                    <h3 class="text-sm text-gray-700 font-serif">
                        <a href="index.php?page=producto&id={$id}">
                            <span aria-hidden="true" class="absolute inset-0"></span>
                            {$nombre}
                        </a>
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">{$marcaNombre}</p>
                </div>
                <p class="text-sm font-medium text-gray-900">\${$precio}</p>
            </div>
        </div>
HTML;
    }

    // Devolver JSON
    header('Content-Type: application/json');
    echo json_encode(['html' => $html, 'count' => count($productos)]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al cargar productos']);
}