<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidosTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id(); // ID del pedido

            // Relación con el cliente (igual que cotizaciones)
            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->onDelete('cascade');

            // (Opcional) vínculo a la cotización de origen
            $table->foreignId('cotizacion_id')
                ->nullable()
                ->constrained('cotizaciones')
                ->nullOnDelete();

            // Quién creó / actualizó / eliminó (auditoría, igual que cotizaciones)
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('deleted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Identificador legible/externo (único) para el pedido
            $table->string('numero_pedido', 30)->unique();

            // Campos de cálculo (mismo patrón que cotizaciones)
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->decimal('descuento_general', 10, 2)->default(0);
            $table->decimal('descuento_items', 10, 2)->default(0);
            $table->decimal('iva', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->default(0);

            // Estado del pedido (equivalente a "estado" en cotizaciones, pero con estados de pedido)
            // Sugeridos: pendiente, confirmado, en_proceso, enviado, entregado, cancelado
            $table->string('estado')->default('pendiente');

            // Campos propios de pedido (entrega / logística / pago) — todos opcionales
            $table->date('fecha_pedido')->nullable();
            $table->date('fecha_entrega_estimada')->nullable();
            $table->timestamp('fecha_entregado_at')->nullable();

            $table->string('tipo_entrega', 20)->nullable();   // domicilio | sucursal | paqueteria
            $table->text('direccion_entrega')->nullable();

            $table->string('empresa_envio', 100)->nullable();
            $table->string('numero_guia', 100)->nullable();
            $table->decimal('costo_envio', 10, 2)->default(0);

            $table->string('metodo_pago', 50)->nullable();     // transferencia, tarjeta, efectivo, etc.
            $table->string('referencia_pago', 100)->nullable();
            $table->timestamp('pagado_at')->nullable();

            // Notas
            $table->text('notas')->nullable();

            // Soft delete + timestamps (idéntico a tu cotizaciones)
            $table->softDeletes();
            $table->timestamps();

            // Índices útiles
            $table->index(['cliente_id', 'estado']);
            $table->index(['fecha_pedido']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
}
