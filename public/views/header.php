<?php
// public/views/header.php

// Obtener marcas para el menú de navegación
$nav_brands = $pdo->query("SELECT id, nombre FROM marcas ORDER BY nombre LIMIT 5")->fetchAll();

// Obtener el número de items en el carrito
$cart_count = 0;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT SUM(cantidad) FROM carrito WHERE usuario_id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $cart_count = (int)$stmt->fetchColumn();
} elseif (isset($_SESSION['carrito'])) {
    $cart_count = array_sum(array_column($_SESSION['carrito'], 'cantidad'));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villa Luro Store</title>
    <meta name="description" content="Catálogo y venta de perfumes de lujo.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="public/css/custom.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'serif': ['Playfair Display', 'serif'],
                        'sans': ['Montserrat', 'sans-serif'],
                    },
                    colors: {
                        'luxury-gold': '#D4AF37',
                        'luxury-matte': '#1A1A1A',
                        'luxury-bone': '#F9F7F2',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-white text-luxury-matte font-sans">
    <header class="sticky-header border-b border-gray-200 relative">
        <div class="container mx-auto flex justify-between items-center py-6 px-6">
            <a href="index.php" class="flex items-center gap-3">
                <img src="public/img/logo.png" alt="Villa Luro Store Logo" class="h-20">
                <span class="font-serif text-2xl tracking-tighter font-bold uppercase">Villa Luro Store</span>
            </a>
            
            <button id="mobile-menu-btn" class="md:hidden flex flex-col justify-center items-center w-8 h-8 space-y-1.5 focus:outline-none z-50">
                <span class="block w-6 h-0.5 bg-luxury-matte transition-all duration-300 ease-in-out origin-center"></span>
                <span class="block w-6 h-0.5 bg-luxury-matte transition-all duration-300 ease-in-out"></span>
                <span class="block w-6 h-0.5 bg-luxury-matte transition-all duration-300 ease-in-out origin-center"></span>
            </button>

            <nav class="hidden md:block">
                <ul class="flex space-x-8 text-[11px] uppercase tracking-widest font-semibold">
                    <li><a href="index.php?page=catalogo" class="nav-link hover:text-luxury-gold hover:text-sm transition-all duration-300 ease-in-out">Colección</a></li>
                    <li class="relative group">
                        <a href="#" class="nav-link hover:text-luxury-gold hover:text-sm transition-all duration-300 ease-in-out">Marcas <i class="fas fa-chevron-down text-[8px] ml-1"></i></a>
                        <ul class="absolute hidden group-hover:block bg-white shadow-xl py-4 w-48 z-50 top-full left-0 border-t-2 border-luxury-gold">
                            <?php foreach($nav_brands as $nb): ?>
                                <li><a href="index.php?page=catalogo&marca=<?= $nb['id'] ?>" class="block px-6 py-2 text-[10px] hover:bg-luxury-bone hover:text-luxury-gold hover:text-xs transition-all duration-300 ease-in-out"><?= htmlspecialchars($nb['nombre']) ?></a></li>
                            <?php endforeach; ?>
                            <li class="border-t border-gray-100 mt-2 pt-2">
                                <a href="index.php?page=catalogo" class="block px-6 py-2 text-[10px] italic hover:text-luxury-gold hover:text-xs transition-all duration-300 ease-in-out">Ver todas</a>
                            </li>
                        </ul>
                    </li>
                    <li><a href="index.php?page=quienes-somos" class="nav-link hover:text-luxury-gold hover:text-sm transition-all duration-300 ease-in-out">Quienes Somos</a></li>
                    <?php if (isLoggedIn()): ?>
                        <?php if ($_SESSION['usuario_id'] == 1): ?>
                            <li><a href="index.php?page=admin_dashboard" class="nav-link text-red-500 hover:text-red-700 hover:text-sm font-bold transition-all duration-300 ease-in-out">Panel Admin</a></li>
                        <?php endif; ?>
                        <li><a href="index.php?page=mi-cuenta" class="nav-link hover:text-luxury-gold hover:text-sm transition-all duration-300 ease-in-out">Perfil</a></li>
                        <li><a href="index.php?page=logout" class="nav-link hover:text-luxury-gold hover:text-sm transition-all duration-300 ease-in-out">Salir</a></li>
                    <?php else: ?>
                        <li><a href="index.php?page=login" class="nav-link hover:text-luxury-gold hover:text-sm transition-all duration-300 ease-in-out">Acceso</a></li>
                    <?php endif; ?>
                    <li class="relative">
                        <a href="index.php?page=carrito" id="cart-icon-container" class="hover:text-luxury-gold transition-colors relative block">
                            <i class="fas fa-shopping-bag text-lg"></i>
                            <?php if ($cart_count > 0): ?>
                                <span id="cart-count" class="absolute -top-2 -right-3 bg-luxury-gold text-white text-[9px] rounded-full h-4 w-4 flex items-center justify-center"><?= $cart_count ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Menú Móvil -->
        <div id="mobile-menu" class="md:hidden absolute top-full left-0 w-full bg-white border-t border-gray-100 shadow-lg z-50 overflow-hidden transition-all duration-500 ease-in-out max-h-0 opacity-0">
            <ul class="flex flex-col p-6 space-y-4 text-xs uppercase tracking-widest font-semibold text-luxury-matte">
                <li><a href="index.php?page=catalogo" class="block hover:text-luxury-gold">Colección</a></li>
                
                <li class="border-b border-gray-100 pb-2">
                    <span class="block mb-2 text-gray-400">Marcas</span>
                    <ul class="pl-4 space-y-2 border-l-2 border-luxury-gold">
                        <?php foreach($nav_brands as $nb): ?>
                            <li><a href="index.php?page=catalogo&marca=<?= $nb['id'] ?>" class="block hover:text-luxury-gold"><?= htmlspecialchars($nb['nombre']) ?></a></li>
                        <?php endforeach; ?>
                        <li><a href="index.php?page=catalogo" class="block italic text-gray-400 hover:text-luxury-gold">Ver todas</a></li>
                    </ul>
                </li>

                <li><a href="index.php?page=quienes-somos" class="block hover:text-luxury-gold">Quienes Somos</a></li>
                
                <?php if (isLoggedIn()): ?>
                    <?php if ($_SESSION['usuario_id'] == 1): ?>
                        <li><a href="index.php?page=admin_dashboard" class="block text-red-500 font-bold">Panel Admin</a></li>
                    <?php endif; ?>
                    <li><a href="index.php?page=mi-cuenta" class="block hover:text-luxury-gold">Perfil</a></li>
                    <li><a href="index.php?page=logout" class="block hover:text-luxury-gold">Cerrar Sesión</a></li>
                <?php else: ?>
                    <li><a href="index.php?page=login" class="block hover:text-luxury-gold">Acceso / Registro</a></li>
                <?php endif; ?>
                
                <li class="pt-2 border-t border-gray-100">
                    <a href="index.php?page=carrito" class="flex items-center gap-2 hover:text-luxury-gold">
                        <i class="fas fa-shopping-bag"></i> Carrito 
                        <?php if ($cart_count > 0): ?>
                            <span class="bg-luxury-gold text-white text-[9px] rounded-full h-4 w-4 flex items-center justify-center"><?= $cart_count ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </div>
    </header>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('mobile-menu-btn');
            const menu = document.getElementById('mobile-menu');
            const spans = btn ? btn.querySelectorAll('span') : [];
            
            if (btn && menu) {
                btn.addEventListener('click', function() {
                    const isClosed = menu.classList.contains('max-h-0');
                    
                    if (isClosed) {
                        menu.classList.remove('max-h-0', 'opacity-0');
                        menu.classList.add('max-h-screen', 'opacity-100');
                        // Animación a X (Abrir)
                        spans[0].classList.add('rotate-45', 'translate-y-2');
                        spans[1].classList.add('opacity-0');
                        spans[2].classList.add('-rotate-45', '-translate-y-2');
                    } else {
                        menu.classList.add('max-h-0', 'opacity-0');
                        menu.classList.remove('max-h-screen', 'opacity-100');
                        // Animación a Hamburguesa (Cerrar)
                        spans[0].classList.remove('rotate-45', 'translate-y-2');
                        spans[1].classList.remove('opacity-0');
                        spans[2].classList.remove('-rotate-45', '-translate-y-2');
                    }
                });
            }
        });
    </script>