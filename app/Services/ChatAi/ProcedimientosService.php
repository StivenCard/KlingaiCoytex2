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
                'name' => 'virtual_try_on2.consultar_historial_chat',
                'description' => 'Consulta el historial de conversaciones de un usuario en el chat.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'p_user_id' => [
                            'type' => 'string',
                            'description' => 'El ID del usuario cuyo historial se quiere consultar.'
                        ]
                    ],
                    'required' => ['p_user_id']
                ]
            ],
            [
                'name' => 'virtual_try_on2.consultar_estado_tarea',
                'description' => 'Consulta el estado actual de una tarea en image_to_videos.',
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
                'name' => 'virtual_try_on2.consultar_precio_modelo',
                'description' => 'Devuelve la configuración de precios y tokens de un modelo específico.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'p_model_name' => [
                            'type' => 'string',
                            'description' => 'El nombre del modelo (ej. kling-v1-6, kolors-v1-5).'
                        ]
                    ],
                    'required' => ['p_model_name']
                ]
            ],
            [
                'name' => 'virtual_try_on2.consultar_api_logs',
                'description' => 'Consulta los últimos registros de llamadas a la API.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'p_limit' => [
                            'type' => 'integer',
                            'description' => 'Número de registros a recuperar.'
                        ]
                    ],
                    'required' => ['p_limit']
                ]
            ],
            [
                'name' => 'virtual_try_on2.obtener_virtual_tryons_usuario',
                'description' => 'Obtiene los últimos virtual try-on realizados por un usuario.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'p_user_id' => [
                            'type' => 'string',
                            'description' => 'El ID del usuario a consultar.'
                        ]
                    ],
                    'required' => ['p_user_id']
                ]
            ],
            [
                'name' => 'virtual_try_on2.obtener_virtual_models_usuario',
                'description' => 'Obtiene los modelos virtuales generados por un usuario.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'p_user_id' => [
                            'type' => 'string',
                            'description' => 'El ID del usuario a consultar.'
                        ]
                    ],
                    'required' => ['p_user_id']
                ]
            ]
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
