<?php

namespace App\Services\KlingAi;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Models\VirtualModel;

class ImageProcessingService
{
    /**
     * Convierte una imagen subida a base64 codificado en PNG.
     *
     * @param UploadedFile $file Imagen subida por el usuario.
     * @return string Base64 codificado
     */
    public function convertToBase64(UploadedFile $file): string
    {
        $this->validateImage($file);
        return base64_encode(Image::make($file->getRealPath())->encode('png'));
    }

    /**
     * Combina dos imágenes horizontalmente en una sola imagen y la convierte a base64 en formato PNG.
     *
     * @param UploadedFile $file1 Primera imagen subida.
     * @param UploadedFile $file2 Segunda imagen subida.
     * @return string Cadena base64 resultante de la imagen combinada.
     *
     * @throws Exception Si alguna de las imágenes no es válida.
     */
    public function combineImagesToBase64(UploadedFile $file1, UploadedFile $file2): string
    {
        return base64_encode($this->combineImages($file1, $file2)->encode('png'));
    }

    /**
     * Guarda una imagen subida en el disco público y retorna la ruta relativa.
     *
     * @param UploadedFile $file Imagen subida por el usuario.
     * @param string $directory Carpeta destino dentro de storage/app/public.
     * @param string $prefix Prefijo opcional para el nombre del archivo.
     * @return string Ruta relativa donde se guardó la imagen.
     *
     * @throws Exception Si la imagen no es válida.
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
     *
     * @param UploadedFile $file1 Primera imagen.
     * @param UploadedFile $file2 Segunda imagen.
     * @param string $directory Carpeta de destino dentro de storage/app/public.
     * @return string Ruta relativa donde se guardó la imagen combinada.
     *
     * @throws Exception Si hay problemas con las imágenes o el guardado.
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
     * Descarga una imagen desde una URL externa y la guarda localmente en el disco público.
     *
     * @param string $url URL desde donde se descargará la imagen.
     * @param string $directory Carpeta de destino dentro de storage/app/public.
     * @param string $prefix Prefijo opcional para el nombre del archivo.
     * @return string Ruta relativa donde se guardó la imagen.
     *
     * @throws Exception Si ocurre un error al descargar o guardar la imagen.
     */
    public function downloadAndSaveImage(string $url, string $directory, string $prefix = ''): string
    {
        try {
            $response = Http::timeout(30)->get($url);

            if (!$response->successful()) {
                throw new \Exception('No se pudo descargar la imagen desde: ' . $url);
            }

            $filename = $prefix . uniqid() . '.png';
            $path = $directory . '/' . $filename;

            Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (\Exception $e) {
            throw new \Exception('Error al descargar imagen: ' . $e->getMessage());
        }
    }

    /**
    * Retorna una imagen default seleccionada en formato base64 PNG.
    *
    * @param string $selectedModel Nombre del archivo del modelo (ej. "model1.png")
    * @return string Imagen codificada en base64.
    *
    * @throws \Exception Si el archivo no existe.
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
     *
     * @param string $virtualModelId ID del modelo virtual.
     * @param int $index Índice de la imagen deseada (por defecto 0).
     * @return string Imagen codificada en base64.
     *
     * @throws Exception Si el modelo o la imagen no existe.
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
     *
     * @param string $storagePath Ruta relativa dentro de storage/app/public.
     * @return string Cadena en base64 de la imagen convertida a PNG.
     *
     * @throws \Exception Si la imagen no existe en disco.
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
     * Valida una imagen subida por el usuario.
     *
     * - Tamaño máximo: 10MB.
     * - Formatos permitidos: JPG, PNG.
     * - Resolución mínima: 300px lado más corto.
     * - Resolución máxima: 4096px lado más largo.
     *
     * @param UploadedFile $file Imagen subida.
     * @throws \Exception Si la imagen no cumple los requisitos.
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
     *
     * @param UploadedFile $file1
     * @param UploadedFile $file2
     * @return \Intervention\Image\Image
     */
    private function combineImages(UploadedFile $file1, UploadedFile $file2): \Intervention\Image\Image
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
