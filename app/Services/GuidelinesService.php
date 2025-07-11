<?php

namespace App\Services;

class GuidelinesService
{
    private const EXTS = ['jpg', 'jpeg', 'png'];
    private const PATH = 'klingai';

    private array $texts = [
        'valid_model' => ['Foto clara y sola', 'Vista frontal completa', 'Área sin obstrucciones', 'Pose simple', 'Ropa ajustada', 'Rostro visible'],
        'invalid_model' => ['Foto de grupo', 'Posición sentada', 'Área obstruida', 'Pose compleja', 'Ropa voluminosa', 'Rostro obstruido'],
        'valid_garment' => ['Fondo blanco', 'Prenda individual', 'Detalles claros', 'Cuerpo principal', 'Sin obstrucciones'],
        'invalid_garment' => ['Múltiples prendas', 'Fondo complejo', 'Patrones complejos', 'Texto flotante', 'Ropa doblada']
    ];

    public function getGuidelines(string $type): array
    {
        $files = $this->getFiles(public_path(self::PATH . '/' . $type));
        $texts = $this->texts[$type] ?? [];

        return array_map(fn($file, $i) => [
            'filename' => $file,
            'url' => asset(self::PATH . '/' . $type . '/' . $file),
            'name' => pathinfo($file, PATHINFO_FILENAME),
            'description' => $texts[$i] ?? 'Ejemplo'
        ], $files, array_keys($files));
    }

    public function getAllGuidelines(): array
    {
        return [
            'validModels' => $this->getGuidelines('valid_model'),
            'invalidModels' => $this->getGuidelines('invalid_model'),
            'validGarments' => $this->getGuidelines('valid_garment'),
            'invalidGarments' => $this->getGuidelines('invalid_garment'),
        ];
    }

    public function getDefaultModels(): array
    {
        $files = $this->getFiles(public_path(self::PATH . '/default_models'));

        return array_map(fn($file) => [
            'filename' => $file,
            'url' => asset(self::PATH . '/default_models/' . $file),
            'name' => pathinfo($file, PATHINFO_FILENAME)
        ], $files);
    }

    private function getFiles(string $path): array
    {
        if (!is_dir($path)) return [];

        $files = array_filter(scandir($path), fn($file) =>
            in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), self::EXTS)
        );

        natsort($files);
        return array_values($files);
    }
}
