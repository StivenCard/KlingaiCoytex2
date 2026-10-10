<?php

namespace App\Services\KlingAi;

use App\Models\VirtualModel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Intervention\Image\Image as InterventionImage;
use Illuminate\Support\Facades\Log;

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
     * Descarga una imagen desde URL, aplica marca de agua y guarda en disco (PNG).
     * Si el procesamiento falla (marca de agua, memoria, formato), guarda la imagen original.
     */
    public function downloadAndSaveImage(string $url, string $directory, string $prefix = ''): string
    {
        $response = Http::timeout(60)->retry(2, 1000, throw: false)->get($url);

        if (!$response->successful()) {
            throw new \Exception("HTTP {$response->status()} al descargar la imagen");
        }

        try {
            $image = Image::make($response->body());
            $watermarked = $this->addHighQualityLogo($image);

            $path = $directory . '/' . $prefix . uniqid() . '.png';
            Storage::disk('public')->put($path, $watermarked->encode('png'));

            return $path;
        } catch (\Throwable $e) {
            Log::warning('Procesamiento de imagen falló, se guarda la original: ' . $e->getMessage());

            $mime = strtolower((string) $response->header('Content-Type'));
            $ext = str_contains($mime, 'jpeg') ? 'jpg' : (str_contains($mime, 'webp') ? 'webp' : 'png');

            $path = $directory . '/' . $prefix . uniqid() . '.' . $ext;
            Storage::disk('public')->put($path, $response->body());

            return $path;
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

        if (empty($imagePaths) || ! isset($imagePaths[$index])) {
            throw new \Exception("No image found at index $index for virtual model.");
        }

        $fullPath = storage_path('app/public/' . $imagePaths[$index]);

        return $this->imageToBase64($fullPath);
    }


    private const TRYON_MAX_SIDE = 2048;              // Try-On: lado largo máx. 2048px
    private const TRYON_MAX_BYTES = 9 * 1024 * 1024;  // Try-On: máx. 10MB (margen de 1MB)

    /**
     * Try-On: valida una imagen subida, la ajusta a las reglas de Kling y la guarda como JPG.
     * Retorna la ruta relativa en el disco public.
     */
    public function storeForTryOn(UploadedFile $file, string $directory, string $prefix = ''): string
    {
        $this->validateImage($file);

        return $this->saveTryOnImage($this->openOriented($file->getRealPath()), $directory, $prefix);
    }

    /**
     * Try-On: toma una imagen que ya existe en disco (modelo predeterminado o virtual),
     * y guarda una copia ajustada a las reglas de Kling.
     */
    public function storeFileForTryOn(string $fullPath, string $directory, string $prefix = ''): string
    {
        if (! is_file($fullPath)) {
            throw new \Exception("File not found: $fullPath");
        }

        return $this->saveTryOnImage($this->openOriented($fullPath), $directory, $prefix);
    }

    /**
     * Try-On: combina dos prendas en una sola imagen (la API solo acepta una) y la guarda ajustada.
     */
    public function storeCombinedForTryOn(UploadedFile $file1, UploadedFile $file2, string $directory, string $prefix = ''): string
    {
        return $this->saveTryOnImage($this->combineImages($file1, $file2), $directory, $prefix);
    }

    /**
     * Abre una imagen corrigiendo la orientación EXIF (fotos de celular), si PHP tiene exif.
     */
    private function openOriented(string $path): InterventionImage
    {
        $image = Image::make($path);

        if (function_exists('exif_read_data')) {
            try {
                $image->orientate();
            } catch (\Throwable $e) {
                // Sin EXIF utilizable: se deja la imagen como está
            }
        }

        return $image;
    }

    /**
     * Reduce a 2048px de lado largo, aplana sobre blanco y guarda como JPG (<= 9MB).
     */
    private function saveTryOnImage(InterventionImage $image, string $directory, string $prefix): string
    {
        $image->resize(self::TRYON_MAX_SIDE, self::TRYON_MAX_SIDE, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        // Kling exige lado corto estrictamente mayor a 300px
        if (min($image->width(), $image->height()) <= 300) {
            throw new \Exception('La imagen es demasiado alargada: al ajustarla a 2048px el lado corto queda en 300px o menos.');
        }

        $canvas = Image::canvas($image->width(), $image->height(), '#ffffff');
        $canvas->insert($image);

        $bytes = (string) $canvas->encode('jpg', 92);
        if (strlen($bytes) > self::TRYON_MAX_BYTES) {
            $bytes = (string) $canvas->encode('jpg', 75);
        }

        $path = $directory . '/' . $prefix . uniqid() . '.jpg';
        Storage::disk('public')->put($path, $bytes);

        return $path;
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
        if (! in_array($file->getMimeType(), $allowedMime)) {
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
        if (! file_exists($fullPath)) {
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

            $sizeFactor = 0.2;
            $logoWidth = max(150, $image->width() * $sizeFactor);

            $logo->resize($logoWidth, null, function ($constraint) {
                $constraint->aspectRatio(); // Mantiene las proporciones de la imagen
                $constraint->upsize(); // Evita que el logo se agrande más que su tamaño original
            });

            $margin = 25;

            // Posición del logo para ubicar el texto "Generado por"
            $logoX = $image->width() - $logo->width() - $margin;
            $logoY = $image->height() - $logo->height() - $margin;

            $image->insert($logo, 'bottom-right', $margin, $margin);

            $fontSize = min($logo->width() * 0.08, 32);
            $text = 'Generado por';
            $textX = $logoX + ($logo->width() / 2);
            $textY = $logoY - 1;

            $image->text($text, $textX, $textY, function ($font) use ($fontSize) {
                $font->file(public_path('fonts/Verdana.ttf'));
                $font->size($fontSize);
                $font->color('#000000');
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

    private const WAIT_INTERVAL_US = 100000; // 100 ms
    private const MAX_WAIT_US = 2000000;     // 2 s

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

            // Esperar hasta que el archivo esté realmente disponible (máx. 2s)
            $waitTime = 0;
            while (!Storage::disk('public')->exists($fullPath) && $waitTime < self::MAX_WAIT_US) {
                usleep(self::WAIT_INTERVAL_US); // 100ms
                $waitTime += self::WAIT_INTERVAL_US;
            }

            return $fullPath;
        } catch (\Exception $e) {
            throw new \Exception('Error al descargar video: ' . $e->getMessage());
        }
    }
}
