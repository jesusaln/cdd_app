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
        Schema::create('cfdi_conceptos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cfdi_id')->constrained('cfdis')->onDelete('cascade');

            // Datos del concepto
            $table->string('clave_prod_serv', 8); // c_ClaveProdServ
            $table->string('no_identificacion', 100)->nullable();
            $table->decimal('cantidad', 8, 2);
            $table->string('clave_unidad', 3); // c_ClaveUnidad
            $table->string('unidad', 20)->nullable();
            $table->text('descripcion');
            $table->decimal('valor_unitario', 12, 2);
            $table->decimal('importe', 12, 2);
            $table->decimal('descuento', 12, 2)->default(0);

            // Impuestos del concepto
            $table->json('impuestos')->nullable(); // Array de impuestos trasladados y retenidos

            // Información aduanera (si aplica)
            $table->string('numero_pedimento', 21)->nullable();

            // Cuenta predial (si aplica)
            $table->string('cuenta_predial', 150)->nullable();

            // Complemento concepto (para conceptos específicos)
            $table->json('complemento')->nullable();

            // Relación con producto/servicio original (opcional)
            $table->foreignId('producto_id')->nullable()->constrained('productos');
            $table->foreignId('servicio_id')->nullable()->constrained('servicios');

            $table->timestamps();

            // Índices
            $table->index(['cfdi_id']);
            $table->index(['clave_prod_serv']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cfdi_conceptos');
    }
};
