<?php

namespace App\Services;

class GuidelinesService
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png'];
    private const BASE_PATH = 'klingai';

    private array $descriptiveTexts = [
        'valid_model' => [
            'Foto clara y sola',
            'Vista frontal de cuerpo completo/medio cuerpo',
            'Área de ropa sin obstrucciones',
            'Pose simple',
            'Ropa simple y ajustada',
            'Rostro sin obstrucciones'
        ],
        'invalid_model' => [
            'Foto de grupo',
            'Posición reclinada/sentada',
            'Área de ropa obstruida',
            'Pose compleja',
            'Ropa voluminosa',
            'Rostro obstruido'
        ],
        'valid_garment' => [
            'Fondo blanco, disposición plana',
            'Prenda individual',
            'Detalles de ropa simples y claros',
            'Resaltar el cuerpo principal',
            'Prenda clara sin obstrucciones'
        ],
        'invalid_garment' => [
            'Múltiples prendas',
            'Fondo complejo',
            'Patrones complejos, estampados',
            'Texto flotante/banners adicionales',
            'Ropa doblada u oscurecida'
        ]
    ];

    /**
     * Obtener guidelines para un tipo específico
     */
    public function getGuidelines(string $type): array
    {
        $path = public_path(self::BASE_PATH . '/' . $type);
        $defaultDescription = str_contains($type, 'valid') ? 'Ejemplo válido' : 'Ejemplo inválido';

        return $this->loadImagesFromDirectory($path, $type, $defaultDescription);
    }

    /**
     * Obtener todos los guidelines organizados
     */
    public function getAllGuidelines(): array
    {
        return [
            'validModels' => $this->getGuidelines('valid_model'),
            'invalidModels' => $this->getGuidelines('invalid_model'),
            'validGarments' => $this->getGuidelines('valid_garment'),
            'invalidGarments' => $this->getGuidelines('invalid_garment'),
        ];
    }

    /**
     * Obtener modelos por defecto
     */
    public function getDefaultModels(): array
    {
        $modelsPath = public_path(self::BASE_PATH . '/default_models');
        $defaultModels = [];

        if (!is_dir($modelsPath)) {
            return $defaultModels;
        }

        $files = $this->getImageFiles($modelsPath);

        foreach ($files as $file) {
            $defaultModels[] = [
                'filename' => $file,
                'url' => asset(self::BASE_PATH . '/default_models/' . $file),
                'name' => pathinfo($file, PATHINFO_FILENAME)
            ];
        }

        return $defaultModels;
    }

    /**
     * Cargar imágenes desde un directorio
     */
    private function loadImagesFromDirectory(string $path, string $type, string $defaultDescription): array
    {
        $items = [];

        if (!is_dir($path)) {
            return $items;
        }

        $files = $this->getImageFiles($path);
        $descriptions = $this->descriptiveTexts[$type] ?? [];

        foreach ($files as $index => $file) {
            $items[] = [
                'filename' => $file,
                'url' => asset(self::BASE_PATH . '/' . $type . '/' . $file),
                'name' => pathinfo($file, PATHINFO_FILENAME),
                'description' => $descriptions[$index] ?? $defaultDescription
            ];
        }

        return $items;
    }

    /**
     * Obtener archivos de imagen de un directorio
     */
    private function getImageFiles(string $path): array
    {
        if (!is_dir($path)) {
            return [];
        }

        $files = array_filter(scandir($path), function($file) {
            return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), self::IMAGE_EXTENSIONS);
        });

        natsort($files);
        return array_values($files);
    }

    /**
     * Agregar nuevos textos descriptivos
     */
    public function addDescriptiveTexts(string $type, array $texts): void
    {
        $this->descriptiveTexts[$type] = $texts;
    }

    /**
     * Verificar si existe un directorio de guidelines
     */
    public function guidelineDirectoryExists(string $type): bool
    {
        return is_dir(public_path(self::BASE_PATH . '/' . $type));
    }

    /**
     * Obtener estadísticas de guidelines
     */
    public function getGuidelinesStats(): array
    {
        $stats = [];

        foreach (array_keys($this->descriptiveTexts) as $type) {
            $path = public_path(self::BASE_PATH . '/' . $type);
            $stats[$type] = [
                'exists' => is_dir($path),
                'count' => count($this->getImageFiles($path)),
                'path' => $path
            ];
        }

        return $stats;
    }
}
