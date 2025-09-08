<?php 

namespace App\Services\ChatAi;

class ProcedimientosService
{
    public static function allProcedimientos(){
       return [
            // Procedimiento para la BD "sio"
            [
                'name' => 'sio.actualizar_stock_producto',
                'description' => 'Actualiza la cantidad de stock de un producto específico en la base de datos SIO.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'id_producto' => [
                            'type' => 'integer', 
                            'description' => 'El ID del producto a actualizar.'
                        ],
                        'nueva_cantidad' => [
                            'type' => 'integer', 
                            'description' => 'La nueva cantidad de stock.'
                        ]
                    ],
                    'required' => ['id_producto', 'nueva_cantidad']
                ]
            ],
            // Procedimiento para la BD "sit"
            [
                'name' => 'sit.actualizar_stock_producto',
                'description' => 'Actualiza la cantidad de stock de un producto específico en la base de datos SIT.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'id_producto' => [
                            'type' => 'integer', 
                            'description' => 'El ID del producto a actualizar.'
                        ],
                        'nueva_cantidad' => [
                            'type' => 'integer', 
                            'description' => 'La nueva cantidad de stock.'
                        ]
                    ],
                    'required' => ['id_producto', 'nueva_cantidad']
                ]
            ],
            // Agrega más procedimientos según sea necesario
        ];
    }
}