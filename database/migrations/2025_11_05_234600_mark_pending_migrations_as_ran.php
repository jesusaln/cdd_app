<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Deshabilitado intencionalmente: esta migración marcaba migraciones como "ran"
        // sin ejecutarlas, causando inconsistencias graves. Se deja vacía para que
        // pueda ejecutarse sin efectos y quedar registrada como aplicada.
        return;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No hay forma de revertir esto de manera segura
    }
};
