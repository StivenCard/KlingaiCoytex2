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
     * 🔥 CORREGIDO: Descarga imagen y aplica SOLO marca de agua con logo
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

            // 🎨 APLICAR SOLO MARCA DE AGUA CON LOGO
            $watermarkedImage = $this->addLogoWatermarkOnly($image);

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
     * 🔥 NUEVO: Aplica SOLO marca de agua con logo - esquina inferior derecha
     */
    private function addLogoWatermarkOnly($image): InterventionImage
    {
        // Ruta del logo
        $logoPath = public_path('images/logo_sio.png');

        if (file_exists($logoPath)) {
            try {
                // Cargar logo original
                $logo = Image::make($logoPath);

                // Calcular tamaño MÁS GRANDE (15% del ancho)
                $sizeFactor =  0.20; // 20% del ancho de la imagen
                $logoWidth = max(180, $image->width() * $sizeFactor); // Mínimo 180px

                // Redimensionar manteniendo proporciones
                $logo->resize($logoWidth, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                $logoHeight = $logo->height();
                $margin = 30;

                // Calcular posición para esquina inferior derecha
                $logoX = $image->width() - $logoWidth - $margin;
                $logoY = $image->height() - $logoHeight - $margin;

                // Texto "Generado Por" encima del logo
                $textY = $logoY - 10; // 10px encima del logo
                $textX = $logoX + ($logoWidth / 2); // Centrado con el logo

                // Crear fondo para el conjunto texto + logo
                $this->addWatermarkBackground($image, $logoX, $textY - 25, $logoWidth, $logoHeight + 35);

                // Aplicar texto "Generado Por"
                $image->text('Generado Por', $textX, $textY, function($font) {
                    $font->size(16);
                    $font->color('#333333');
                    $font->align('center');
                    $font->valign('bottom');
                });

                // Insertar logo debajo del texto
                $image->insert($logo, $logoX, $logoY);

            } catch (\Exception $e) {
                Log::warning('Error al aplicar logo de marca de agua: ' . $e->getMessage());

                // Si falla, crear marca de agua básica
                $image = $this->addFallbackWatermark($image);
            }
        } else {
            // Si no existe el archivo, crear marca de agua básica
            $image = $this->addFallbackWatermark($image);
        }

        return $image;
    }

    /**
     * 🔥 NUEVO: Agrega fondo semi-transparente para el conjunto texto + logo
     */
    private function addWatermarkBackground($image, $x, $y, $width, $height): void
    {
        $padding = 15;
        $bgColor = 'rgba(255, 255, 255, 0.9)';

        // Crear rectángulo con esquinas redondeadas (simulado)
        $image->rectangle(
            $x - $padding,
            $y - $padding,
            $x + $width + $padding,
            $y + $height + $padding,
            function ($draw) use ($bgColor) {
                $draw->background($bgColor);
            }
        );

        // Agregar borde sutil
        $image->rectangle(
            $x - $padding,
            $y - $padding,
            $x + $width + $padding,
            $y + $height + $padding,
            function ($draw) {
                $draw->border(1, 'rgba(0, 0, 0, 0.1)');
            }
        );
    }

    /**
     * 🔥 NUEVO: Crea marca de agua básica si no existe logo
     */
    private function addFallbackWatermark($image): InterventionImage
    {
        $logoWidth = max(150, $image->width() * 0.12);
        $logoHeight = 50;
        $margin = 30;

        // Posición esquina inferior derecha
        $logoX = $image->width() - $logoWidth - $margin;
        $logoY = $image->height() - $logoHeight - $margin;
        $textY = $logoY - 10;
        $textX = $logoX + ($logoWidth / 2);

        // Crear fondo
        $this->addWatermarkBackground($image, $logoX, $textY - 25, $logoWidth, $logoHeight + 35);

        // Texto "Generado Por"
        $image->text('Generado Por', $textX, $textY, function($font) {
            $font->size(16);
            $font->color('#333333');
            $font->align('center');
            $font->valign('bottom');
        });

        // Crear logo básico "SIO"
        $logo = Image::canvas($logoWidth, $logoHeight, '#3b82f6');

        // Agregar gradiente
        $logo->fill('#1e40af', 0, 0);

        // Texto "SIO"
        $logo->text('SIO', $logoWidth / 2, $logoHeight / 2, function($font) {
            $font->size(28);
            $font->color('#ffffff');
            $font->align('center');
            $font->valign('middle');
        });

        // Insertar logo
        $image->insert($logo, $logoX, $logoY);

        return $image;
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
        $watermarkedImage = $this->addLogoWatermarkOnly($image);

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
