<?php

namespace App\Services\KlingAi;

use App\Models\VirtualModel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Image as InterventionImage;

class ImageProcessingService
{
    /**
     * Convierte una imagen subida a base64 codificado en PNG.
     */
    public function convertToBase64(UploadedFile $file): string
    {
        $this->validateImage($file);
        return base64_encode(Image::make($file->getRealPath())->encode('png'));
    }

    /**
     * Combina dos imágenes horizontalmente en una sola imagen y la convierte a base64 en formato PNG.
     */
    public function combineImagesToBase64(UploadedFile $file1, UploadedFile $file2): string
    {
        return base64_encode($this->combineImages($file1, $file2)->encode('png'));
    }

    /**
     * Guarda una imagen subida en el disco público y retorna la ruta relativa.
     */
    public function saveImage(UploadedFile $file, string $directory, string $prefix = ''): string
    {
        $this->validateImage($file);

        $filename = $prefix . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $directory . '/' . $filename;

        Storage::disk('public')->putFileAs($directory, $file, $filename);

        return $path;
    }

    /**
     * Combina dos imágenes en una sola sobre un lienzo blanco, la guarda en el disco público y retorna la ruta relativa.
     */
    public function saveCombinedImage(UploadedFile $file1, UploadedFile $file2, string $directory): string
    {
        $canvas = $this->combineImages($file1, $file2);

        $filename = 'combined_' . uniqid() . '.png';
        $path = $directory . '/' . $filename;

        Storage::disk('public')->put($path, $canvas->encode('png'));

        return $path;
    }

    /**
     * 🔥 CORREGIDO: Descarga imagen y aplica marca de agua MEJORADA
     */
    public function downloadAndSaveImage(string $url, string $directory, string $prefix = ''): string
    {
        try {
            $response = Http::timeout(30)->get($url);

            if (!$response->successful()) {
                throw new \Exception('No se pudo descargar la imagen desde: ' . $url);
            }

            // Crear imagen desde el contenido descargado
            $image = Image::make($response->body());

            // 🎨 APLICAR MARCA DE AGUA MEJORADA CON CONFIGURACIÓN
            $watermarkedImage = $this->addWatermarkWithConfig($image);

            $filename = $prefix . uniqid() . '.png';
            $path = $directory . '/' . $filename;

            Storage::disk('public')->put($path, $watermarkedImage->encode('png'));

            return $path;
        } catch (\Exception $e) {
            throw new \Exception('Error al descargar imagen: ' . $e->getMessage());
        }
    }

    /**
     * Retorna una imagen default seleccionada en formato base64 PNG.
     */
    public function getSelectedDefaultModelBase64(string $selectedModel): string
    {
        $path = public_path('klingai/default_models/' . $selectedModel);

        if (!file_exists($path)) {
            throw new \Exception('Default model not found: ' . $selectedModel);
        }

        return base64_encode(Image::make($path)->encode('png'));
    }

    /**
     * Obtiene una imagen específica de un modelo virtual en formato base64.
     */
    public function getVirtualModelBase64(string $virtualModelId, int $index = 0): string
    {
        $model = VirtualModel::find($virtualModelId);

        if (!$model || $model->status !== 'completed') {
            throw new \Exception('Virtual model not found or not completed');
        }

        $imagePaths = $model->result_image_paths;

        if (empty($imagePaths) || !isset($imagePaths[$index])) {
            throw new \Exception("No image found at index $index for virtual model.");
        }

        $fullPath = storage_path('app/public/' . $imagePaths[$index]);

        if (!file_exists($fullPath)) {
            throw new \Exception('Virtual model image file not found');
        }

        return base64_encode(Image::make($fullPath)->encode('png'));
    }

    /**
     * Convierte cualquier imagen almacenada en base64.
     */
    public function convertStoredImageToBase64(string $storagePath): string
    {
        $fullPath = storage_path('app/public/' . $storagePath);

        if (!file_exists($fullPath)) {
            throw new \Exception('Image file not found: ' . $storagePath);
        }

        return base64_encode(Image::make($fullPath)->encode('png'));
    }

    /**
     * 🔥 NUEVO: Aplica marca de agua MEJORADA con configuración desde config/watermark.php
     */
    private function addWatermarkWithConfig($image): InterventionImage
    {
        // Aplicar logo primero
        $image = $this->addHighQualityLogo($image);

        // Aplicar texto mejorado
        $image = $this->addHighQualityText($image);

        return $image;
    }

