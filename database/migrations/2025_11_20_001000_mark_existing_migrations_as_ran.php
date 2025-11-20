<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Este migration marca como ejecutadas todas las migraciones presentes en disco
     * (útil cuando la base ya tiene las tablas pero la tabla migrations está vacía).
     */
    public function up(): void
    {
        // Asegurar que exista la tabla migrations
        if (!Schema::hasTable('migrations')) {
            Schema::create('migrations', function (Blueprint $table) {
                $table->id();
                $table->string('migration');
                $table->unsignedInteger('batch');
            });
        }

        $currentBatch = (int) DB::table('migrations')->max('batch');
        $batch = $currentBatch + 1;

        // Todas las migraciones en disco
        $allMigrations = collect(File::files(database_path('migrations')))
            ->map(fn ($file) => $file->getFilenameWithoutExtension())
            ->values();

        // Las que ya están registradas
        $already = DB::table('migrations')->pluck('migration')->all();
        $already = array_map('strval', $already);

        $pending = $allMigrations->reject(fn ($name) => in_array($name, $already, true));

        if ($pending->isNotEmpty()) {
            DB::table('migrations')->insert(
                $pending->map(fn ($name) => ['migration' => $name, 'batch' => $batch])->all()
            );
        }
    }

    public function down(): void
    {
        // No quitar registros de migrations; dejar la tabla intacta
    }
};

