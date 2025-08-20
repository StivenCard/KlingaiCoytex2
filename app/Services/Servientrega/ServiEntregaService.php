<?php

namespace App\Services\Servientrega;

use Illuminate\Support\Facades\Http;
use Exception;

class ServiEntregaService
{
    private string $baseUrl;
    private string $username;
    private string $password;

    /**
     * Inicializa el servicio con los datos de configuración desde el archivo `config/services.php`.
     *
     * @throws Exception Si falta alguna clave de configuración.
     */
    public function __construct()
    {
        $this->baseUrl = config('services.servientrega.api_url');
        $this->username = config('services.servientrega.username');
        $this->password = config('services.servientrega.password');

        if (!$this->baseUrl || !$this->username || !$this->password) {
            throw new Exception('servientrega configuration missing');
        }
    }


    public function consultarGuia($numeroGuia){
        
        try {
            // Realiza la petición GET con autenticación Basic
            $response = Http::withBasicAuth($this->username,$this->password)->get($this->baseUrl,[
                'NumeroGuia' => $numeroGuia
            ]);

            // Verifica si la petición fue exitosa
            if ($response->successful()) {
                // Obtiene la respuesta XML como una cadena de texto
                $xmlString = $response->body();

                // Analiza el XML y lo convierte en un objeto SimpleXMLElement
                $xmlObject = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA);

                // Convierte el objeto SimpleXMLElement a un array asociativo usando json_encode/json_decode
                $datosGuiaArray = json_decode(json_encode($xmlObject), true);
                /* dd($datosGuiaArray); */

                // Convierte el array PHP a un string JSON y lo devuelve como respuesta JSON
                return $datosGuiaArray;

            } else {
                // Maneja el error si la respuesta no fue exitosa
                return response()->json(['error' => 'No se pudo obtener la información de la guía. Código de estado: ' . $response->status()], $response->status());
            }

        } catch (\Exception $e) {
            // Maneja cualquier excepción (problemas de conexión, etc.)
            return response()->json(['error' => 'Ocurrió un error al conectar con el servicio: ' . $e->getMessage()], 500);
        }
    }
}