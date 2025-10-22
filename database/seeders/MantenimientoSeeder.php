<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mantenimiento;
use App\Models\Carro;
use Carbon\Carbon;

class MantenimientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar que existan vehículos para asociar mantenimientos
        $carros = Carro::all();

        if ($carros->isEmpty()) {
            $this->command->warn('No hay vehículos registrados. Crea algunos vehículos primero.');
            return;
        }

        // Limpiar mantenimientos existentes
        Mantenimiento::truncate();

        $tiposServicio = [
            'Cambio de aceite',
            'Revisión periódica',
            'Servicio de frenos',
            'Servicio de llantas',
            'Servicio de batería',
            'Servicio de motor',
            'Revisión de luces',
            'Alineación y balanceo',
            'Cambio de filtros',
            'Revisión de transmisión',
            'Otro servicio'
        ];

        $prioridades = ['baja', 'media', 'alta', 'critica'];
        $estados = ['pendiente', 'en_proceso', 'completado'];

        $this->command->info('Creando mantenimientos de prueba...');

        // Crear mantenimientos de ejemplo
        foreach ($carros as $index => $carro) {
            // Crear entre 3 y 8 mantenimientos por vehículo
            $numMantenimientos = rand(3, 8);

            for ($i = 0; $i < $numMantenimientos; $i++) {
                $tipo = $tiposServicio[array_rand($tiposServicio)];
                $prioridad = $prioridades[array_rand($prioridades)];

                // Calcular fechas basadas en el índice para variedad
                $fechaBase = Carbon::now()->subMonths(rand(0, 12));
                $diasAnticipacion = $this->getDiasAnticipacionPorTipo($tipo);

                // Crear fechas de servicio y próximo mantenimiento
                $fechaServicio = $fechaBase->copy()->subDays(rand(30, 180));
                $proximoMantenimiento = $fechaServicio->copy()->addDays(rand(30, 365));

                // Estado basado en fechas
                $estado = $this->determinarEstadoPorFechas($proximoMantenimiento);

                // Crear mantenimiento
                Mantenimiento::create([
                    'carro_id' => $carro->id,
                    'tipo' => $tipo,
                    'fecha' => $fechaServicio->format('Y-m-d'),
                    'proximo_mantenimiento' => $proximoMantenimiento->format('Y-m-d'),
                    'descripcion' => "Mantenimiento de prueba - {$tipo}",
                    'notas' => "Mantenimiento generado automáticamente para pruebas. Vehículo: {$carro->marca} {$carro->modelo}",
                    'costo' => $this->getCostoPorTipo($tipo),
                    'estado' => $estado,
                    'kilometraje_actual' => $carro->kilometraje + rand(1000, 50000),
                    'prioridad' => $prioridad,
                    'dias_anticipacion_alerta' => $diasAnticipacion,
                    'requiere_aprobacion' => rand(0, 1) && in_array($prioridad, ['alta', 'critica']),
                    'observaciones_alerta' => "Alerta automática generada para pruebas",
                ]);
            }
        }

        $totalMantenimientos = Mantenimiento::count();
        $this->command->info("✅ Creados {$totalMantenimientos} mantenimientos de prueba exitosamente!");

        // Mostrar estadísticas
        $estadisticas = [
            'total' => $totalMantenimientos,
            'completados' => Mantenimiento::where('estado', 'completado')->count(),
            'pendientes' => Mantenimiento::where('estado', 'pendiente')->count(),
            'en_proceso' => Mantenimiento::where('estado', 'en_proceso')->count(),
            'vencidos' => Mantenimiento::vencidos()->count(),
            'por_vencer' => Mantenimiento::porVencer()->count(),
            'al_dia' => Mantenimiento::alDia()->count(),
        ];

        $this->command->table(
            ['Estado', 'Cantidad'],
            [
                ['Total', $estadisticas['total']],
                ['Completados', $estadisticas['completados']],
                ['Pendientes', $estadisticas['pendientes']],
                ['En Proceso', $estadisticas['en_proceso']],
                ['Vencidos (🔴)', $estadisticas['vencidos']],
                ['Por Vencer (🟠)', $estadisticas['por_vencer']],
                ['Al Día (🔵)', $estadisticas['al_dia']],
            ]
        );
    }

    /**
     * Obtener días de anticipación según tipo de servicio
     */
    private function getDiasAnticipacionPorTipo(string $tipo): int
    {
        return match ($tipo) {
            'Cambio de aceite' => 30,
            'Revisión periódica' => 60,
            'Servicio de frenos' => 90,
            'Servicio de llantas' => 180,
            'Servicio de batería' => 180,
            'Servicio de motor' => 120,
            'Revisión de luces' => 30,
            'Alineación y balanceo' => 180,
            'Cambio de filtros' => 60,
            'Revisión de transmisión' => 120,
            'Otro servicio' => 30,
            default => 30,
        };
    }

    /**
     * Obtener costo aproximado según tipo de servicio
     */
    private function getCostoPorTipo(string $tipo): float
    {
        $costosBase = [
            'Cambio de aceite' => 800.00,
            'Revisión periódica' => 1200.00,
            'Servicio de frenos' => 2500.00,
            'Servicio de llantas' => 600.00,
            'Servicio de batería' => 1800.00,
            'Servicio de motor' => 3500.00,
            'Revisión de luces' => 300.00,
            'Alineación y balanceo' => 800.00,
            'Cambio de filtros' => 400.00,
            'Revisión de transmisión' => 2000.00,
            'Otro servicio' => 500.00,
        ];

        $costoBase = $costosBase[$tipo] ?? 500.00;

        // Agregar variación aleatoria del ±20%
        $variacion = $costoBase * 0.2;
        return round($costoBase + rand(-$variacion * 100, $variacion * 100) / 100, 2);
    }

    /**
     * Determinar estado basado en fechas (para crear variedad en datos de prueba)
     */
    private function determinarEstadoPorFechas(Carbon $proximoMantenimiento): string
    {
        $hoy = Carbon::now();
        $diasDiferencia = $hoy->diffInDays($proximoMantenimiento, false);

        // Si ya pasó la fecha, 70% de probabilidad de estar completado
        if ($diasDiferencia < 0) {
            return rand(1, 10) <= 7 ? 'completado' : 'pendiente';
        }

        // Si está próximo (7 días o menos), 80% de probabilidad de estar en proceso
        if ($diasDiferencia <= 7) {
            return rand(1, 10) <= 8 ? 'en_proceso' : 'pendiente';
        }

        // Si falta más tiempo, 90% de probabilidad de estar pendiente
        return rand(1, 10) <= 9 ? 'pendiente' : 'en_proceso';
    }
}
