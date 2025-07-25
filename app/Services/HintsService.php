<?php

namespace App\Services;

class HintsService
{
    /**
     * Palabras clave y descripciones para los hints.
     *
     *
     */
    private array $hints = [
        'elegante' => 'De pie elegantemente, con los pies naturalmente juntos, la mano derecha sosteniendo un bolso negro, la mano izquierda colgando naturalmente, la barbilla ligeramente levantada, cabello corto ligeramente rizado, delicada chaqueta corta de lana rosa con decoración de hebilla de metal, ropa interior blanca, falda negra, medias negras, mocasines de charol negro, fondo de tierra verde claro, simple toma de estudio',

        'urbano' => 'Pose relajada de pie, piernas cruzadas naturalmente, una mano tocando suavemente el cabello, cabello castaño rojizo medio corto, sudadera blanca suelta, pantalones cortos negros, escena de acera, fondo de árbol verde, cerca de hierro, luz natural, estilo casual cotidiano.',

        'energetico' => 'Pose atlética dinámica, una pierna ligeramente hacia adelante, brazos posicionados naturalmente mostrando energía, ropa deportiva brillante, atuendo moderno de fitness, gimnasio o entorno deportivo al aire libre, colores vibrantes, expresión confiada.',

        'dulce' => 'Pose suave con una sonrisa delicada, cabello fluido, vestido de color pastel, joyería delicada, iluminación natural suave, fondo floral o de jardín, apariencia femenina y graciosa.',

        'intelectual' => 'Pose profesional y segura, atuendo de negocios, aspecto moderno y limpio, sosteniendo libros o documentos, entorno de oficina o biblioteca, estilo sofisticado, fondo neutro, apariencia inteligente y casual.'
    ];

    private array $promptVideo = [
        'sugerencia 1' => 'Una modelo da una vuelta elegante para mostrar su atuendo completo, la cámara sigue suavemente su movimiento desde distintos ángulos. El vestido ondea con naturalidad mientras gira, resaltando cada detalle del diseño. Fondo neutro con iluminación profesional de pasarela.',
        'sugerencia 2' => 'Una modelo camina por la pasarela luciendo majestuosas alas estilizadas, como si fuera un ángel de alta costura. El público observa maravillado mientras la iluminación brillante realza la textura de las alas y el vestido. Estilo etéreo y fantasioso',
        'sugerencia 3' => 'Toda la escena transmite una experiencia visual inmersiva y vibrante, con colores intensos y una estética surrealista. Inspirado en el estilo del fotógrafo David LaChapelle, con iluminación teatral, fondos llamativos y poses audaces que rompen la realidad convencional.',
        'sugerencia 4' => 'Una modelo se encuentra en el escenario de la Semana de la Moda de París, rodeada por otras modelos que lucen vestidos de alta costura. La iluminación suave y difusa resalta cada detalle delicado de las prendas, creando una atmósfera mágica y sofisticada.',
        'sugerencia 5' => 'Escena de un desfile de moda sobre una pasarela moderna. Modelos caminan con seguridad, mostrando prendas elegantes y contemporáneas, mientras el público observa desde los costados. Luces de escenario, cámaras y flashes crean una ambientación profesional.'
    ];

    private array $negativePrompts = [
        'opcion 1' => 'Sin artefactos visuales, sin deformaciones corporales, sin desenfoque por movimiento, sin doble exposición, evitar calidad baja, sin duplicación de extremidades, sin píxeles visibles, sin apariencia estática o congelada, sin rostro mal definido, evitar estilo anime o caricaturesco.',
        'opcion 2' => 'Sin alas deformes ni poco detalladas, evitar imágenes borrosas, sin distorsión de manos, sin múltiples extremidades, sin dientes mal formados, sin textura pixelada, sin estilo de dibujos animados o anime, sin congelamiento de cuadros, sin saturación excesiva ni iluminación exagerada.',
        'opcion 3' => 'Sin calidad baja o granulada, evitar desenfoque, sin proporciones extrañas en el cuerpo, sin deformaciones de la cara, evitar superposición errática, sin elementos pixelados, sin movimiento errático ni difuso, sin ambiente opaco o estático, sin estilo anime, sin errores de morfología.',
        'opcion 4' => 'Sin borrosidad, evitar distorsión de rostros o vestidos, sin imágenes de baja resolución, sin manos o piernas deformadas, sin falta de enfoque, sin estilo de caricatura, sin parpadeos, evitar duplicación de modelos, sin errores de renderizado o desenfoque artificial.',
        'opcion 5' => 'Sin desenfoque de movimiento, sin deformidades en las modelos, evitar distorsión de la pasarela, sin estilo anime o cartoon, sin ruido visual, sin baja calidad, evitar imágenes congeladas, sin errores de luz ni sombra, sin expresiones irreales o proporciones inhumanas.',
        'opcion 6' => 'Artefactos, Deformado, Baja calidad, Múltiples apéndices, Granulado, Dientes deformados, Tres piernas, Manos deformadas Borroso, Distorsionado, Pixelado, Similar a anime, Caricaturesco, Estático, Plano, Desenfocado, Poco claro, Sobresaturado, Borroso, Neblinoso, Deformado, Fijo, Morphing, Propenso a errores, Lento, Baja resolución, Sin refinar, Indefinido, Congelado, Movimiento rápido Desenfoque, desfiguración, desenfoque, mala cara'

    ];

    public function getAllPromptVideo():array {
        return array_map(fn($key) => [
            'key' => $key,
            'name' => ucfirst($key),
            'prompt' => $this->promptVideo[$key]
        ], array_keys($this->promptVideo));
    }

    public function getAllNegativePrompts():array {
        return array_map(fn($key) => [
            'key' => $key,
            'name' => ucfirst($key),
            'prompt' => $this->negativePrompts[$key]
        ], array_keys($this->negativePrompts));
    }

    /**
     * Retorna todos los hints disponibles.
     *
     * @return array[] Lista con:
     *   - key: identificador interno
     *   - name: nombre capitalizado (para mostrar)
     *   - prompt: texto completo que se inserta en el textarea
     */
    public function getAllHints(): array
    {
        return array_map(fn($key) => [
            'key' => $key,
            'name' => ucfirst($key),
            'prompt' => $this->hints[$key]
        ], array_keys($this->hints));
    }

    /**
     * Retorna el prompt completo de un hint específico.
     *
     * @param string $hint
     * @return string
     */
    public function getPromptForHint(string $hint): string
    {
        return $this->hints[$hint] ?? '';
    }
}
