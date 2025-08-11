<?php

namespace App\Services\KlingAi;

use App\Models\VirtualModel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Intervention\Image\Image as InterventionImage;
use Illuminate\Support\Facades\Log;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

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
     * Combina dos imágenes horizontalmente y las convierte a base64 PNG.
     */
    public function combineImagesToBase64(UploadedFile $file1, UploadedFile $file2): string
    {
        return base64_encode($this->combineImages($file1, $file2)->encode('png'));
    }

    /**
     * Guarda una imagen subida en disco y retorna la ruta relativa.
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
     * Combina dos imágenes, las guarda en disco y retorna la ruta relativa.
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
     * Descarga una imagen desde URL, aplica marca de agua y guarda en disco.
     */
    public function downloadAndSaveImage(string $url, string $directory, string $prefix = ''): string
    {
        try {
            $response = Http::timeout(30)->get($url);

            if (!$response->successful()) {
                throw new \Exception('No se pudo descargar la imagen desde: ' . $url);
            }

            $image = Image::make($response->body());
            $watermarked = $this->addHighQualityLogo($image);

            $filename = $prefix . uniqid() . '.png';
            $path = $directory . '/' . $filename;

            Storage::disk('public')->put($path, $watermarked->encode('png'));

            return $path;
        } catch (\Exception $e) {
            throw new \Exception('Error al descargar imagen: ' . $e->getMessage());
        }
    }

    /**
     * Retorna una imagen default convertida a base64 PNG.
     */
    public function getSelectedDefaultModelBase64(string $selectedModel): string
    {
        $path = public_path('klingai/default_models/' . $selectedModel);
        return $this->imageToBase64($path);
    }

    /**
     * Retorna una imagen generada por la API convertida a base64 PNG.
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
        return $this->imageToBase64($fullPath);
    }

    /**
     * Valida una imagen cargada por el usuario según restricciones KlingAI.
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
        $width = $image->width();
        $height = $image->height();

        $short = min($width, $height);
        $long = max($width, $height);

        if ($short < 300 || $long > 4096) {
            throw new \Exception('Resolución no permitida. El lado corto debe ser al menos 300px y el lado largo como máximo 4096px.');
        }
    }

    /**
     * Combina dos imágenes horizontalmente sobre un fondo blanco.
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

    /**
     * Convierte una imagen en disco a base64 PNG.
     */
    private function imageToBase64(string $fullPath): string
    {
        if (!file_exists($fullPath)) {
            throw new \Exception("File not found: $fullPath");
        }

        return base64_encode(Image::make($fullPath)->encode('png'));
    }

    /**
     * Aplica logo de marca de agua con configuración fija.
     */
    private function addHighQualityLogo(InterventionImage $image): InterventionImage
    {
        $logoPath = public_path('images/logo_sio.png');

        try {
            $logo = Image::make($logoPath);

            $imgW = $image->width();
            $imgH = $image->height();

            // 20% del ancho, pero nunca menos de 60px y nunca más del 25% del alto
            $logoWidth = max(60, min($imgW * 0.20, $imgH * 0.25));
            $logo->resize($logoWidth, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            // Márgenes: proporcionales, pero con mínimo
            $margin = max(15, round($imgW * 0.025));

            // Posición logo
            $logoX = $imgW - $logo->width() - $margin;
            $logoY = $imgH - $logo->height() - $margin;

            $image->insert($logo, 'bottom-right', $margin, $margin);

            // Texto adaptativo: entre 14 y 38px, centrado arriba del logo
            $fontSize = max(14, min($logo->width() * 0.12, 38));
            $text = 'Generado por';
            $textX = $logoX + ($logo->width() / 2);
            $textY = $logoY - max(8, $fontSize / 3);

            $image->text($text, $textX, $textY, function ($font) use ($fontSize) {
                $font->file(public_path('fonts/Verdana.ttf'));
                $font->size($fontSize);
                $font->color([0, 0, 0, 0.68]); // negro semi-transparente
                $font->align('center');
                $font->valign('bottom');
            });

        } catch (\Exception $e) {
            Log::warning('Error al aplicar logo de marca de agua: ' . $e->getMessage());
        }

        return $image;
    }

    /**
     * Descarga y guarda un video desde una URL.
     *
     * @param string $url URL del video.
     * @param string $path Ruta base donde guardar.
     * @param string $prefix Prefijo para el nombre del archivo.
     * @return string Ruta donde se guardó el video.
     */
    public function downloadAndSaveVideo(string $url, string $path, string $prefix = ''): string
    {
        try {
            $response = Http::timeout(60)->get($url);

            if (!$response->successful()) {
                throw new \Exception('No se pudo descargar el video desde: ' . $url);
            }

            $filename = $prefix . uniqid() . '.mp4';
            $fullPath = $path . '/' . $filename;

            // Usar el disco público para almacenar el video
            Storage::disk('public')->put($fullPath, $response->body());

            return $fullPath;
        } catch (\Exception $e) {
            throw new \Exception('Error al descargar video: ' . $e->getMessage());
        }
    }
}




