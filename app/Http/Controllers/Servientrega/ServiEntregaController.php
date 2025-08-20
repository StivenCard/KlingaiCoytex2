<?php

namespace App\Http\Controllers\Servientrega;

use App\Services\Servientrega\ServiEntregaService;
use Illuminate\Http\Request;

class ServiEntregaController
{
    private $servientregaService;

    public function __construct(ServiEntregaService $servientregaService) {
        $this->servientregaService = $servientregaService;
    }

    public function consultaGuia(Request $request) {
        // Valida que el campo 'numero_guia' sea requerido
        // Si no existe, mostrará la vista del formulario sin datos
        $numeroGuia = $request->input('numero_guia');

        if (!$numeroGuia) {
            return view('servientrega.consultar-guia');
        }

        $validated = $request->validate([
            'numero_guia' => 'required|string|max:50'
        ]);

        $datosGuia = $this->servientregaService->consultarGuia($validated['numero_guia']);

        // Opcional: Usa dd() aquí si quieres ver la estructura antes de renderizar la vista
        // dd($datosGuia);

        // Pasa los datos (o el estado de error) a la vista
        return view('servientrega.consultar-guia', [
            'datosGuia' => $datosGuia,
        ]);
    }
}