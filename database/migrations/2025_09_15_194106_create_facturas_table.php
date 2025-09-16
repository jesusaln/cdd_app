<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();

            // Relación con clientes
            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Datos principales de Facturapi / CFDI
            $table->string('facturapi_invoice_id')->index();
            $table->string('uuid')->nullable()->unique();
            $table->string('serie')->nullable();
            $table->unsignedInteger('folio')->nullable();

            // Estatus de la factura (valid, canceled, pending, etc.)
            $table->string('estatus')->default('valid');

            // Totales
            $table->decimal('total', 12, 2)->default(0);

            // Archivos generados
            $table->string('pdf_url')->nullable();
            $table->string('xml_url')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
