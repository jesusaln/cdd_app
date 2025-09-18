<?php

namespace Database\Seeders;

use App\Models\Herramienta;
use App\Models\Tecnico;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HerramientaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener técnicos existentes para asignar algunas herramientas
        $tecnicos = Tecnico::all();

        $herramientas = [
            [
                'nombre' => 'Taladro inalámbrico Bosch',
                'numero_serie' => 'TLR-BSH-001-2024',
                'foto' => null,
                'tecnico_id' => $tecnicos->isNotEmpty() ? $tecnicos->random()->id : null,
            ],
            [
                'nombre' => 'Sierra circular Makita',
                'numero_serie' => 'SCR-MKT-002-2024',
                'foto' => null,
                'tecnico_id' => $tecnicos->isNotEmpty() ? $tecnicos->random()->id : null,
            ],
            [
                'nombre' => 'Martillo neumático Hilti',
                'numero_serie' => 'MRT-HLT-003-2024',
                'foto' => null,
                'tecnico_id' => $tecnicos->isNotEmpty() ? $tecnicos->random()->id : null,
            ],
            [
                'nombre' => 'Soldador eléctrico Lincoln',
                'numero_serie' => 'SLD-LNC-004-2024',
                'foto' => null,
                'tecnico_id' => $tecnicos->isNotEmpty() ? $tecnicos->random()->id : null,
            ],
            [
                'nombre' => 'Generador portátil Honda',
                'numero_serie' => 'GEN-HND-005-2024',
                'foto' => null,
                'tecnico_id' => $tecnicos->isNotEmpty() ? $tecnicos->random()->id : null,
            ],
            [
                'nombre' => 'Compresor de aire Ingersoll Rand',
                'numero_serie' => 'CMP-IRD-006-2024',
                'foto' => null,
                'tecnico_id' => $tecnicos->isNotEmpty() ? $tecnicos->random()->id : null,
            ],
            [
                'nombre' => 'Llave de impacto Dewalt',
                'numero_serie' => 'LLV-DWT-007-2024',
                'foto' => null,
                'tecnico_id' => $tecnicos->isNotEmpty() ? $tecnicos->random()->id : null,
            ],
            [
                'nombre' => 'Multímetro digital Fluke',
                'numero_serie' => 'MLT-FLK-008-2024',
                'foto' => null,
                'tecnico_id' => $tecnicos->isNotEmpty() ? $tecnicos->random()->id : null,
            ],
            [
                'nombre' => 'Nivel láser Bosch',
                'numero_serie' => 'NVL-BSH-009-2024',
                'foto' => null,
                'tecnico_id' => $tecnicos->isNotEmpty() ? $tecnicos->random()->id : null,
            ],
            [
                'nombre' => 'Cortadora de plasma Miller',
                'numero_serie' => 'CRT-MLR-010-2024',
                'foto' => null,
                'tecnico_id' => $tecnicos->isNotEmpty() ? $tecnicos->random()->id : null,
            ],
            [
                'nombre' => 'Elevador hidráulico Genie',
                'numero_serie' => 'ELV-GNE-011-2024',
                'foto' => null,
                'tecnico_id' => $tecnicos->isNotEmpty() ? $tecnicos->random()->id : null,
            ],
            [
                'nombre' => 'Prensa hidráulica Enerpac',
                'numero_serie' => 'PRN-ENP-012-2024',
                'foto' => null,
                'tecnico_id' => $tecnicos->isNotEmpty() ? $tecnicos->random()->id : null,
            ],
            [
                'nombre' => 'Pulidora orbital Festool',
                'numero_serie' => 'PLD-FST-013-2024',
                'foto' => null,
                'tecnico_id' => $tecnicos->isNotEmpty() ? $tecnicos->random()->id : null,
            ],
            [
                'nombre' => 'Lijadora de banda Bosch',
                'numero_serie' => 'LJD-BSH-014-2024',
                'foto' => null,
                'tecnico_id' => $tecnicos->isNotEmpty() ? $tecnicos->random()->id : null,
            ],
            [
                'nombre' => 'Amoladora angular Makita',
                'numero_serie' => 'AML-MKT-015-2024',
                'foto' => null,
                'tecnico_id' => $tecnicos->isNotEmpty() ? $tecnicos->random()->id : null,
            ],
            [
                'nombre' => 'Destornillador eléctrico Bosch',
                'numero_serie' => 'DST-BSH-016-2024',
                'foto' => null,
                'tecnico_id' => $tecnicos->isNotEmpty() ? $tecnicos->random()->id : null,
            ],
            [
                'nombre' => 'Sierra de calar Dewalt',
                'numero_serie' => 'SRC-DWT-017-2024',
                'foto' => null,
                'tecnico_id' => $tecnicos->isNotEmpty() ? $tecnicos->random()->id : null,
            ],
            [
                'nombre' => 'Taladro de columna JET',
                'numero_serie' => 'TDC-JET-018-2024',
                'foto' => null,
                'tecnico_id' => $tecnicos->isNotEmpty() ? $tecnicos->random()->id : null,
            ],
            [
                'nombre' => 'Máquina de soldar MIG Miller',
                'numero_serie' => 'SLM-MLR-019-2024',
                'foto' => null,
                'tecnico_id' => $tecnicos->isNotEmpty() ? $tecnicos->random()->id : null,
            ],
            [
                'nombre' => 'Compresor de tornillo Atlas Copco',
                'numero_serie' => 'CMT-ACP-020-2024',
                'foto' => null,
                'tecnico_id' => $tecnicos->isNotEmpty() ? $tecnicos->random()->id : null,
            ],
            // Herramientas sin asignar (disponibles)
            [
                'nombre' => 'Kit de herramientas Stanley',
                'numero_serie' => 'KIT-STN-021-2024',
                'foto' => null,
                'tecnico_id' => null,
            ],
            [
                'nombre' => 'Medidor de distancia láser Leica',
                'numero_serie' => 'MED-LCA-022-2024',
                'foto' => null,
                'tecnico_id' => null,
            ],
            [
                'nombre' => 'Herramienta de torque digital',
                'numero_serie' => 'TRQ-DGT-023-2024',
                'foto' => null,
                'tecnico_id' => null,
            ],
            [
                'nombre' => 'Detector de voltaje Klein Tools',
                'numero_serie' => 'DTC-KLT-024-2024',
                'foto' => null,
                'tecnico_id' => null,
            ],
            [
                'nombre' => 'Herramienta de corte hidráulico',
                'numero_serie' => 'CRTHYD-025-2024',
                'foto' => null,
                'tecnico_id' => null,
            ]
        ];

        foreach ($herramientas as $herramienta) {
            Herramienta::create($herramienta);
        }
    }
}