    /**
     * 🔥 NUEVO: Aplica logo SIO de ALTA CALIDAD
     */
    private function addHighQualityLogo($image): InterventionImage
    {

        $logoPath = public_path('images/logo_sio.png');

        if (file_exists($logoPath)) {
            try {
                // Cargar logo original
                $logo = Image::make($logoPath);

                // Calcular tamaño MEJORADO (más grande)
                $sizeFactor = 0.12; // 12% en vez de 8%
                $logoWidth = max(150, $image->width() * $sizeFactor); // Mínimo 150px

                // Redimensionar manteniendo calidad
                $logo->resize($logoWidth, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                $logoHeight = $logo->height();
                $margin = 25;

                // Posición del logo
                $logoX = $margin;
                $logoY = $image->height() - $logoHeight - $margin;

                // Crear fondo MEJORADO para el logo
                if (true) {
                    $bgColor = 'rgba(255, 255, 255, 0.9)';
                    $padding = 10;

                    $image->rectangle(
                        $logoX - $padding,
                        $logoY - $padding,
                        $logoX + $logoWidth + $padding,
                        $logoY + $logoHeight + $padding,
                        function ($draw) use ($bgColor) {
                            $draw->background($bgColor);
                        }
                    );
                }

                // Insertar logo
                $image->insert($logo, 'bottom-left', $margin, $margin);

            } catch (\Exception $e) {
                Log::warning('Error al aplicar logo de marca de agua: ' . $e->getMessage());

                // Si falla el logo, crear uno básico
                $image = $this->addFallbackLogo($image);
            }
        } else {
            // Si no existe el archivo, crear logo básico
            $image = $this->addFallbackLogo($image);
        }

        return $image;
    }

    /**
     * 🔥 NUEVO: Crea logo básico programáticamente si no existe archivo
     */
    private function addFallbackLogo($image): InterventionImage
    {
        $logoWidth = max(120, $image->width() * 0.1);
        $logoHeight = 40;
        $margin = 25;

        // Crear logo básico con gradiente
        $logo = Image::canvas($logoWidth, $logoHeight, '#3b82f6');

        // Agregar gradiente simple
        $logo->fill('#1e40af', 0, 0);

        // Texto "SIO" centrado
        $logo->text('SIO', $logoWidth / 2, $logoHeight / 2, function($font) {
            $font->size(24);
            $font->color('#ffffff');
            $font->align('center');
            $font->valign('middle');
        });

        // Fondo para el logo
        $image->rectangle(
            $margin - 10,
            $image->height() - $logoHeight - $margin - 10,
            $margin + $logoWidth + 10,
            $image->height() - $margin + 10,
            function ($draw) {
                $draw->background('rgba(255, 255, 255, 0.9)');
            }
        );

        // Insertar logo
        $image->insert($logo, 'bottom-left', $margin, $margin);

        return $image;
    }

    /**
     * 🔥 NUEVO: Aplica texto de ALTA CALIDAD y LEGIBLE
     */
    private function addHighQualityText($image): InterventionImage
    {
        $text =  'Generado por SIO';
        $fontSize = $this->calculateImprovedFontSize($image);
        $margin = 25;
        $color =  '#ffffff';
        $shadowColor = '#000000';

        // Posición del texto
        $textX = $image->width() - $margin;
        $textY = $image->height() - $margin;

        // Crear fondo MEJORADO para el texto
        if (true) {
            $this->addTextBackground($image, $text, $textX, $textY, $fontSize);
        }

        // Aplicar sombra FUERTE para contraste
        $image->text($text, $textX + 2, $textY + 2, function($font) use ($fontSize, $shadowColor) {
            $font->size($fontSize);
            $font->color($shadowColor);
            $font->align('right');
            $font->valign('bottom');
        });

        // Aplicar texto principal
        $image->text($text, $textX, $textY, function($font) use ($fontSize, $color) {
            $font->size($fontSize);
            $font->color($color);
            $font->align('right');
            $font->valign('bottom');
        });

        return $image;
    }

    /**
     * 🔥 NUEVO: Calcula tamaño de fuente MEJORADO (más grande y legible)
     */
    private function calculateImprovedFontSize($image): int
    {
        $width = $image->width();
        $height = $image->height();

        // Usar configuración más agresiva para mayor legibilidad
        $factor = 0.025; // 2.5% en vez de 2%
        $minSize = 16; // Mínimo 16px
        $maxSize = 36; // Máximo 36px

        $calculatedSize = $width * $factor;

        // Usar área para cálculo más preciso
        $area = $width * $height;
        if ($area > 2000000) {
            $calculatedSize = 32;
        } elseif ($area > 1000000) {
            $calculatedSize = 28;
        } elseif ($area > 500000) {
            $calculatedSize = 24;
        } else {
            $calculatedSize = 20;
        }

        return max($minSize, min($maxSize, (int)$calculatedSize));
    }

    /**
     * 🔥 NUEVO: Agrega fondo MEJORADO al texto
     */
    private function addTextBackground($image, $text, $x, $y, $fontSize): void
    {
        $bgColor = 'rgba(0, 0, 0, 0.7)';

        // Calcular dimensiones más precisas
        $textWidth = strlen($text) * ($fontSize * 0.65);
        $textHeight = $fontSize + 12;
        $padding = 8;

        // Coordenadas del rectángulo
        $bgX1 = $x - $textWidth - $padding;
        $bgY1 = $y - $textHeight - $padding;
        $bgX2 = $x + $padding;
        $bgY2 = $y + $padding;

        // Crear fondo con esquinas redondeadas (simulado)
        $image->rectangle($bgX1, $bgY1, $bgX2, $bgY2, function ($draw) use ($bgColor) {
            $draw->background($bgColor);
        });
    }

    /**
     * 🔥 VERSIÓN ANTERIOR: Mantener compatibilidad
     */
    private function addWatermark($image): InterventionImage
    {
        return $this->addWatermarkWithConfig($image);
    }

    /**
     * 🔥 VERSIÓN ANTERIOR: Mantener compatibilidad
     */
    private function addLogoWatermark($image): InterventionImage
    {
        return $this->addHighQualityLogo($image);
    }

    /**
     * 🔥 VERSIÓN ANTERIOR: Mantener compatibilidad
     */
    private function calculateFontSize($image): int
    {
        return $this->calculateImprovedFontSize($image);
    }

    /**
     * Aplica marca de agua a imagen existente en storage.
     */
    public function addWatermarkToStoredImage(string $storagePath): string
    {
        $fullPath = storage_path('app/public/' . $storagePath);

        if (!file_exists($fullPath)) {
            throw new \Exception('Image file not found: ' . $storagePath);
        }

        $image = Image::make($fullPath);
        $watermarkedImage = $this->addWatermarkWithConfig($image);

        Storage::disk('public')->put($storagePath, $watermarkedImage->encode('png'));

        return $storagePath;
    }

    /**
     * Valida una imagen subida por el usuario.
     */
    private function validateImage(UploadedFile $file): void
    {
        if ($file->getSize() > 10 * 1024 * 1024) {
            throw new \Exception('La imagen no debe superar los 10MB');
        }

        $allowedMime = ['image/jpeg', 'image/png'];
        if (!in_array($file->getMimeType(), $allowedMime)) {
            throw new \Exception('Formato no permitido. Solo JPG y PNG.');
        }

        $image = Image::make($file->getRealPath());
        $min = min($image->width(), $image->height());
        $max = max($image->width(), $image->height());

        if ($min < 300 || $max > 4096) {
            throw new \Exception('Resolución no permitida. Mínimo 300px, máximo 4096px.');
        }
    }

    /**
     * Combina dos imágenes horizontalmente en un solo canvas blanco.
     */
    private function combineImages(UploadedFile $file1, UploadedFile $file2): InterventionImage
    {
        $this->validateImage($file1);
        $this->validateImage($file2);

        $img1 = Image::make($file1->getRealPath());
        $img2 = Image::make($file2->getRealPath());

        $targetHeight = max($img1->height(), $img2->height());

        $img1->resize(null, $targetHeight, fn($c) => $c->aspectRatio());
        $img2->resize(null, $targetHeight, fn($c) => $c->aspectRatio());

        $canvas = Image::canvas($img1->width() + $img2->width(), $targetHeight, '#ffffff');

        $canvas->insert($img1, 'left');
        $canvas->insert($img2, 'top-right');

        return $canvas;
    }
}
