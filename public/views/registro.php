<?php
// public/views/registro.php
?>
<div class="min-h-[80vh] flex items-center py-12">
    <div class="container mx-auto">
        <div class="flex flex-col lg:flex-row w-10/12 lg:w-8/12 bg-white rounded-xl mx-auto shadow-lg overflow-hidden">
            <!-- Image Section -->
            <div class="w-full lg:w-1/2 flex flex-col items-center justify-center p-12 bg-no-repeat bg-cover bg-center bg-register">
                <h1 class="text-luxury-gold text-4xl font-serif mb-3">Únase a Villa Luro Store</h1>
                <p class="text-luxury-gold font-light text-center">Cree una cuenta para disfrutar de beneficios y una experiencia de compra fluida.</p>
            </div>
            <!-- Form Section -->
            <div class="w-full lg:w-1/2 py-16 px-12">
                <h2 class="text-3xl mb-4 font-serif">Crear una Cuenta</h2>
                <p class="mb-8 text-sm text-gray-500 font-light">Complete sus datos para registrarse.</p>
                <?php if ($error): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 text-sm mb-6" role="alert"><?= $error ?></div>
                <?php endif; ?>
                <form method="post" aria-label="Formulario de registro">
                    <input type="text" name="nombre" placeholder="Nombre Completo" required class="w-full bg-transparent border-b py-3 px-1 mb-6 text-sm focus:outline-none focus:border-luxury-gold transition-colors">
                    <input type="email" name="email" placeholder="Correo Electrónico" required class="w-full bg-transparent border-b py-3 px-1 mb-6 text-sm focus:outline-none focus:border-luxury-gold transition-colors">
                    <input type="password" name="password" placeholder="Contraseña" required class="w-full bg-transparent border-b py-3 px-1 mb-6 text-sm focus:outline-none focus:border-luxury-gold transition-colors">
                    <input type="password" name="password2" placeholder="Confirmar Contraseña" required class="w-full bg-transparent border-b py-3 px-1 mb-8 text-sm focus:outline-none focus:border-luxury-gold transition-colors">
                    <button type="submit" class="w-full bg-luxury-matte text-white py-4 text-[10px] uppercase tracking-widest font-bold hover:bg-luxury-gold transition-all duration-500">Crear Cuenta</button>
                </form>
                <div class="mt-8 text-center text-xs text-gray-500">
                    <p>¿Ya tiene una cuenta? <a href="index.php?page=login" class="text-luxury-gold font-semibold hover:underline">Inicie sesión</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
