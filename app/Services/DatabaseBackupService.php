<?php

namespace App\Services;

use App\Models\BackupLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use ZipArchive;

/**
 * Servicio para gestionar respaldos de la base de datos.
 */
class DatabaseBackupService
{
    protected string $backupDisk = 'local';
    protected string $backupPath = 'backups/database/';

    /**
     * Crear un nuevo respaldo de la base de datos.
     *
     * @param array $options Opciones: 'name', 'compress'
     * @return array ['success' => bool, 'message' => string, 'path' => string, 'size' => int]
     */
    public function createBackup(array $options = []): array
    {
        $filename = $this->generateFilename($options['name'] ?? null);
        $fullPath = $this->backupPath . $filename;

        $logData = [
            'filename' => basename($fullPath),
            'path' => $fullPath,
            'type' => 'sql',
            'method' => null,
            'status' => 'failed',
            'message' => null,
            'metadata' => [
                'compress' => $options['compress'] ?? false,
                'driver' => config('database.default'),
            ]
        ];

        try {
            if (!$this->hasEnoughDiskSpace(500 * 1024 * 1024)) {
                throw new \Exception('Espacio en disco insuficiente');
            }

            $driver = config('database.default');
            if ($driver === 'mysql' && $this->isMysqldumpAvailable()) {
                $result = $this->createBackupWithMysqldump($fullPath);
                $logData['method'] = 'mysqldump';
            } else {
                $result = $this->createBackupWithLaravel($fullPath);
                $logData['method'] = 'laravel';
            }

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            $logData['status'] = 'success';
            $logData['size'] = $result['size'];
            $logData['message'] = $result['message'];

            if (($options['compress'] ?? false) && $result['success']) {
                $compressResult = $this->compressBackup($result['path']);
                if ($compressResult['success']) {
                    $result = $compressResult;
                    $logData['path'] = $result['path'];
                    $logData['type'] = 'zip';
                    $logData['size'] = $result['size'];
                    $logData['message'] .= ' (comprimido)';
                } else {
                    throw new \Exception('Compresión fallida: ' . $compressResult['message']);
                }
            }

            BackupLog::create($logData);

            return $result;
        } catch (\Exception $e) {
            $logData['message'] = $e->getMessage();
            BackupLog::create($logData);

            Log::error('DatabaseBackupService - createBackup failed', [
                'exception' => $e,
                'options' => $options
            ]);

            return [
                'success' => false,
                'message' => 'Error al crear respaldo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Crear respaldo usando mysqldump (solo MySQL).
     *
     * @param string $path Ruta relativa
     * @return array
     */
    protected function createBackupWithMysqldump(string $path): array
    {
        $config = config('database.connections.' . config('database.default'));
        $storagePath = storage_path('app/' . $path);

        $directory = dirname($storagePath);
        if (!file_exists($directory)) mkdir($directory, 0755, true);

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers --no-tablespaces %s > %s 2>&1',
            escapeshellarg($config['host']),
            escapeshellarg($config['port'] ?? 3306),
            escapeshellarg($config['username']),
            escapeshellarg($config['password']),
            escapeshellarg($config['database']),
            escapeshellarg($storagePath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode === 0 && file_exists($storagePath) && filesize($storagePath) > 0) {
            return [
                'success' => true,
                'message' => 'Respaldo creado con mysqldump',
                'path' => $path,
                'size' => filesize($storagePath)
            ];
        }

        $error = implode("\n", $output);
        Log::error('mysqldump failed', ['command' => $command, 'output' => $error]);
        throw new \Exception("Error en mysqldump: {$error}");
    }

    /**
     * Crear respaldo usando Laravel (portable entre drivers).
     *
     * @param string $path Ruta relativa
     * @return array
     */
    protected function createBackupWithLaravel(string $path): array
    {
        $fullPath = storage_path('app/' . $path);
        $directory = dirname($fullPath);
        if (!file_exists($directory)) mkdir($directory, 0755, true);

        $tables = $this->getAllTables();
        $sql = $this->generateSqlHeader();

        foreach ($tables as $table) {
            $sql .= $this->getTableStructure($table);
            $sql .= $this->getTableData($table);
        }

        $sql .= $this->getSqlFooter();

        if (Storage::disk($this->backupDisk)->put($path, $sql)) {
            return [
                'success' => true,
                'message' => 'Respaldo creado con Laravel',
                'path' => $path,
                'size' => Storage::disk($this->backupDisk)->size($path)
            ];
        }

        Log::error('No se pudo guardar el respaldo', [
            'path' => $fullPath,
            'disk' => $this->backupDisk,
            'sql_length' => strlen($sql),
        ]);

        throw new \Exception('No se pudo guardar el archivo de respaldo');
    }

    /**
     * Obtener todas las tablas según el driver.
     *
     * @return array
     */
    protected function getAllTables(): array
    {
        $driver = config('database.default');
        $connection = config("database.connections.{$driver}");

        switch ($driver) {
            case 'mysql':
                return DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = ?", [$connection['database']]);
            case 'pgsql':
                return DB::select("SELECT tablename AS table_name FROM pg_tables WHERE schemaname = ?", [$connection['schema'] ?? 'public']);
            case 'sqlite':
                return DB::select("SELECT name AS table_name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'");
            default:
                throw new \Exception("Driver no soportado: {$driver}");
        }
    }

    /**
     * Generar estructura SQL de una tabla.
     *
     * @param object $table
     * @return string
     */
    protected function getTableStructure($table): string
    {
        $tableName = $table->table_name ?? $table->TABLE_NAME;
        $driver = config('database.default');

        if ($driver === 'sqlite') {
            $row = DB::select("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ?", [$tableName])[0];
            return "\n-- Estructura de tabla `{$tableName}`\n" . $row->sql . ";\n\n";
        }

        $row = DB::select("SHOW CREATE TABLE `{$tableName}`")[0];
        return "\n-- Estructura de tabla `{$tableName}`\n" . $row->{'Create Table'} . ";\n\n";
    }

    /**
     * Generar datos SQL de una tabla.
     *
     * @param object $table
     * @return string
     */
    protected function getTableData($table): string
    {
        $tableName = $table->table_name ?? $table->TABLE_NAME;
        $rows = DB::table($tableName)->get()->toArray();

        if (empty($rows)) return '';

        $columns = '`' . implode('`, `', array_keys((array)$rows[0])) . '`';
        $sql = "-- Datos de tabla `{$tableName}`\nINSERT INTO `{$tableName}` ({$columns}) VALUES\n";

        $values = [];
        foreach ($rows as $row) {
            $vals = array_map(function ($value) {
                if ($value === null) return 'NULL';
                if (is_numeric($value)) return $value;
                return "'" . addslashes((string)$value) . "'";
            }, (array)$row);
            $values[] = '(' . implode(', ', $vals) . ')';
        }

        return $sql . implode(",\n", $values) . ";\n\n";
    }

    /**
     * Encabezado SQL según driver.
     *
     * @return string
     */
    protected function generateSqlHeader(): string
    {
        $driver = config('database.default');
        $dbName = config("database.connections.{$driver}.database");

        switch ($driver) {
            case 'mysql':
                return "-- MySQL Backup\n-- DB: {$dbName}\n-- Fecha: " . now() . "\n\nSET FOREIGN_KEY_CHECKS=0;\nSTART TRANSACTION;\n";
            case 'pgsql':
                return "-- PostgreSQL Backup\n-- DB: {$dbName}\n-- Fecha: " . now() . "\n\nBEGIN;\n";
            case 'sqlite':
                return "-- SQLite Backup\n-- DB: " . basename($dbName) . "\n-- Fecha: " . now() . "\n\nPRAGMA foreign_keys=OFF;\nBEGIN TRANSACTION;\n";
            default:
                return "-- Backup\n-- Fecha: " . now() . "\n";
        }
    }

    /**
     * Pie de archivo SQL.
     *
     * @return string
     */
    protected function getSqlFooter(): string
    {
        $driver = config('database.default');
        switch ($driver) {
            case 'mysql':
                return "\nSET FOREIGN_KEY_CHECKS=1;\nCOMMIT;\n";
            case 'pgsql':
                return "\nCOMMIT;\n";
            case 'sqlite':
                return "\nCOMMIT;\n";
            default:
                return "\nCOMMIT;\n";
        }
    }

    /**
     * Comprimir un archivo SQL en ZIP.
     *
     * @param string $filePath Ruta relativa
     * @return array
     */
    public function compressBackup(string $filePath): array
    {
        $storagePath = storage_path('app/' . $filePath);
        $zipPath = str_replace('.sql', '.zip', $storagePath);

        if (!file_exists($storagePath)) {
            return ['success' => false, 'message' => "Archivo no encontrado: {$storagePath}"];
        }

        $zip = new ZipArchive();
        $result = $zip->open($zipPath, ZipArchive::CREATE);

        if ($result !== true) {
            $errors = [
                ZipArchive::ER_EXISTS => 'Ya existe',
                ZipArchive::ER_INCONS => 'Inconsistencia',
                ZipArchive::ER_INVAL => 'Inválido',
                ZipArchive::ER_MEMORY => 'Sin memoria',
                ZipArchive::ER_NOENT => 'No encontrado',
                ZipArchive::ER_OPEN => 'No se pudo abrir',
            ];
            return ['success' => false, 'message' => $errors[$result] ?? "Error ZIP: {$result}"];
        }

        if (!$zip->addFile($storagePath, basename($storagePath))) {
            $zip->close();
            @unlink($zipPath);
            return ['success' => false, 'message' => 'No se pudo agregar al ZIP'];
        }

        $zip->close();

        if (!file_exists($zipPath)) {
            return ['success' => false, 'message' => 'ZIP no creado'];
        }

        unlink($storagePath);
        $relativePath = str_replace(storage_path('app/'), '', $zipPath);

        return [
            'success' => true,
            'message' => 'Comprimido OK',
            'path' => $relativePath,
            'size' => filesize($zipPath)
        ];
    }

    /**
     * Listar todos los respaldos disponibles.
     *
     * @return array
     */
    public function listBackups(): array
    {
        $files = Storage::disk($this->backupDisk)->files($this->backupPath);
        $backups = [];

        foreach ($files as $file) {
            if (!preg_match('/\.(sql|zip)$/', $file)) continue;
            if (!Storage::disk($this->backupDisk)->exists($file)) continue;

            try {
                $size = Storage::disk($this->backupDisk)->size($file);
                $mtime = Storage::disk($this->backupDisk)->lastModified($file);

                $backups[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => $size,
                    'size_human' => $this->formatBytes($size),
                    'created_at' => Carbon::createFromTimestamp($mtime)->format('Y-m-d H:i:s'),
                    'type' => pathinfo($file, PATHINFO_EXTENSION)
                ];
            } catch (\Exception $e) {
                Log::error("Error leyendo respaldo: {$file}", ['exception' => $e]);
            }
        }

        usort($backups, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
        return $backups;
    }

    /**
     * Verificar si un respaldo existe.
     *
     * @param string $filename Nombre del archivo
     * @return bool
     */
    public function backupExists(string $filename): bool
    {
        $path = $this->backupPath . $filename;
        return Storage::disk($this->backupDisk)->exists($path);
    }

    /**
     * Obtener la ruta física del archivo de respaldo.
     *
     * @param string $filename Nombre del archivo
     * @return string Ruta completa
     */
    public function getBackupPath(string $filename): string
    {
        return storage_path('app/' . $this->backupPath . $filename);
    }

    /**
     * Eliminar un archivo de respaldo.
     *
     * @param string $filename Nombre del archivo
     * @return array
     */
    public function deleteBackup(string $filename): array
    {
        $path = $this->backupPath . $filename;

        if (!Storage::disk($this->backupDisk)->exists($path)) {
            return ['success' => false, 'message' => 'El archivo no existe.'];
        }

        $size = Storage::disk($this->backupDisk)->size($path);
        if (Storage::disk($this->backupDisk)->delete($path)) {
            return [
                'success' => true,
                'message' => 'Respaldo eliminado.',
                'freed_space' => $size
            ];
        }

        return ['success' => false, 'message' => 'No se pudo eliminar el archivo.'];
    }

    /**
     * Restaurar la base de datos desde un respaldo.
     *
     * @param string $filename Nombre del archivo
     * @return array
     */
    public function restoreBackup(string $filename): array
    {
        DB::beginTransaction();
        try {
            $path = $this->backupPath . $filename;
            if (!Storage::disk($this->backupDisk)->exists($path)) {
                throw new \Exception('Archivo no existe');
            }

            $storagePath = storage_path('app/' . $path);
            $sqlContent = '';

            if (pathinfo($filename, PATHINFO_EXTENSION) === 'zip') {
                $zip = new ZipArchive();
                if ($zip->open($storagePath) === true) {
                    $tempDir = storage_path('app/temp_restore/');
                    if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

                    $zip->extractTo($tempDir);
                    $zip->close();

                    $sqlFiles = glob($tempDir . '*.sql');
                    if (empty($sqlFiles)) throw new \Exception('No hay SQL en el ZIP');
                    $sqlContent = file_get_contents($sqlFiles[0]);

                    array_map('unlink', glob($tempDir . '*'));
                    rmdir($tempDir);
                } else {
                    throw new \Exception('No se pudo abrir ZIP');
                }
            } else {
                $sqlContent = Storage::disk($this->backupDisk)->get($path);
            }

            DB::unprepared($sqlContent);
            DB::commit();
            return ['success' => true, 'message' => 'Base de datos restaurada.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Limpiar respaldos más antiguos que X días.
     *
     * @param int $daysOld Días de antigüedad
     * @return array
     */
    public function cleanOldBackups(int $daysOld): array
    {
        $cutoff = now()->subDays($daysOld);
        $files = Storage::disk($this->backupDisk)->files($this->backupPath);
        $deletedCount = 0;
        $freedSpace = 0;

        foreach ($files as $file) {
            try {
                $mtime = Storage::disk($this->backupDisk)->lastModified($file);
                if (Carbon::createFromTimestamp($mtime)->lt($cutoff)) {
                    $freedSpace += Storage::disk($this->backupDisk)->size($file);
                    Storage::disk($this->backupDisk)->delete($file);
                    $deletedCount++;
                }
            } catch (\Exception $e) {
                Log::warning("No se pudo eliminar respaldo: {$file}", ['exception' => $e]);
            }
        }

        return [
            'success' => true,
            'deleted_count' => $deletedCount,
            'freed_space' => $freedSpace,
        ];
    }

    /**
     * Verificar integridad de un respaldo.
     *
     * @param string $filename Nombre del archivo
     * @return array
     */
    public function verifyBackup(string $filename): array
    {
        $path = $this->backupPath . $filename;

        if (!$this->backupExists($filename)) {
            return [
                'success' => false,
                'message' => 'El archivo de respaldo no existe.'
            ];
        }

        $fullPath = $this->getBackupPath($filename);
        $size = filesize($fullPath);

        if ($size === 0) {
            return [
                'success' => false,
                'message' => 'El archivo está vacío.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Archivo válido.',
            'size' => $size,
            'modified' => filemtime($fullPath),
        ];
    }

    /**
     * Obtener información detallada de un respaldo.
     *
     * @param string $filename Nombre del archivo
     * @return array
     */
    public function getBackupInfo(string $filename): array
    {
        $path = $this->backupPath . $filename;

        if (!$this->backupExists($filename)) {
            throw new \Exception('Archivo no encontrado.');
        }

        $fullPath = $this->getBackupPath($filename);
        $size = filesize($fullPath);
        $mtime = filemtime($fullPath);

        return [
            'name' => $filename,
            'path' => $fullPath,
            'size' => $size,
            'size_formatted' => $this->formatBytes($size),
            'created_at' => $mtime,
            'created_at_formatted' => Carbon::createFromTimestamp($mtime)->format('Y-m-d H:i:s'),
            'is_compressed' => in_array(pathinfo($filename, PATHINFO_EXTENSION), ['zip', 'gz']),
            'type' => pathinfo($filename, PATHINFO_EXTENSION),
        ];
    }

    /**
     * Obtener espacio libre en disco.
     *
     * @return int|null Bytes disponibles
     */
    public function getAvailableDiskSpace(): ?int
    {
        $free = @disk_free_space(storage_path('app'));
        return $free ?: null;
    }

    /**
     * Generar nombre de archivo para el respaldo.
     *
     * @param string|null $customName Nombre personalizado
     * @return string
     */
    protected function generateFilename(?string $customName): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        if ($customName) {
            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '', $customName);
            if (preg_match('/\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}/', $customName)) {
                return $safeName . '.sql';
            }
            return $safeName . '_' . $timestamp . '.sql';
        }
        return "backup_{$timestamp}.sql";
    }

    /**
     * Verificar si mysqldump está disponible.
     *
     * @return bool
     */
    public function isMysqldumpAvailable(): bool
    {
        exec('mysqldump --version 2>&1', $output, $returnCode);
        return $returnCode === 0;
    }

    /**
     * Verificar si hay espacio en disco.
     *
     * @param int $minBytes Espacio mínimo requerido
     * @return bool
     */
    protected function hasEnoughDiskSpace(int $minBytes): bool
    {
        $free = @disk_free_space(storage_path('app'));
        return $free && $free > $minBytes;
    }

    /**
     * Formatear tamaño en bytes a unidad legible.
     *
     * @param int $size Tamaño en bytes
     * @param int $precision Decimales
     * @return string
     */
    protected function formatBytes(int $size, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $base = $size > 0 ? log($size, 1024) : 0;
        $unitIndex = min(floor($base), count($units) - 1);
        return round(pow(1024, $base - $unitIndex), $precision) . ' ' . $units[$unitIndex];
    }

    /**
     * Obtener estado general del sistema de respaldos.
     *
     * @return array
     */
    public function getStatus(): array
    {
        $driver = config('database.default');
        $mysqldumpAvailable = $driver === 'mysql' ? $this->isMysqldumpAvailable() : false;
        $zipEnabled = extension_loaded('zip');
        $diskWritable = is_writable(storage_path('app')) || (file_exists(storage_path('app')) && is_dir(storage_path('app')));

        try {
            $backups = $this->listBackups();
            $totalBackups = count($backups);
            $lastBackup = $backups[0] ?? null;
        } catch (\Exception $e) {
            $totalBackups = 0;
            $lastBackup = null;
            Log::warning('Error al listar respaldos en getStatus', ['exception' => $e]);
        }

        try {
            $latestLog = BackupLog::latest('created_at')->first();
        } catch (\Exception $e) {
            $latestLog = null;
            Log::warning('Error al obtener BackupLog en getStatus', ['exception' => $e]);
        }

        return [
            'mysqldump' => $mysqldumpAvailable,
            'zip_enabled' => $zipEnabled,
            'disk_writable' => $diskWritable,
            'total_backups' => $totalBackups,
            'last_backup' => $lastBackup,
            'latest_log' => $latestLog,
        ];
    }
}
