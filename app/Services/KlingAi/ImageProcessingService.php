<?php

namespace App\Services\KlingAi;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImageProcessingService
{
    public function convertToBase64(UploadedFile $file): string
    {
        $this->validateImage($file);

        $image = Image::make($file->getRealPath());
        return base64_encode($image->encode('png'));
    }

    public function combineImagesToBase64(UploadedFile $file1, UploadedFile $file2): string
    {
        $this->validateImage($file1);
        $this->validateImage($file2);

        $img1 = Image::make($file1->getRealPath());
        $img2 = Image::make($file2->getRealPath());

        $targetHeight = max($img1->height(), $img2->height());

        $img1->resize(null, $targetHeight, function ($constraint) {
            $constraint->aspectRatio();
        });

        $img2->resize(null, $targetHeight, function ($constraint) {
            $constraint->aspectRatio();
        });

        $canvasWidth = $img1->width() + $img2->width();
        $canvas = Image::canvas($canvasWidth, $targetHeight, '#ffffff');

        $canvas->insert($img1, 'left');
        $canvas->insert($img2, 'top-right');

        return base64_encode($canvas->encode('png'));
    }

    // 🔥 NUEVO: Guardar imagen individual
    public function saveImage(UploadedFile $file, string $directory, string $prefix = ''): string
    {
        $this->validateImage($file);

        $filename = $prefix . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $directory . '/' . $filename;

        Storage::disk('public')->putFileAs($directory, $file, $filename);

        return $path;
    }

    // 🔥 NUEVO: Guardar imagen combinada
    public function saveCombinedImage(UploadedFile $file1, UploadedFile $file2, string $directory): string
    {
        $this->validateImage($file1);
        $this->validateImage($file2);

        $img1 = Image::make($file1->getRealPath());
        $img2 = Image::make($file2->getRealPath());

        $targetHeight = max($img1->height(), $img2->height());

        $img1->resize(null, $targetHeight, function ($constraint) {
            $constraint->aspectRatio();
        });

        $img2->resize(null, $targetHeight, function ($constraint) {
            $constraint->aspectRatio();
        });

        $canvasWidth = $img1->width() + $img2->width();
        $canvas = Image::canvas($canvasWidth, $targetHeight, '#ffffff');

        $canvas->insert($img1, 'left');
        $canvas->insert($img2, 'top-right');

        $filename = 'combined_' . uniqid() . '.png';
        $path = $directory . '/' . $filename;

        Storage::disk('public')->put($path, $canvas->encode('png'));

        return $path;
    }

    // 🔥 NUEVO: Descargar imagen de URL y guardarla
    public function downloadAndSaveImage(string $url, string $directory, string $prefix = ''): string
    {
        try {
            $response = Http::timeout(30)->get($url);

            if (!$response->successful()) {
                throw new \Exception('No se pudo descargar la imagen desde: ' . $url);
            }

            $imageData = $response->body();
            $filename = $prefix . uniqid() . '.png';
            $path = $directory . '/' . $filename;

            Storage::disk('public')->put($path, $imageData);

            return $path;
        } catch (\Exception $e) {
            throw new \Exception('Error al descargar imagen: ' . $e->getMessage());
        }
    }

    // 🔥 NUEVO: Obtener imagen default como base64
    public function getDefaultModelBase64(): string
    {
        $defaultModels = [
            'model1.jpg',
            'model2.jpg',
            'model3.jpg'
        ];

        foreach ($defaultModels as $model) {
            $path = public_path('klingai/default_models/' . $model);
            if (file_exists($path)) {
                $image = Image::make($path);
                return base64_encode($image->encode('png'));
            }
        }

        // Si no existe ningún modelo default, crear placeholder
        return $this->createPlaceholderImage();
    }

    // 🔥 NUEVO: Crear imagen placeholder
    private function createPlaceholderImage(): string
    {
        $image = Image::canvas(512, 768, '#f8f9fa');
        $image->text('DEFAULT MODEL', 256, 384, function($font) {
            $font->size(24);
            $font->color('#6c757d');
            $font->align('center');
            $font->valign('middle');
        });

        return base64_encode($image->encode('png'));
    }

    // 🔥 MEJORADO: Validación de imagen
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
        $width = $image->width();
        $height = $image->height();
        $min = min($width, $height);
        $max = max($width, $height);

        if ($min < 300 || $max > 4096) {
            throw new \Exception('Resolución no permitida. Mínimo 300px, máximo 4096px.');
        }
    }

    // 🔥 NUEVO: Obtener modelo default seleccionado
    public function getSelectedDefaultModelBase64(string $selectedModel): string
    {
        $path = public_path('klingai/default_models/' . $selectedModel);

        if (file_exists($path)) {
            $image = Image::make($path);
            return base64_encode($image->encode('png'));
        }

        // Si no existe el modelo seleccionado, crear placeholder
        return $this->createPlaceholderImage();
    }

}
