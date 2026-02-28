<?php
// public/views/quienes-somos.php
?>
<!-- Hero Section -->
<div class="relative h-[50vh] bg-luxury-matte flex items-center justify-center text-center text-white">
    <div class="absolute inset-0 about-us-banner-bg bg-cover bg-center opacity-40"></div>
    <div class="relative z-10 px-6">
        <h1 class="font-serif text-5xl md:text-7xl">El Arte de la Fragancia</h1>
        <p class="mt-4 max-w-2xl mx-auto font-light text-lg text-gray-300">Un universo donde cada aroma cuenta una historia de elegancia, pasión y exclusividad.</p>
    </div>
</div>

<div class="bg-luxury-bone py-24">
    <div class="container mx-auto px-6">

        <!-- Nuestra Historia Section -->
        <div class="flex flex-col lg:flex-row items-center gap-16 mb-24">
            <div class="lg:w-1/2">
                <span class="text-luxury-gold uppercase tracking-[0.3em] text-[10px] font-bold">Desde 2010</span>
                <h2 class="font-serif text-4xl mt-4 mb-6">Nuestra Historia</h2>
                <p class="font-light text-gray-600 leading-relaxed mb-4">
                    Fundada con la visión de ser más que una perfumería, Luxe nació como un santuario para los conocedores de fragancias. Nuestro viaje comenzó con una selección curada de las casas de perfumes más veneradas del mundo, con el compromiso de ofrecer una autenticidad y una calidad inigualables.
                </p>
                <p class="font-light text-gray-600 leading-relaxed">
                    Hoy, somos un destino de referencia para quienes buscan lo extraordinario, un lugar donde el patrimonio se encuentra con la innovación en el delicado arte de la perfumería.
                </p>
            </div>
            <div class="lg:w-1/2">
                <img src="<?= get_config('about_image', 'public/img/about-us-1.jpg') ?>" alt="Fundadores de Luxe" class="w-full h-auto shadow-xl rounded-sm">
            </div>
        </div>

        <!-- Filosofía Section -->
        <div class="text-center max-w-3xl mx-auto mb-24">
            <h2 class="font-serif text-4xl mb-6">La Filosofía Luxe</h2>
            <p class="font-light text-gray-600 leading-relaxed text-lg">
                Creemos que un perfume es una firma invisible, una expresión de identidad. Por eso, nuestra filosofía se centra en tres pilares: <strong>Exclusividad</strong> en nuestra selección, <strong>Conocimiento</strong> en nuestro asesoramiento y una <strong>Experiencia</strong> de compra que deleita los sentidos.
            </p>
        </div>

        <!-- Calidad y Presentación Section -->
        <div class="mb-24">
            <h2 class="font-serif text-4xl text-center mb-16">Excelencia en Cada Detalle</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-center">
                <!-- Frasco -->
                <div class="p-8 bg-white rounded-sm shadow-sm border border-gray-100 hover:border-luxury-gold/30 transition-colors group">
                    <div class="w-16 h-16 mx-auto bg-luxury-bone rounded-full flex items-center justify-center mb-6 text-luxury-matte group-hover:bg-luxury-gold group-hover:text-white transition-colors">
                        <i class="fas fa-wine-bottle text-2xl"></i>
                    </div>
                    <h3 class="font-serif text-xl mb-3 text-luxury-matte">Presentación del Frasco</h3>
                    <p class="font-light text-gray-600 text-sm leading-relaxed">
                        Nuestras esencias se conservan en frascos de vidrio importado de alta pureza, con atomizadores de bruma fina que garantizan una dispersión perfecta sobre la piel y una estética sofisticada.
                    </p>
                </div>

                <!-- Packaging -->
                <div class="p-8 bg-white rounded-sm shadow-sm border border-gray-100 hover:border-luxury-gold/30 transition-colors group">
                    <div class="w-16 h-16 mx-auto bg-luxury-bone rounded-full flex items-center justify-center mb-6 text-luxury-matte group-hover:bg-luxury-gold group-hover:text-white transition-colors">
                        <i class="fas fa-gift text-2xl"></i>
                    </div>
                    <h3 class="font-serif text-xl mb-3 text-luxury-matte">Experiencia Unboxing</h3>
                    <p class="font-light text-gray-600 text-sm leading-relaxed">
                        Recibirá su perfume en un estuche rígido de lujo, sellado y protegido. Cada detalle del empaque ha sido pensado para que recibirlo sea un momento memorable.
                    </p>
                </div>

                <!-- ANMAT -->
                <div class="p-8 bg-white rounded-sm shadow-sm border border-gray-100 hover:border-luxury-gold/30 transition-colors group">
                    <div class="w-16 h-16 mx-auto bg-luxury-bone rounded-full flex items-center justify-center mb-6 text-luxury-matte group-hover:bg-luxury-gold group-hover:text-white transition-colors">
                        <i class="fas fa-check-circle text-2xl"></i>
                    </div>
                    <h3 class="font-serif text-xl mb-3 text-luxury-matte">Aprobación ANMAT</h3>
                    <p class="font-light text-gray-600 text-sm leading-relaxed">
                        Seguridad y confianza garantizada. Todos nuestros productos cuentan con la aprobación de la Administración Nacional de Medicamentos, Alimentos y Tecnología Médica (ANMAT).
                    </p>
                </div>
            </div>
        </div>

        <!-- Cita -->
        <div class="border-y border-gray-200 py-16 text-center">
            <blockquote class="font-serif text-3xl italic text-luxury-matte max-w-4xl mx-auto leading-relaxed">
                "El perfume es la forma más intensa del recuerdo. Debe ser como un objeto de arte, personal y atemporal."
            </blockquote>
            <p class="mt-6 text-sm tracking-widest uppercase text-gray-500">- Fundador de Luxe</p>
        </div>
    </div>
</div>
