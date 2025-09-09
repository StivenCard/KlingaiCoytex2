<?php 

namespace App\Services\ChatAi;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcedimientosService
{
    public static function allProcedimientos(){
       return [
            [
                'name' => 'virtual_try_on2.consultar_estado_tarea',
                'description' => 'Consulta el estado actual de una tarea que está realizando algún modelo.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'p_task_id' => [
                            'type' => 'string',
                            'description' => 'El ID de la tarea a revisar.'
                        ],
                    ],
                    'required' => ['p_task_id']
                ]
            ],
            [
                'name' => 'virtual_try_on2.obtener_historial_usuario',
                'description' => 'Consulta el historial de conversaciones de un usuario específico.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'p_user_id' => [
                            'type' => 'string',
                            'description' => 'El ID del usuario al que queremos revisar su historial.'
                        ]
                    ],
                    'required' => ['p_user_id']
                ]
            ],
            // ... más procedimientos
        ];
    }

    public static function ejecutarProcedimiento($nombre, $args) {
        try {
            // $nombre ya viene en formato "bd.sp"
            [$bd, $sp] = explode('.', $nombre, 2);

            if (!$bd || !$sp) {
                throw new \Exception("Formato de procedimiento inválido: {$nombre}");
            }

            // Construir query con placeholders seguros
            $placeholders = implode(',', array_fill(0, count($args), '?'));
            $query = "CALL {$nombre}({$placeholders})";

            // Ejecutar SIEMPRE usando la única conexión configurada
            $result = DB::select($query, array_values($args));

            return $result;

        } catch (\Exception $e) {
            Log::error("Error al ejecutar el procedimiento {$nombre}: " . $e->getMessage());
            return ['error' => 'Error al ejecutar el procedimiento.'];
        }
    }
}
