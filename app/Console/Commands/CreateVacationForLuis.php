<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Vacacion;
use App\Models\RegistroVacaciones;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CreateVacationForLuis extends Command
{
    protected $signature = 'vacations:create-for-luis';
    protected $description = 'Crea una vacación de 1 día para Luis hoy';

    public function handle()
    {
        // Buscar o crear el usuario Luis
        $luis = User::where('name', 'like', '%Luis%')->first();

        if (!$luis) {
            $this->error('No se encontró ningún usuario llamado Luis.');
            $this->info('Creando usuario Luis...');
            
            $luis = User::create([
                'name' => 'Luis',
                'email' => 'luis@empresa.com',
                'password' => bcrypt('password123'),
                'es_empleado' => true,
                'fecha_contratacion' => Carbon::now()->subYear(), // Contratado hace 1 año para tener días disponibles
                'puesto' => 'Empleado',
                'departamento' => 'General',
            ]);
            
            $this->info("Usuario Luis creado con ID: {$luis->id}");
        } else {
            $this->info("Usuario Luis encontrado: {$luis->name} (ID: {$luis->id})");
        }

        // Verificar o crear registro de vacaciones
        $registro = RegistroVacaciones::actualizarRegistroAnual($luis->id);
        
        if (!$registro) {
            $this->error('No se pudo crear el registro de vacaciones para Luis.');
            return 1;
        }

        $this->info("Días disponibles de Luis: {$registro->dias_disponibles}");

        // Crear la vacación para hoy (1 día)
        $hoy = Carbon::today()->toDateString();

        try {
            $vacacion = Vacacion::create([
                'user_id' => $luis->id,
                'fecha_inicio' => $hoy,
                'fecha_fin' => $hoy,
                'dias_solicitados' => 1,
                'dias_pendientes' => 1,
                'motivo' => 'Vacación de prueba creada automáticamente',
                'estado' => 'pendiente',
            ]);

            $this->info("✅ Vacación creada exitosamente para Luis:");
            $this->info("   - ID: {$vacacion->id}");
            $this->info("   - Fecha: {$hoy}");
            $this->info("   - Días: 1");
            $this->info("   - Estado: Pendiente");

            return 0;

        } catch (\Exception $e) {
            $this->error("Error al crear la vacación: " . $e->getMessage());
            return 1;
        }
    }
}