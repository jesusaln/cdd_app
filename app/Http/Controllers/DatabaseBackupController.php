<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackupService;
use Illuminate\Support\Facades\Auth;
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
    public function index(Request $request)
    {
        try {
            $backups = $this->backupService->listBackups();

            // Formatear los datos de respaldos para el frontend
            $formattedBackups = collect($backups)->map(function ($backup) {
                return [
                    'name' => $backup['name'] ?? basename($backup['path']),
                    'path' => $backup['path'],
                    'size' => $backup['size'] ?? 0,
                    'created_at' => $backup['created_at'] ?? filemtime($backup['path']),
                    'compressed' => $this->isCompressed($backup['path']),
                ];
            })->values()->toArray();

            // Estadísticas
            $totalBackups = count($formattedBackups);
            $totalSize = $this->calculateTotalSize($formattedBackups);

            // Estadísticas adicionales
            $compressedCount = collect($formattedBackups)->where('compressed', true)->count();
            $uncompressedCount = $totalBackups - $compressedCount;

            // Filtros y búsqueda
            $search = $request->get('search', '');
            if ($search) {
                $formattedBackups = collect($formattedBackups)->filter(function ($backup) use ($search) {
                    return stripos($backup['name'], $search) !== false;
                })->values()->toArray();
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');

            $formattedBackups = collect($formattedBackups)->sortBy($sortBy, SORT_REGULAR, $sortDirection === 'desc')->values()->toArray();

            // Paginación
            $perPage = $request->get('per_page', 10);
            $currentPage = $request->get('page', 1);
            $paginatedBackups = collect($formattedBackups)->forPage($currentPage, $perPage);

            return Inertia::render('DatabaseBackup/Index', [
                'backups' => $paginatedBackups->values()->toArray(),
                'stats' => [
                    'total' => $totalBackups,
                    'total_size' => $totalSize,
                    'compressed' => $compressedCount,
                    'uncompressed' => $uncompressedCount,
                ],
                'filters' => [
                    'search' => $search,
                ],
                'sorting' => [
                    'sort_by' => $sortBy,
                    'sort_direction' => $sortDirection,
                ],
                'pagination' => [
                    'current_page' => $currentPage,
                    'last_page' => ceil(count($formattedBackups) / $perPage),
                    'per_page' => $perPage,
                    'from' => (($currentPage - 1) * $perPage) + 1,
                    'to' => min($currentPage * $perPage, count($formattedBackups)),
                    'total' => count($formattedBackups),
                ],
                'mysqldump_available' => $this->backupService->isMysqldumpAvailable(),
                'total_backups' => $totalBackups,
                'total_size' => $totalSize
            ]);
        } catch (Exception $e) {
            Log::error('Error loading backup index: ' . $e->getMessage());

            return Inertia::render('DatabaseBackup/Index', [
                'backups' => [],
                'stats' => [
                    'total' => 0,
                    'total_size' => 0,
                    'compressed' => 0,
                    'uncompressed' => 0,
                ],
                'filters' => ['search' => ''],
                'sorting' => ['sort_by' => 'created_at', 'sort_direction' => 'desc'],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 10,
                    'from' => 0,
                    'to' => 0,
                    'total' => 0,
                ],
                'mysqldump_available' => false,
                'total_backups' => 0,
                'total_size' => 0
            ])->with('error', 'Error al cargar la lista de respaldos: ' . $e->getMessage());
        }
    }

    /**
     * Crear un nuevo respaldo de la base de datos
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9_\-\.]+$/',
            'compress' => 'boolean',
            'include_structure_only' => 'boolean'
        ], [
            'name.regex' => 'El nombre del respaldo solo puede contener letras, números, guiones, guiones bajos y puntos.',
            'name.max' => 'El nombre del respaldo no puede exceder 255 caracteres.'
        ]);

        try {
            // Generar nombre por defecto si no se proporciona
            $backupName = $validated['name'] ?: $this->generateDefaultBackupName();

            $result = $this->backupService->createBackup([
                'name' => $backupName,
                'compress' => $validated['compress'] ?? true,
                'include_structure_only' => $validated['include_structure_only'] ?? false
            ]);

            if ($result['success']) {
                return redirect()->route('backup.index')
                    ->with('success', $result['message'] . ' Tamaño: ' . $this->formatFileSize($result['size'] ?? 0));
            }

            return redirect()->route('backup.index')->with('error', $result['message']);
        } catch (Exception $e) {
            Log::error('Error creating backup: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            return redirect()->route('backup.index')
                ->with('error', 'Error interno al crear el respaldo. Por favor, inténtelo de nuevo.');
        }
    }

    /**
     * Descargar un archivo de respaldo
     */
    public function download($filename)
    {
        try {
            // Sanitizar el nombre del archivo
            $filename = basename($filename);

            // Validar que el archivo existe y es seguro
            if (!$this->backupService->backupExists($filename)) {
                return redirect()->route('backup.index')->with('error', 'El archivo de respaldo no existe.');
            }

            $filePath = $this->backupService->getBackupPath($filename);

            if (!file_exists($filePath)) {
                return redirect()->route('backup.index')->with('error', 'El archivo físico no se encuentra en el servidor.');
            }

            return response()->download($filePath, $filename, [
                'Content-Type' => $this->getContentType($filename),
                'Cache-Control' => 'no-cache, must-revalidate',
                'Expires' => 'Sat, 26 Jul 1997 05:00:00 GMT'
            ]);
        } catch (Exception $e) {
            Log::error('Error downloading backup: ' . $e->getMessage(), [
                'filename' => $filename,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('backup.index')->with('error', 'Error al descargar el archivo de respaldo.');
        }
    }

    /**
     * Eliminar un archivo de respaldo
     */
    public function delete($filename)
    {
        try {
            $filename = basename($filename);

            if (!$this->backupService->backupExists($filename)) {
                return redirect()->route('backup.index')->with('error', 'El archivo de respaldo no existe.');
            }

            $result = $this->backupService->deleteBackup($filename);

            if ($result['success']) {
                return redirect()->route('backup.index')
                    ->with('success', 'Respaldo eliminado exitosamente.');
            }

            return redirect()->route('backup.index')->with('error', $result['message']);
        } catch (Exception $e) {
            Log::error('Error deleting backup: ' . $e->getMessage(), [
                'filename' => $filename,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('backup.index')->with('error', 'Error al eliminar el archivo de respaldo.');
        }
    }

    /**
     * Restaurar la base de datos desde un respaldo
     */
    public function restore($filename)
    {
        try {
            $filename = basename($filename);

            if (!$this->backupService->backupExists($filename)) {
                return redirect()->route('backup.index')->with('error', 'El archivo de respaldo no existe.');
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

            return redirect()->route('backup.index')->with('error', $result['message']);
        } catch (Exception $e) {
            Log::error('Error restoring backup: ' . $e->getMessage(), [
                'filename' => $filename,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('backup.index')
                ->with('error', 'Error crítico durante la restauración. Verifique el estado de la base de datos.');
        }
    }

    /**
     * Limpiar respaldos antiguos
     */
    public function clean(Request $request)
    {
        $validated = $request->validate([
            'days_old' => 'required|integer|min:1|max:365'
        ], [
            'days_old.required' => 'Debe especificar la antigüedad de los archivos a eliminar.',
            'days_old.integer' => 'La antigüedad debe ser un número entero.',
            'days_old.min' => 'La antigüedad mínima es 1 día.',
            'days_old.max' => 'La antigüedad máxima es 365 días.'
        ]);

        try {
            $daysOld = $validated['days_old'];
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

            return redirect()->route('backup.index')->with('error', $result['message']);
        } catch (Exception $e) {
            Log::error('Error cleaning old backups: ' . $e->getMessage(), [
                'days_old' => $validated['days_old'],
                'user_id' => Auth::id()
            ]);

            return redirect()->route('backup.index')->with('error', 'Error al limpiar los respaldos antiguos.');
        }
    }

    /**
     * Obtener estadísticas de los respaldos (AJAX)
     */
    public function stats()
    {
        try {
            $backups = $this->backupService->listBackups();
            $formattedBackups = collect($backups)->map(function ($backup) {
                return [
                    'name' => $backup['name'] ?? basename($backup['path']),
                    'path' => $backup['path'],
                    'size' => $backup['size'] ?? 0,
                    'created_at' => $backup['created_at'] ?? filemtime($backup['path']),
                ];
            })->values()->toArray();

            $stats = [
                'total_backups' => count($formattedBackups),
                'total_size' => $this->calculateTotalSize($formattedBackups),
                'total_size_formatted' => $this->formatFileSize($this->calculateTotalSize($formattedBackups)),
                'oldest_backup' => $this->getOldestBackup($formattedBackups),
                'newest_backup' => $this->getNewestBackup($formattedBackups),
                'mysqldump_available' => $this->backupService->isMysqldumpAvailable(),
                'disk_space_available' => $this->backupService->getAvailableDiskSpace() ?? 0,
                'disk_space_available_formatted' => $this->formatFileSize($this->backupService->getAvailableDiskSpace() ?? 0)
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
            $filename = basename($filename);
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
            $filename = basename($filename);
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

    private function isCompressed(string $filename): bool
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        return in_array(strtolower($extension), ['zip', 'gz', 'tar', '7z']);
    }
}
