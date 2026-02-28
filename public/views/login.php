<?php
// public/views/login.php
?>
<div class="min-h-screen grid grid-cols-1 md:grid-cols-2">
    <!-- Columna Izquierda (Imagen) -->
    <div class="h-screen hidden md:block">
        <img src="<?= get_config('login_image', 'public/img/login.png') ?>" 
             alt="Perfume bottle on a reflective surface" 
             class="w-full h-full object-cover">
    </div>

    <!-- Columna Derecha (Formulario) -->
    <div class="bg-white flex flex-col justify-center items-center p-8 md:p-12">
        <div class="w-full max-w-md">
            <!-- Logo/Nombre Tienda -->
            <a href="index.php" class="font-serif text-3xl tracking-tighter font-bold uppercase text-luxury-matte mb-16 block text-center">
                Villa Luro Store
            </a>

            <!-- Título -->
            <h1 class="font-serif text-5xl md:text-6xl text-luxury-matte mb-12 text-center">
                Bienvenido
            </h1>
            
            <!-- Mensajes de error/éxito -->
            <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 text-base mb-6" role="alert">Registro exitoso. Ahora puede iniciar sesión.</div>
            <?php endif; ?>
            <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_user'): ?>
                <p class="text-red-500 text-xs font-sans mb-4 text-center">Su sesión ha expirado o es inválida. Por favor, inicie sesión de nuevo.</p>
            <?php endif; ?>
            <?php if ($error): ?>
                <p class="text-red-500 text-xs font-sans mb-4 text-center"><?= $error ?></p>
            <?php endif; ?>

            <!-- Formulario -->
            <form method="post" class="space-y-8">
                <div>
                    <input type="email" id="email" name="email" placeholder="Correo Electrónico" required 
                           class="w-full bg-transparent border-0 border-b border-gray-300 py-3 text-base focus:outline-none focus:ring-0 focus:border-luxury-gold transition-colors duration-300 font-sans placeholder:text-gray-400">
                </div>
                <div class="relative">
                    <input type="password" id="password" name="password" placeholder="Contraseña" required 
                           class="w-full bg-transparent border-0 border-b border-gray-300 py-3 text-base focus:outline-none focus:ring-0 focus:border-luxury-gold transition-colors duration-300 font-sans placeholder:text-gray-400 pr-10">
                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-luxury-gold focus:outline-none">
                        <i class="fas fa-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>

                <div class="flex justify-between items-center font-sans text-sm">
                    <label class="flex items-center cursor-pointer text-gray-600">
                        <input type="checkbox" name="remember" class="h-4 w-4 text-luxury-gold border-gray-300 rounded focus:ring-luxury-gold">
                        <span class="ml-2">Recordarme</span>
                    </label>
                    <a href="index.php?page=recuperar-password" class="text-gray-400 hover:text-luxury-gold transition-colors duration-300">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="w-full bg-black text-luxury-gold py-4 uppercase tracking-widest font-bold text-sm hover:bg-luxury-gold hover:text-black transition-all duration-300">
                    Iniciar Sesión
                </button>
            </form>

            <!-- Enlace a Registro -->
            <div class="mt-12 text-center text-sm text-gray-500 font-sans">
                <p>¿No tiene una cuenta? <a href="index.php?page=registro" class="text-luxury-gold font-semibold hover:underline">Regístrese aquí</a></p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const icon = document.getElementById('togglePasswordIcon');

    if (togglePassword && passwordInput && icon) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }
});
</script>
