<?php

namespace App\Services\KlingAi;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;

class ImageProcessingService
{
    public function convertToBase64(UploadedFile $file): string
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

        return base64_encode($image->encode('png'));
    }

    public function combineImagesToBase64(UploadedFile $file1, UploadedFile $file2): string
    {
        if ($file1->getSize() > 10 * 1024 * 1024 || $file2->getSize() > 10 * 1024 * 1024) {
            throw new \Exception('Cada imagen no debe superar los 10MB');
        }

        $allowedMime = ['image/jpeg', 'image/png'];
        if (!in_array($file1->getMimeType(), $allowedMime) || !in_array($file2->getMimeType(), $allowedMime)) {
            throw new \Exception('Formato no permitido. Solo JPG y PNG.');
        }

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
}
