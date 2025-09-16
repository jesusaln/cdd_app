<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCotizacionesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id(); // ID de la cotización

            // Relación con el cliente
            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->onDelete('cascade');

            // Quién creó / quién actualizó por última vez (nullable y nullOnDelete)
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Quién eliminó (para auditoría con SoftDeletes)
            $table->foreignId('deleted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Identificador legible/externo (único)
            $table->string('numero_cotizacion', 30)->unique();

            // Campos de cálculo
            $table->decimal('subtotal', 10, 2)->nullable();          // Suma de ítems
            $table->decimal('descuento_general', 10, 2)->default(0); // Monto del descuento general
            $table->decimal('descuento_items', 10, 2)->default(0);   // Suma de descuentos por ítem
            $table->decimal('iva', 10, 2)->nullable();               // IVA calculado
            $table->decimal('total', 10, 2)->default(0);             // Total final

            // Estado (compatible con enum EstadoCotizacion)
            $table->string('estado')->default('pendiente');

            // Notas adicionales
            $table->text('notas')->nullable();

            // Soft delete + timestamps
            $table->softDeletes(); // agrega deleted_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
}
