<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('herramientas', function (Blueprint $table) {
            $table->string('estado')->default('disponible')->after('tecnico_id');
            $table->integer('vida_util_meses')->nullable()->after('estado');
            $table->date('fecha_ultimo_mantenimiento')->nullable()->after('vida_util_meses');
            $table->decimal('costo_reemplazo', 10, 2)->nullable()->after('fecha_ultimo_mantenimiento');
            $table->string('categoria')->nullable()->after('costo_reemplazo');
            $table->text('descripcion')->nullable()->after('categoria');
            $table->boolean('requiere_mantenimiento')->default(false)->after('descripcion');
            $table->integer('dias_para_mantenimiento')->nullable()->after('requiere_mantenimiento');
            $table->timestamp('fecha_asignacion')->nullable()->after('dias_para_mantenimiento');
            $table->timestamp('fecha_recepcion')->nullable()->after('fecha_asignacion');
            $table->foreignId('categoria_id')->nullable()->after('categoria')->constrained('categoria_herramientas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('herramientas', function (Blueprint $table) {
            $table->dropForeign(['categoria_id']);
            $table->dropColumn([
                'estado',
                'vida_util_meses',
                'fecha_ultimo_mantenimiento',
                'costo_reemplazo',
                'categoria',
                'descripcion',
                'requiere_mantenimiento',
                'dias_para_mantenimiento',
                'fecha_asignacion',
                'fecha_recepcion',
                'categoria_id'
            ]);
        });
    }
};
