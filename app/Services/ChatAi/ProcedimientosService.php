<?php

namespace App\Services\ChatAi;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcedimientosService
{
    public static function allProcedimientos()
    {
        return [
            [
                'name' => 'virtual_try_on2.consultar_estado_tarea',
                'description' => 'Consulta el estado actual de una tarea que está realizando un modelo.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'p_task_id' => [
                            'type' => 'string',
                            'description' => 'El ID de la tarea a revisar.'
                        ]
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
                            'description' => 'El ID del usuario cuyo historial queremos revisar.'
                        ]
                    ],
                    'required' => ['p_user_id']
                ]
            ],
            // Agrega más procedimientos aquí
        ];
    }

    public static function ejecutarProcedimiento(string $nombre, array $args)
    {
        try {
            //Validar que el procedimiento exista en la whitelist
            $procedimientos = collect(self::allProcedimientos())->pluck('name')->toArray();
            if (!in_array($nombre, $procedimientos)) {
                throw new \Exception("Procedimiento no permitido: {$nombre}");
            }

            //Separar BD y SP
            [$bd, $sp] = explode('.', $nombre, 2);
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $bd) || !preg_match('/^[a-zA-Z0-9_]+$/', $sp)) {
                throw new \Exception("Nombre de BD o SP inválido.");
            }

            //Construir query segura con bindings
            $placeholders = implode(',', array_fill(0, count($args), '?'));
            $query = "CALL {$bd}.{$sp}({$placeholders})";

            return DB::select($query, array_values($args));

        } catch (\Exception $e) {
            Log::error("Error ejecutando {$nombre}: " . $e->getMessage());
            return ['error' => 'Error al ejecutar el procedimiento.'];
        }
    }
}
