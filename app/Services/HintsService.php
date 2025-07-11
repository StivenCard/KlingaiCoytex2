<?php

namespace App\Services;

class HintsService
{
    private array $hints = [
        'elegante' => 'De pie elegantemente, con los pies naturalmente juntos, la mano derecha sosteniendo un bolso negro, la mano izquierda colgando naturalmente, la barbilla ligeramente levantada, cabello corto ligeramente rizado, delicada chaqueta corta de lana rosa con decoración de hebilla de metal, ropa interior blanca, falda negra, medias negras, mocasines de charol negro, fondo de tierra verde claro, simple toma de estudio',

        'urbano' => 'Pose relajada de pie, piernas cruzadas naturalmente, una mano tocando suavemente el cabello, cabello castaño rojizo medio corto, sudadera blanca suelta, pantalones cortos negros, escena de acera, fondo de árbol verde, cerca de hierro, luz natural, estilo casual cotidiano.',

        'energetico' => 'Pose atlética dinámica, una pierna ligeramente hacia adelante, brazos posicionados naturalmente mostrando energía, ropa deportiva brillante, atuendo moderno de fitness, gimnasio o entorno deportivo al aire libre, colores vibrantes, expresión confiada.',

        'dulce' => 'Pose suave con una sonrisa delicada, cabello fluido, vestido de color pastel, joyería delicada, iluminación natural suave, fondo floral o de jardín, apariencia femenina y graciosa.',

        'intelectual' => 'Pose profesional y segura, atuendo de negocios, aspecto moderno y limpio, sosteniendo libros o documentos, entorno de oficina o biblioteca, estilo sofisticado, fondo neutro, apariencia inteligente y casual.'
    ];

    public function getAllHints(): array
    {
        return array_map(fn($key) => [
            'key' => $key,
            'name' => ucfirst($key),
            'prompt' => $this->hints[$key]
        ], array_keys($this->hints));
    }

    public function getPromptForHint(string $hint): string
    {
        return $this->hints[$hint] ?? '';
    }
}
