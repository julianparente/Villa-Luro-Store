<?php
// public/views/admin_sidebar.php
$current_page = $_GET['page'] ?? 'admin_dashboard';
?>
<!-- Mobile Header (Visible solo en móvil) -->
<div class="md:hidden bg-luxury-matte text-white p-4 flex justify-between items-center shadow-md z-30 relative">
    <div class="flex items-center gap-2">
        <img src="public/img/logo.png" alt="Logo" class="h-8">
        <span class="font-bold text-sm uppercase tracking-widest">Admin Panel</span>
    </div>
    <button id="sidebar-toggle" class="text-white focus:outline-none p-2">
        <i class="fas fa-bars fa-lg"></i>
    </button>
</div>

<!-- Sidebar (Drawer en móvil, Fijo en desktop) -->
<aside id="sidebar" class="bg-luxury-matte text-gray-300 w-64 flex-col flex-shrink-0 shadow-lg fixed inset-y-0 left-0 z-50 transform -translate-x-full transition-transform duration-300 md:relative md:translate-x-0 md:flex md:shadow-none h-full md:h-auto overflow-y-auto">
    <div class="p-6 flex flex-col h-full">
        <div class="flex justify-between items-center mb-10">
            <a href="index.php?page=admin_dashboard" class="flex items-center gap-3">
                <img src="<?= get_config('logo', 'public/img/logo.png') ?>" alt="Logo" class="h-12 object-contain">
                <span class="font-bold text-lg text-white uppercase tracking-widest">Admin</span>
            </a>
            <!-- Botón cerrar solo en móvil -->
            <button id="sidebar-close" class="md:hidden text-gray-400 hover:text-white focus:outline-none">
                <i class="fas fa-times fa-lg"></i>
            </button>
        </div>
        
        <nav class="flex-grow space-y-2">
            <a href="index.php?page=admin_dashboard" class="sidebar-link <?= $current_page == 'admin_dashboard' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt fa-fw w-6"></i><span>Resumen</span></a>
            <a href="index.php?page=admin_productos" class="sidebar-link <?= in_array($current_page, ['admin_productos', 'admin_producto_form']) ? 'active' : '' ?>"><i class="fas fa-wine-bottle fa-fw w-6"></i><span>Perfumes</span></a>
            <a href="index.php?page=admin_marcas" class="sidebar-link <?= $current_page == 'admin_marcas' ? 'active' : '' ?>"><i class="fas fa-tags fa-fw w-6"></i><span>Marcas</span></a>
            <a href="index.php?page=admin_pedidos" class="sidebar-link <?= $current_page == 'admin_pedidos' ? 'active' : '' ?>"><i class="fas fa-box-open fa-fw w-6"></i><span>Pedidos</span></a>
            <a href="index.php?page=admin_suscripciones" class="sidebar-link <?= $current_page == 'admin_suscripciones' ? 'active' : '' ?>"><i class="fas fa-envelope-open-text fa-fw w-6"></i><span>Suscriptores</span></a>
            <a href="index.php?page=admin_config" class="sidebar-link <?= $current_page == 'admin_config' ? 'active' : '' ?>"><i class="fas fa-cog fa-fw w-6"></i><span>Configuración</span></a>
        </nav>
        
        <div class="mt-auto pt-6">
            <a href="index.php" class="sidebar-link text-sm"><i class="fas fa-globe fa-fw w-6"></i><span>Ver Tienda</span></a>
            <a href="index.php?page=logout" class="sidebar-link mt-2 text-sm text-red-400 hover:bg-red-900/50"><i class="fas fa-sign-out-alt fa-fw w-6"></i><span>Cerrar Sesión</span></a>
        </div>
    </div>
</aside>

<!-- Overlay para móvil -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden backdrop-blur-sm transition-opacity"></div>

<style>
    .sidebar-link { display: flex; align-items: center; padding: 0.75rem 1rem; border-radius: 0.5rem; transition: background-color 0.2s, color 0.2s; font-weight: 500; }
    .sidebar-link.active { background-color: #D4AF37; color: #1A1A1A; font-weight: 700; }
    .sidebar-link:not(.active):hover { background-color: #374151; color: white; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('sidebar-toggle');
    const closeBtn = document.getElementById('sidebar-close');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    function toggleSidebar() {
        const isClosed = sidebar.classList.contains('-translate-x-full');
        if (isClosed) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }
    }

    if(toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
    if(closeBtn) closeBtn.addEventListener('click', toggleSidebar);
    if(overlay) overlay.addEventListener('click', toggleSidebar);
});
</script>