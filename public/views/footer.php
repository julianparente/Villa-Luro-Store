<?php
// public/views/footer.php
?>
<footer class="bg-luxury-matte text-gray-400 py-20">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
            <!-- Columna 1: Marca -->
            <div>
                <a href="index.php" class="font-serif text-2xl mb-6 uppercase tracking-tighter block hover:text-white transition-colors">Villa Luro Store</a>
                <p class="text-gray-400 text-sm leading-relaxed font-light">
                    Curaduría de las fragancias más exclusivas del mundo. Una experiencia sensorial diseñada para quienes exigen lo extraordinario.
                </p>
                <div class="mt-4">
                    <a href="index.php?page=quienes-somos" class="text-gray-400 text-sm font-light hover:text-luxury-gold transition-colors">Nuestra Historia</a>
                </div>
            </div>

            <!-- Columna 2: Navegación -->
            <div>
                <h4 class="text-[10px] uppercase tracking-[0.2em] mb-6 font-bold text-white">Explorar</h4>
                <ul class="space-y-4 text-sm font-light text-gray-400">
                    <li><a href="index.php?page=catalogo" class="hover:text-luxury-gold transition-colors">Catálogo Completo</a></li>
                    <li><a href="index.php?page=catalogo&categoria=masculino" class="hover:text-luxury-gold transition-colors">Fragancias Masculinas</a></li>
                    <li><a href="index.php?page=catalogo&categoria=femenino" class="hover:text-luxury-gold transition-colors">Fragancias Femeninas</a></li>
                    <li><a href="<?= isLoggedIn() ? 'index.php?page=mi-cuenta' : 'index.php?page=registro' ?>" class="hover:text-luxury-gold transition-colors">Mi Cuenta</a></li>
                </ul>
            </div>

            <!-- Columna 3: Contacto -->
            <div>
                <h4 class="text-[10px] uppercase tracking-[0.2em] mb-6 font-bold text-white">Contacto</h4>
                <ul class="space-y-4 text-sm font-light text-gray-400">
                    <li><a href="https://instagram.com/villalurostore" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 hover:text-luxury-gold transition-colors"><i class="fab fa-instagram w-4 text-center"></i><span>@villalurostore</span></a></li>
                    <li><a href="https://wa.me/5491123700575" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 hover:text-luxury-gold transition-colors"><i class="fab fa-whatsapp w-4 text-center"></i><span>+54 9 11 2370-0575</span></a></li>
                </ul>
            </div>

            <!-- Columna 4: Newsletter -->
            <div>
                <h4 class="text-[10px] uppercase tracking-[0.2em] mb-6 font-bold text-white">Newsletter</h4>
                <p class="text-gray-400 text-sm mb-4 font-light">Suscríbase para recibir lanzamientos exclusivos.</p>
                <form id="newsletter-form" method="post" action="index.php?page=api_subscribe" class="flex border-b border-gray-700 pb-2">
                    <input type="email" name="email" placeholder="Su correo electrónico" required class="bg-transparent border-none text-sm w-full focus:outline-none text-white placeholder-gray-600">
                    <button type="submit" class="text-[10px] uppercase tracking-widest hover:text-white transition-colors font-semibold">Unirse</button>
                </form>
            </div>
        </div>

        <div class="mt-20 pt-8 border-t border-gray-800 flex flex-col md:flex-row justify-between items-center text-[10px] uppercase tracking-widest text-gray-500">
            <p>&copy; <?php echo date('Y'); ?> Villa Luro Store. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

<!-- Botón Flotante de WhatsApp -->
<a href="https://wa.me/5491123700575?text=Hola!%20Me%20interesan%20sus%20productos." target="_blank" rel="noopener noreferrer" class="fixed bottom-6 right-6 bg-[#25D366] text-white p-4 rounded-full shadow-lg hover:scale-110 transition-transform duration-300 z-50 w-14 h-14 flex items-center justify-center">
    <i class="fab fa-whatsapp text-3xl"></i>
</a>
</body>
</html>