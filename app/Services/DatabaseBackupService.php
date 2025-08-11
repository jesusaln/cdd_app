<?php

// app/Services/DatabaseBackupService.php
namespace App\Services;

use App\Models\BackupLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use ZipArchive;




class DatabaseBackupService
{
    protected $backupDisk = 'local';
    protected $backupPath = 'backups/database/';

    public function createBackup($options = [])
    {
        $filename = $this->generateFilename($options['name'] ?? null);
        $fullPath = $this->backupPath . $filename;

        // Datos iniciales del log
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
            // Verificar espacio en disco (estimado 500MB)
            if (!$this->hasEnoughDiskSpace(500 * 1024 * 1024)) {
                throw new \Exception('Espacio en disco insuficiente');
            }

            // Elegir método
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

            // Comprimir si se solicita
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

            // Guardar log
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

    protected function createBackupWithMysqldump($path)
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

    protected function createBackupWithLaravel($path)
    {
        // Asegurar que el directorio exista
        $fullPath = storage_path('app/' . $path);
        $directory = dirname($fullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $tables = $this->getAllTables();
        $sql = $this->generateSqlHeader();

        foreach ($tables as $table) {
            $sql .= $this->getTableStructure($table);
            $sql .= $this->getTableData($table);
        }

        $sql .= $this->getSqlFooter();

        // Intentar guardar
        if (Storage::disk($this->backupDisk)->put($path, $sql)) {
            return [
                'success' => true,
                'message' => 'Respaldo creado con Laravel',
                'path' => $path,
                'size' => Storage::disk($this->backupDisk)->size($path),
                'method' => 'laravel'
            ];
        }

        // Si falla, loguea
        Log::error('No se pudo guardar el respaldo', [
            'path' => $fullPath,
            'disk' => $this->backupDisk,
            'sql_length' => strlen($sql),
        ]);

        throw new \Exception('No se pudo guardar el archivo de respaldo');
    }

    protected function getAllTables()
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

    protected function getTableStructure($table)
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

    protected function getTableData($table)
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

    protected function generateSqlHeader()
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

    protected function getSqlFooter()
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

    public function compressBackup($filePath)
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

    public function listBackups()
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

    public function deleteBackup($filePath)
    {
        try {
            if (Storage::disk($this->backupDisk)->exists($filePath)) {
                Storage::disk($this->backupDisk)->delete($filePath);
                return ['success' => true, 'message' => 'Eliminado'];
            }
            return ['success' => false, 'message' => 'No existe'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function downloadBackup($filePath)
    {
        if (Storage::disk($this->backupDisk)->exists($filePath)) {
            $fullPath = Storage::disk($this->backupDisk)->path($filePath);
            return response()->download($fullPath, basename($filePath), [
                'Content-Type' => 'application/octet-stream'
            ]);
        }
        abort(404, 'Archivo no encontrado');
    }

    public function restoreBackup($filePath)
    {
        try {
            if (!Storage::disk($this->backupDisk)->exists($filePath)) {
                throw new \Exception('Archivo no existe');
            }

            $storagePath = storage_path('app/' . $filePath);
            $sqlContent = '';

            if (pathinfo($filePath, PATHINFO_EXTENSION) === 'zip') {
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
                $sqlContent = Storage::disk($this->backupDisk)->get($filePath);
            }

            DB::unprepared($sqlContent);
            return ['success' => true, 'message' => 'Base de datos restaurada'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function cleanOldBackups($daysOld = 30)
    {
        $cutoff = now()->subDays($daysOld);
        $files = Storage::disk($this->backupDisk)->files($this->backupPath);
        $deleted = 0;

        foreach ($files as $file) {
            try {
                $mtime = Storage::disk($this->backupDisk)->lastModified($file);
                if (Carbon::createFromTimestamp($mtime)->lt($cutoff)) {
                    Storage::disk($this->backupDisk)->delete($file);
                    $deleted++;
                }
            } catch (\Exception $e) {
                Log::warning("No se pudo eliminar respaldo: {$file}");
            }
        }

        return $deleted;
    }

    protected function generateFilename($customName = null)
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $safeName = $customName ? preg_replace('/[^a-zA-Z0-9_-]/', '', $customName) : 'backup';
        return "{$safeName}_{$timestamp}.sql";
    }

    public function isMysqldumpAvailable()
    {
        exec('mysqldump --version 2>&1', $output, $returnCode);
        return $returnCode === 0;
    }

    protected function hasEnoughDiskSpace($minBytes = 100 * 1024 * 1024)
    {
        $free = @disk_free_space(storage_path('app'));
        return $free && $free > $minBytes;
    }

    protected function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $base = $size > 0 ? log($size, 1024) : 0;
        $unitIndex = min(floor($base), count($units) - 1);
        return round(pow(1024, $base - $unitIndex), $precision) . ' ' . $units[$unitIndex];
    }

    public function getStatus()
    {
        // 1. Verificar mysqldump (solo si no es SQLite)
        $driver = config('database.default');
        $mysqldumpAvailable = $driver === 'mysql' ? $this->isMysqldumpAvailable() : false;

        // 2. Verificar extensión zip
        $zipEnabled = extension_loaded('zip');

        // 3. Verificar escritura en disco
        $storagePath = storage_path('app');
        $diskWritable = is_writable($storagePath) || (file_exists($storagePath) && is_dir($storagePath));

        // 4. Listar respaldos
        try {
            $backups = $this->listBackups();
            $totalBackups = count($backups);
            $lastBackup = $backups[0] ?? null;
        } catch (\Exception $e) {
            $totalBackups = 0;
            $lastBackup = null;
            Log::warning('Error al listar respaldos en getStatus', ['exception' => $e]);
        }

        // 5. Último log (con manejo de null)
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
