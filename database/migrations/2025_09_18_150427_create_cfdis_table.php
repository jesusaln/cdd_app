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
        Schema::create('cfdis', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('venta_id')->nullable()->constrained('ventas'); // CFDI generado desde venta
            $table->foreignId('cfdi_relacionado_id')->nullable()->constrained('cfdis'); // Para sustituciones

            // Tipo de CFDI (I=Ingreso, E=Egreso, T=Traslado, N=Nómina, P=Pagos)
            $table->enum('tipo_comprobante', ['I', 'E', 'T', 'N', 'P'])->default('I');

            // Serie y Folio
            $table->string('serie', 25)->nullable();
            $table->string('folio', 40)->nullable();

            // UUID y datos del timbrado
            $table->uuid('uuid')->nullable()->unique();
            $table->timestamp('fecha_timbrado')->nullable();
            $table->string('no_certificado_sat', 20)->nullable();
            $table->string('no_certificado_cfdi', 20)->nullable();
            $table->text('sello_sat')->nullable();
            $table->text('sello_cfdi')->nullable();
            $table->string('cadena_original', 500)->nullable();

            // Estatus del CFDI
            $table->enum('estatus', [
                'borrador',
                'timbrado',
                'cancelado',
                'vigente',
                'cancelado_con_acuse'
            ])->default('borrador');

            // Fechas
            $table->date('fecha_emision');
            $table->timestamp('fecha_cancelacion')->nullable();

            // Moneda y tipo de cambio
            $table->string('moneda', 3)->default('MXN');
            $table->decimal('tipo_cambio', 6, 4)->nullable();

            // Subtotal, descuentos, impuestos, total
            $table->decimal('subtotal', 12, 2);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('total_impuestos_trasladados', 12, 2)->default(0);
            $table->decimal('total_impuestos_retenidos', 12, 2)->default(0);
            $table->decimal('total', 12, 2);

            // Método y forma de pago
            $table->string('metodo_pago', 3)->nullable(); // c_MetodoPago
            $table->string('forma_pago', 2)->nullable();   // c_FormaPago

            // Condiciones de pago
            $table->string('condiciones_pago', 1000)->nullable();

            // Uso CFDI del receptor
            $table->string('uso_cfdi', 3); // c_UsoCFDI

            // Complementos
            $table->json('complementos')->nullable(); // Para almacenar complementos como Nómina, Pagos, etc.

            // Datos del PAC
            $table->string('pac_rfc', 13)->nullable();
            $table->string('pac_nombre', 255)->nullable();

            // URLs de archivos
            $table->string('xml_url')->nullable();
            $table->string('pdf_url')->nullable();

            // Datos adicionales
            $table->text('observaciones')->nullable();
            $table->json('datos_adicionales')->nullable();

            // Auditoría
            $table->string('creado_por')->nullable();
            $table->string('actualizado_por')->nullable();
            $table->string('cancelado_por')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['cliente_id', 'estatus']);
            $table->index(['uuid']);
            $table->index(['fecha_emision']);
            $table->index(['tipo_comprobante']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cfdis');
    }
};
