<?php

// app/Console/Commands/DatabaseBackupCommand.php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class DatabaseBackupCommand extends Command
{
    /**
     * El nombre y firma del comando.
     *
     * @var string
     */
    protected $signature = 'db:backup
                            {--name= : Nombre personalizado para el respaldo}
                            {--compress : Comprimir el respaldo en ZIP}
                            {--clean= : Eliminar respaldos con más de N días (opcional)}';

    /**
     * La descripción del comando.
     *
     * @var string
     */
    protected $description = 'Crea un respaldo de la base de datos (SQL o ZIP)';

    /**
     * Ejecuta el comando.
     */
    public function handle(DatabaseBackupService $backupService): int
    {
        $this->components->info('Iniciando respaldo de la base de datos...');

        $options = [
            'name'     => $this->option('name'),
            'compress' => $this->option('compress'),
        ];

        $result = $backupService->createBackup($options);

        if (! $result['success']) {
            $this->components->error($result['message']);
            return self::FAILURE;
        }

        // Mostrar detalles del respaldo
        $this->info('✅ Respaldo creado exitosamente');
        $this->newLine();
        $this->table(
            ['Descripción', 'Valor'],
            [
                ['Nombre del archivo' => $result['path']],
                ['Tamaño' => $this->formatBytes($result['size'])],
                ['Método' => ucfirst($result['method'] ?? 'laravel')],
                ['Compresión' => $options['compress'] ? 'Sí (ZIP)' : 'No'],
            ]
        );

        // Limpiar respaldos antiguos
        if ($cleanDays = $this->option('clean')) {
            $deleted = $backupService->cleanOldBackups((int) $cleanDays);
            $this->newLine();
            $this->components->info("🧹 Se eliminaron {$deleted} respaldos con más de {$cleanDays} días.");
        }

        $this->newLine();
        $this->components->info('Respaldo completado exitosamente.');

        return self::SUCCESS;
    }

    /**
     * Formatea bytes a una unidad legible.
     */
    protected function formatBytes(int $size, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = $size > 0 ? floor(log($size, 1024)) : 0;
        $base = min($base, count($units) - 1);

        $value = $size / pow(1024, $base);

        return round($value, $precision) . ' ' . $units[$base];
    }
}
