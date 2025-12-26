<?php

namespace Database\Seeders;

use App\Models\CargoType;
use Illuminate\Database\Seeder;

class CargoTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $cargoTypes = [
            [
                'code' => 'GENERAL',
                'name' => 'Carga general',
                'description' => 'Mercancías sin requisitos especiales de manipulación.',
                'requires_refrigeration' => false,
                'is_hazardous' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'PERISH',
                'name' => 'Perecibles',
                'description' => 'Alimentos y productos con vida útil corta que requieren entrega rápida.',
                'requires_refrigeration' => false,
                'is_hazardous' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'REFRIG',
                'name' => 'Refrigerados',
                'description' => 'Productos que necesitan cadena de frío controlada.',
                'requires_refrigeration' => true,
                'is_hazardous' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'HAZMAT',
                'name' => 'Materiales peligrosos',
                'description' => 'Sustancias reguladas que requieren protocolos especiales de seguridad.',
                'requires_refrigeration' => false,
                'is_hazardous' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'BULK',
                'name' => 'Granel',
                'description' => 'Cargas voluminosas o a granel como granos, minerales o líquidos.',
                'requires_refrigeration' => false,
                'is_hazardous' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'ENCOM',
                'name' => 'Encomiendas',
                'description' => 'Paquetería y encomiendas con entrega programada y trazabilidad.',
                'requires_refrigeration' => false,
                'is_hazardous' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'FRAGILE',
                'name' => 'Frágil',
                'description' => 'Mercadería frágil que requiere manipulación especial y sujeción adicional.',
                'requires_refrigeration' => false,
                'is_hazardous' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'HEAVY',
                'name' => 'Sobredimensionada',
                'description' => 'Carga sobredimensionada o pesada que puede requerir permisos, rutas y equipos especiales.',
                'requires_refrigeration' => false,
                'is_hazardous' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'LIQUID',
                'name' => 'Líquidos',
                'description' => 'Transporte de líquidos no peligrosos en contenedores o cisternas con control de derrames.',
                'requires_refrigeration' => false,
                'is_hazardous' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        CargoType::query()->upsert($cargoTypes, ['code'], [
            'name',
            'description',
            'requires_refrigeration',
            'is_hazardous',
            'updated_at',
        ]);
    }
}
