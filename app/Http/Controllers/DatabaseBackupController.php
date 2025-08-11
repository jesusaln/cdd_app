<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Exception;

class DatabaseBackupController extends Controller
{
    protected $backupService;

    public function __construct(DatabaseBackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Mostrar la página principal de gestión de respaldos
     */
    public function index()
    {
        try {
            $backups = $this->backupService->listBackups();

            return Inertia::render('DatabaseBackup/Index', [
                'backups' => $backups,
                'mysqldump_available' => $this->backupService->isMysqldumpAvailable(),
                'total_backups' => count($backups),
                'total_size' => $this->calculateTotalSize($backups)
            ]);
        } catch (Exception $e) {
            Log::error('Error loading backup index: ' . $e->getMessage());

            return Inertia::render('DatabaseBackup/Index', [
                'backups' => [],
                'mysqldump_available' => false,
                'total_backups' => 0,
                'total_size' => 0
            ])->with('error', 'Error al cargar la lista de respaldos');
        }
    }

    /**
     * Crear un nuevo respaldo de la base de datos
     */
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9_\-\.]+$/',
            'compress' => 'boolean',
            'include_structure_only' => 'boolean'
        ], [
            'name.regex' => 'El nombre del respaldo solo puede contener letras, números, guiones, guiones bajos y puntos.',
            'name.max' => 'El nombre del respaldo no puede exceder 255 caracteres.'
        ]);

        try {
            // Generar nombre por defecto si no se proporciona
            $backupName = $request->name ?: $this->generateDefaultBackupName();

            $result = $this->backupService->createBackup([
                'name' => $backupName,
                'compress' => $request->boolean('compress', true),
                'include_structure_only' => $request->boolean('include_structure_only', false)
            ]);

            if ($result['success']) {
                return redirect()->route('backup.index')
                    ->with('success', $result['message'] . ' Tamaño: ' . $this->formatFileSize($result['size'] ?? 0));
            }

            return back()->with('error', $result['message']);
        } catch (Exception $e) {
            Log::error('Error creating backup: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            return back()->with('error', 'Error interno al crear el respaldo. Por favor, inténtelo de nuevo.');
        }
    }

    /**
     * Descargar un archivo de respaldo
     */
    public function download($filename)
    {
        try {
            // Validar que el archivo existe y es seguro
            if (!$this->backupService->backupExists($filename)) {
                return back()->with('error', 'El archivo de respaldo no existe.');
            }

            $filePath = $this->backupService->getBackupPath($filename);

            if (!file_exists($filePath)) {
                return back()->with('error', 'El archivo físico no se encuentra en el servidor.');
            }

            return response()->download($filePath, $filename, [
                'Content-Type' => $this->getContentType($filename),
                'Cache-Control' => 'no-cache, must-revalidate',
                'Expires' => 'Sat, 26 Jul 1997 05:00:00 GMT'
            ]);
        } catch (Exception $e) {
            Log::error('Error downloading backup: ' . $e->getMessage(), [
                'filename' => $filename,
                'user_id' => auth()->id()
            ]);

            return back()->with('error', 'Error al descargar el archivo de respaldo.');
        }
    }

    /**
     * Eliminar un archivo de respaldo
     */
    public function delete($filename)
    {
        try {
            if (!$this->backupService->backupExists($filename)) {
                return back()->with('error', 'El archivo de respaldo no existe.');
            }

            $result = $this->backupService->deleteBackup($filename);

            if ($result['success']) {
                return redirect()->route('backup.index')
                    ->with('success', 'Respaldo eliminado exitosamente.');
            }

            return back()->with('error', $result['message']);
        } catch (Exception $e) {
            Log::error('Error deleting backup: ' . $e->getMessage(), [
                'filename' => $filename,
                'user_id' => auth()->id()
            ]);

            return back()->with('error', 'Error al eliminar el archivo de respaldo.');
        }
    }

    /**
     * Restaurar la base de datos desde un respaldo
     */
    public function restore($filename)
    {
        try {
            if (!$this->backupService->backupExists($filename)) {
                return back()->with('error', 'El archivo de respaldo no existe.');
            }

            // Crear un respaldo automático antes de la restauración
            $preRestoreBackup = $this->backupService->createBackup([
                'name' => 'pre_restore_' . date('Y-m-d_H-i-s'),
                'compress' => true,
                'include_structure_only' => false
            ]);

            if (!$preRestoreBackup['success']) {
                Log::warning('Could not create pre-restore backup: ' . $preRestoreBackup['message']);
            }

            $result = $this->backupService->restoreBackup($filename);

            if ($result['success']) {
                $message = 'Base de datos restaurada exitosamente.';
                if ($preRestoreBackup['success']) {
                    $message .= ' Se creó un respaldo automático antes de la restauración.';
                }

                return redirect()->route('backup.index')->with('success', $message);
            }

            return back()->with('error', $result['message']);
        } catch (Exception $e) {
            Log::error('Error restoring backup: ' . $e->getMessage(), [
                'filename' => $filename,
                'user_id' => auth()->id()
            ]);

            return back()->with('error', 'Error crítico durante la restauración. Verifique el estado de la base de datos.');
        }
    }

    /**
     * Limpiar respaldos antiguos
     */
    public function clean(Request $request)
    {
        $request->validate([
            'days_old' => 'required|integer|min:1|max:365'
        ], [
            'days_old.required' => 'Debe especificar la antigüedad de los archivos a eliminar.',
            'days_old.integer' => 'La antigüedad debe ser un número entero.',
            'days_old.min' => 'La antigüedad mínima es 1 día.',
            'days_old.max' => 'La antigüedad máxima es 365 días.'
        ]);

        try {
            $daysOld = $request->integer('days_old', 30);
            $result = $this->backupService->cleanOldBackups($daysOld);

            if ($result['success']) {
                $deletedCount = $result['deleted_count'] ?? 0;
                $freedSpace = $result['freed_space'] ?? 0;

                $message = "Se eliminaron {$deletedCount} respaldos antiguos";
                if ($freedSpace > 0) {
                    $message .= " liberando " . $this->formatFileSize($freedSpace) . " de espacio";
                }
                $message .= ".";

                return redirect()->route('backup.index')->with('success', $message);
            }

            return back()->with('error', $result['message']);
        } catch (Exception $e) {
            Log::error('Error cleaning old backups: ' . $e->getMessage(), [
                'days_old' => $request->input('days_old'),
                'user_id' => auth()->id()
            ]);

            return back()->with('error', 'Error al limpiar los respaldos antiguos.');
        }
    }

    /**
     * Obtener estadísticas de los respaldos (AJAX)
     */
    public function stats()
    {
        try {
            $backups = $this->backupService->listBackups();
            $stats = [
                'total_backups' => count($backups),
                'total_size' => $this->calculateTotalSize($backups),
                'oldest_backup' => $this->getOldestBackup($backups),
                'newest_backup' => $this->getNewestBackup($backups),
                'mysqldump_available' => $this->backupService->isMysqldumpAvailable(),
                'disk_space_available' => $this->backupService->getAvailableDiskSpace()
            ];

            return response()->json($stats);
        } catch (Exception $e) {
            Log::error('Error getting backup stats: ' . $e->getMessage());

            return response()->json([
                'error' => 'Error al obtener estadísticas'
            ], 500);
        }
    }

    /**
     * Verificar el estado de un respaldo específico (AJAX)
     */
    public function verify($filename)
    {
        try {
            $result = $this->backupService->verifyBackup($filename);
            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Error verifying backup: ' . $e->getMessage(), ['filename' => $filename]);

            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el respaldo'
            ], 500);
        }
    }

    /**
     * Obtener información detallada de un respaldo (AJAX)
     */
    public function info($filename)
    {
        try {
            $info = $this->backupService->getBackupInfo($filename);
            return response()->json($info);
        } catch (Exception $e) {
            Log::error('Error getting backup info: ' . $e->getMessage(), ['filename' => $filename]);

            return response()->json([
                'error' => 'Error al obtener información del respaldo'
            ], 500);
        }
    }

    /**
     * Métodos utilitarios privados
     */
    private function generateDefaultBackupName(): string
    {
        return 'backup_' . date('Y-m-d_H-i-s');
    }

    private function calculateTotalSize(array $backups): int
    {
        return array_sum(array_column($backups, 'size'));
    }

    private function getOldestBackup(array $backups): ?array
    {
        if (empty($backups)) return null;

        return collect($backups)->sortBy('created_at')->first();
    }

    private function getNewestBackup(array $backups): ?array
    {
        if (empty($backups)) return null;

        return collect($backups)->sortByDesc('created_at')->first();
    }

    private function formatFileSize(int $bytes): string
    {
        if ($bytes === 0) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);

        return round($bytes / (1024 ** $power), 2) . ' ' . $units[$power];
    }

    private function getContentType(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        return match (strtolower($extension)) {
            'sql' => 'application/sql',
            'zip' => 'application/zip',
            'gz' => 'application/gzip',
            'tar' => 'application/x-tar',
            default => 'application/octet-stream'
        };
    }
}
