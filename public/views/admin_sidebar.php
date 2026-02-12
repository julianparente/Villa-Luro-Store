<?php
// public/views/admin_sidebar.php
$current_page = $_GET['page'] ?? 'admin_dashboard';
?>
<aside class="w-64 bg-luxury-matte text-gray-300 p-6 flex flex-col flex-shrink-0 shadow-lg">
    <a href="index.php?page=admin_dashboard" class="flex items-center gap-3 mb-10">
        <img src="public/img/logo.png" alt="Logo" class="h-12">
        <span class="font-bold text-lg text-white uppercase tracking-widest">Admin</span>
    </a>
    <nav class="flex-grow space-y-2">
        <a href="index.php?page=admin_dashboard" class="sidebar-link <?= $current_page == 'admin_dashboard' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt fa-fw w-6"></i><span>Resumen</span></a>
        <a href="index.php?page=admin_productos" class="sidebar-link <?= in_array($current_page, ['admin_productos', 'admin_producto_form']) ? 'active' : '' ?>"><i class="fas fa-wine-bottle fa-fw w-6"></i><span>Perfumes</span></a>
        <a href="index.php?page=admin_marcas" class="sidebar-link <?= $current_page == 'admin_marcas' ? 'active' : '' ?>"><i class="fas fa-tags fa-fw w-6"></i><span>Marcas</span></a>
        <a href="index.php?page=admin_pedidos" class="sidebar-link <?= $current_page == 'admin_pedidos' ? 'active' : '' ?>"><i class="fas fa-box-open fa-fw w-6"></i><span>Pedidos</span></a>
        <a href="index.php?page=admin_suscripciones" class="sidebar-link <?= $current_page == 'admin_suscripciones' ? 'active' : '' ?>"><i class="fas fa-envelope-open-text fa-fw w-6"></i><span>Suscriptores</span></a>
    </nav>
    <div class="mt-auto">
        <a href="index.php" class="sidebar-link text-sm"><i class="fas fa-globe fa-fw w-6"></i><span>Ver Tienda</span></a>
        <a href="index.php?page=logout" class="sidebar-link mt-2 text-sm text-red-400 hover:bg-red-900/50"><i class="fas fa-sign-out-alt fa-fw w-6"></i><span>Cerrar Sesión</span></a>
    </div>
</aside>

<style>
    .sidebar-link { display: flex; align-items: center; padding: 0.75rem 1rem; border-radius: 0.5rem; transition: background-color 0.2s, color 0.2s; font-weight: 500; }
    .sidebar-link.active { background-color: #D4AF37; color: #1A1A1A; font-weight: 700; }
    .sidebar-link:not(.active):hover { background-color: #374151; color: white; }
</style>