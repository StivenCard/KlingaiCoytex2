<?php

namespace App\Services\ChatAi;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;

class ProcedimientosService
{
    public static function allProcedimientos()
    {
        return [
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
                'description' => 'Obtiene los últimos virtual try-on realizados por un usuario y permite preguntar por sus características.',
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
                'description' => 'Obtiene los modelos virtuales generados por un usuario y permite preguntar por sus características.',
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
                'name' => 'virtual_try_on2.obtener_todos_los_precios',
                'description' => 'Obtiene la lista de precios de todos los modelos y puede identificar el modelo más caro y el más barato.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => new stdClass,
                    'required' => []
                ]
            ],

            //Procedimientos para tabla eficiencia talleres
            [
                'name' => 'virtual_try_on2.calcular_eficiencia_taller_por_anio',
                'description' => 'Calcula la eficiencia o promedio de eficiencia de un taller específico en un año determinado y permite preguntar por sus características.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'nombre_taller_param' => [
                            'type' => 'string',
                            'description' => 'El nombre del taller a consultar.'
                        ],
                        'anio_param' => [
                            'type' => 'string',
                            'description' => 'El año a consultar.'
                        ]
                    ],
                    'required' => ['nombre_taller_param', 'anio_param']
                ]
            ],

            [
                'name' => 'virtual_try_on2.obtener_mejores_talleres_por_eficiencia',
                'description' => 'Recupera los mejores talleres de un año específico, ordenados por su eficiencia promedio calculada a partir de registros anuales. '.
                                'Agrupa los resultados por nombre de taller, calcula la eficiencia promedio para cada uno y limita la salida según el número máximo indicado.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'anio_param' => [
                            'type' => 'string',
                            'description' => 'El año para el cual se desea consultar la eficiencia de los talleres.'
                        ],
                        'limite' => [
                            'type' => 'integer',
                            'description' => 'Cantidad máxima de talleres que se retornarán en el resultado.'
                        ]
                    ],
                    'required' => ['anio_param', 'limite_param']
                ]
            ],

            [
                'name' => 'virtual_try_on2.obtener_resumen_taller',
                'description' => 'Proporciona un resumen detallado de un taller específico en un año determinado. Permite analizar el desempeño del taller en el período indicado. ',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'nombre_taller_param' => [
                            'type' => 'string',
                            'description' => 'El nombre del taller a consultar.'
                        ],
                        'anio_param' => [
                            'type' => 'string',
                            'description' => 'El año a consultar.'
                        ]
                    ],
                    'required' => ['nombre_taller_param', 'anio_param']
                ]
            ],
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
