<?php

namespace App\Services;

class GuidelinesService
{
    private const EXTS = ['jpg', 'jpeg', 'png'];
    private const PATH = 'klingai';

    /**
     * Descripciones breves por imagen para cada tipo.
     */
    private array $texts = [
        'valid_model' => ['Foto clara y sola', 'Vista frontal completa', 'Área sin obstrucciones', 'Pose simple', 'Ropa ajustada', 'Rostro visible'],
        'invalid_model' => ['Foto de grupo', 'Posición sentada', 'Área obstruida', 'Pose compleja', 'Ropa voluminosa', 'Rostro obstruido'],
        'valid_garment' => ['Fondo blanco', 'Prenda individual', 'Detalles claros', 'Cuerpo principal', 'Sin obstrucciones'],
        'invalid_garment' => ['Múltiples prendas', 'Fondo complejo', 'Patrones complejos', 'Texto flotante', 'Ropa doblada']
    ];

    /**
     * Retorna un conjunto de ejemplos visuales para un tipo de guideline.
     *
     * @param string $type 'valid_model', 'invalid_model', 'valid_garment', 'invalid_garment'
     * @return array[] Lista de ejemplos con:
     *     - url: string URL accesible en el navegador
     *     - description: string texto explicativo
     */
    public function getGuidelines(string $type): array
    {
        $files = $this->getFiles(public_path(self::PATH . '/' . $type)); // Obtiene los archivos de imagen válidos en la carpeta correspondiente
        $descriptions = $this->texts[$type] ?? [];

        /* el formato que el frontend necesita para mostrar una galería de imágenes con su texto. */
        return array_map(fn($file, $i) => [
            'url' => asset(self::PATH . '/' . $type . '/' . $file),
            'description' => $descriptions[$i] ?? 'Ejemplo'
        ], $files, array_keys($files));
    }

    /**
     * Retorna todos los guidelines agrupados por tipo.
     *
     * @return array[]
     */
    public function getAllGuidelines(): array
    {
        return [
            'validModels'    => $this->getGuidelines('valid_model'),
            'invalidModels'  => $this->getGuidelines('invalid_model'),
            'validGarments'  => $this->getGuidelines('valid_garment'),
            'invalidGarments'=> $this->getGuidelines('invalid_garment'),
        ];
    }

    /**
     * Retorna las imágenes por defecto disponibles para selección.
     *
     * @return array[] Lista de modelos por defecto con:
     *     - url: string
     *     - filename: string
     *     - name: string (nombre sin extensión)
     */
    public function getDefaultModels(): array
    {
        $files = $this->getFiles(public_path(self::PATH . '/default_models'));

        return array_map(fn($file) => [
            'url' => asset(self::PATH . '/default_models/' . $file),
            'filename' => $file,
            'name' => pathinfo($file, PATHINFO_FILENAME)
        ], $files);
    }

    /**
     * Obtiene archivos de imagen válidos en una ruta.
     *
     * @param string $path Ruta absoluta en el sistema.
     * @return string[] Lista de nombres de archivo válidos (filtrados por extensión).
     */
    private function getFiles(string $path): array
    {
        if (!is_dir($path)) return [];

        //scandir obtiene todos los archivos y carpetas en la ruta, luego array_filter filtra solo los archivos con extensiones válidas (jpg, jpeg, png).
        $files = array_filter(scandir($path), fn($file) =>
            in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), self::EXTS)
        );

        //natsort ordena los archivos de manera natural (por ejemplo, "file2" antes de "file10").
        natsort($files);
        return array_values($files);
    }
}
