<?php

namespace App\Services\KlingAi;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Models\VirtualModel;

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

    public function saveImage(UploadedFile $file, string $directory, string $prefix = ''): string
    {
        $this->validateImage($file);

        $filename = $prefix . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $directory . '/' . $filename;

        Storage::disk('public')->putFileAs($directory, $file, $filename);

        return $path;
    }

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

    public function getSelectedDefaultModelBase64(string $selectedModel): string
    {
        $path = public_path('klingai/default_models/' . $selectedModel);

        if (file_exists($path)) {
            $image = Image::make($path);
            return base64_encode($image->encode('png'));
        }

        return $this->createPlaceholderImage();
    }

    // 🔥 NUEVO: Obtener modelo virtual como base64
    public function getVirtualModelBase64(string $virtualModelId): string
    {
        $model = VirtualModel::find($virtualModelId);

        if (!$model || $model->status !== 'completed') {
            throw new \Exception('Virtual model not found or not completed');
        }

        $imagePaths = $model->result_image_paths;
        if (empty($imagePaths)) {
            throw new \Exception('Virtual model has no generated images');
        }

        // Usar la primera imagen generada
        $imagePath = $imagePaths[0];
        $fullPath = storage_path('app/public/' . $imagePath);

        if (!file_exists($fullPath)) {
            throw new \Exception('Virtual model image file not found');
        }

        $image = Image::make($fullPath);
        return base64_encode($image->encode('png'));
    }

    // 🔥 NUEVO: Convertir cualquier imagen guardada a base64
    public function convertStoredImageToBase64(string $storagePath): string
    {
        $fullPath = storage_path('app/public/' . $storagePath);

        if (!file_exists($fullPath)) {
            throw new \Exception('Image file not found: ' . $storagePath);
        }

        $image = Image::make($fullPath);
        return base64_encode($image->encode('png'));
    }

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
}
