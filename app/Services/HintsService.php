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
        'opcion 1' => 'Modelo da una vuelta elegante mostrando su atuendo completo. La cámara sigue suavemente su movimiento. El vestido se mueve con naturalidad. Fondo neutro con luz de pasarela profesional.',
        'opcion 2' => 'Modelo camina con majestuosas alas estilizadas como ángel de alta costura. Iluminación brillante resalta alas y vestido. Estilo etéreo y fantasioso.',
        'opcion 3' => 'Escena vibrante y surreal, con colores intensos y estética teatral. Inspiración en David LaChapelle. Fondos llamativos, poses audaces y dramáticas.',
        'opcion 4' => 'Modelo en la Semana de la Moda de París, rodeada de alta costura. Iluminación suave resalta detalles finos. Ambiente mágico y sofisticado.',
        'opcion 5' => 'Desfile sobre pasarela moderna. Modelos caminan seguras mostrando ropa elegante. Público observa, luces y flashes crean un entorno profesional.',
        'opcion 6' => 'Modelo gira lentamente en un set minimalista con fondo blanco, mostrando su ropa desde todos los ángulos. Luz suave y limpia resalta texturas y siluetas.',
        'opcion 7' => 'Una modelo desfila al aire libre sobre una plataforma rodeada de vegetación tropical, con iluminación natural dorada y una brisa suave moviendo la ropa.'
    ];

    private array $negativePrompts = [
        'opcion 1' => 'artefactos, deformaciones, desenfoque por movimiento, doble exposición, calidad baja, duplicación de extremidades, píxeles visibles, imagen congelada, rostro borroso, estilo anime, caricaturesco',

        'opcion 2' => 'alas deformadas, imagen borrosa, manos distorsionadas, extremidades múltiples, dientes malformados, textura pixelada, estilo animado, cuadros congelados, sobresaturación, iluminación exagerada',

        'opcion 3' => 'baja calidad, imagen granulada, desenfoque, proporciones extrañas, cara deformada, superposición, elementos pixelados, movimiento errático, fondo estático, estilo anime, errores morfológicos',

        'opcion 4' => 'imagen borrosa, distorsión facial, baja resolución, extremidades deformadas, falta de enfoque, estilo de caricatura, parpadeo, duplicación de modelos, errores de renderizado, desenfoque artificial',

        'opcion 5' => 'desenfoque por movimiento, deformidades físicas, distorsión del entorno, estilo anime, estilo cartoon, ruido visual, calidad baja, imagen congelada, errores de luz, proporciones irreales',

        'opcion 6' => 'artefactos, deformado, baja calidad, múltiples apéndices, imagen granulada, dientes malformados, extremidades adicionales, manos deformadas, borroso, distorsionado, pixelado, estilo anime, caricaturesco, imagen estática, plano, desenfocado, sin detalle, sobresaturado, congelado, desenfoque rápido, sin definición, sin refinar'
    ];

    private function formatPrompts(array $source): array {
        return array_map(fn($key) => [
            'key' => $key,
            'name' => ucfirst($key),
            'prompt' => $source[$key]
        ], array_keys($source));
    }

    public function getAllHints(): array {
        return $this->formatPrompts($this->hints);
    }

    public function getAllPromptVideo(): array {
        return $this->formatPrompts($this->promptVideo);
    }

    public function getAllNegativePrompts(): array {
        return $this->formatPrompts($this->negativePrompts);
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
