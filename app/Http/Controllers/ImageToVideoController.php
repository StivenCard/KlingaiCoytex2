<?php

namespace App\Http\Controllers;

use App\Models\VirtualTryOn;
use Illuminate\Http\Request;
use App\Services\KlingAi\KlingApiService;

class ImageToVideoController extends Controller
{
    /**
     * Inyección de dependencias de servicios necesarios para el módulo Try-On.
     *
     * @param KlingApiService $api Servicio para comunicarse con la API de KlingAI
     * @param ImageProcessingService $images Servicio de procesamiento de imágenes (base64, combinación, guardado)
     * @param GuidelinesService $guidelines Servicio para obtener modelos e imágenes de ejemplo válidas/incorrectas
     */
    public function __construct(
        private KlingApiService $api,
    ) {}

    /**
     * Muestra la vista principal del módulo Virtual Try-On.
     * Carga:
     * - Modelos por defecto (desde carpeta pública)
     * - Modelos virtuales ya generados por el usuario
     * - Intentos anteriores de Try-On
     * - Imágenes guía válidas e inválidas
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {

        $virtualTryOns = VirtualTryOn::where('status', 'completed')
            ->latest()
            ->take(20)
            ->get();

        $existingVideos = VirtualTryOn::where('status', '!=', 'failed')
            ->latest()
            ->take(20)
            ->get();

        return view('image-to-video', compact('virtualTryOns', 'existingVideos'));
    }

    /**
     * Procesa una solicitud para generar una nueva imagen Try-On a través de la API externa de KlingAI.
     * Valida el tipo de modelo, prenda y prepara la imagen en base64.
     * Luego, realiza el llamado y guarda el registro en base de datos con estado `processing`.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(Request $request)
    {
        $request->validate([
            'model_source' => 'required|in:virtual,default,upload',
            'selected_default_model' => 'required_if:model_source,default',
            'selected_virtual_model' => 'required_if:model_source,virtual|exists:virtual_models,id',
            'selected_virtual_index' => 'nullable|integer|min:0|max:3',
            'human_image' => 'required_if:model_source,upload|file|image|mimes:jpg,jpeg,png|max:51200',
            'garment_type' => 'required|in:single,multiple',
            'single_garment' => 'required_if:garment_type,single|file|image|mimes:jpg,jpeg,png|max:51200',
            'top_garment' => 'required_if:garment_type,multiple|file|image|mimes:jpg,jpeg,png|max:51200',
            'bottom_garment' => 'required_if:garment_type,multiple|file|image|mimes:jpg,jpeg,png|max:51200',
            'output_count' => 'required|integer|min:1|max:4',
        ]);

        try {

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
