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
        Schema::create('rentas', function (Blueprint $table) {
            $table->id();

            // Información básica de la renta
            $table->string('numero_contrato')->unique(); // Número único del contrato (ej: R-2024-001)

            // Relación con cliente (asumiendo que ya tienes tabla clientes)
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');

            // Fechas del contrato
            $table->date('fecha_inicio'); // Cuándo inicia la renta
            $table->date('fecha_fin'); // Cuándo termina la renta
            $table->date('fecha_firma')->nullable(); // Cuándo se firmó el contrato

            // Información económica
            $table->decimal('monto_mensual', 10, 2); // Monto total mensual
            $table->decimal('deposito_garantia', 10, 2)->default(0); // Depósito en garantía
            $table->integer('dia_pago')->default(1); // Día del mes para pagar (1-31)

            // Estado del contrato
            $table->enum('estado', [
                'borrador',           // En proceso de creación
                'activo',            // Contrato vigente y al corriente
                'proximo_vencimiento', // Próximo a vencer (30 días)
                'vencido',           // Pasó la fecha de fin
                'moroso',            // Con pagos atrasados
                'suspendido',        // Suspendido temporalmente
                'cancelado',         // Cancelado antes de tiempo
                'finalizado'         // Terminado naturalmente
            ])->default('borrador');

            // Información de pagos
            $table->date('ultimo_pago')->nullable(); // Fecha del último pago
            $table->decimal('saldo_pendiente', 10, 2)->default(0); // Saldo pendiente
            $table->integer('meses_mora')->default(0); // Meses en mora

            // Condiciones del contrato
            $table->text('condiciones_especiales')->nullable(); // Condiciones específicas
            $table->boolean('renovacion_automatica')->default(false); // Si se renueva automáticamente
            $table->integer('meses_duracion')->default(12); // Duración en meses

            // Información de entrega/devolución
            $table->json('lugar_instalacion')->nullable(); // Dirección donde se instala
            $table->date('fecha_instalacion')->nullable(); // Cuándo se instaló
            $table->date('fecha_retiro')->nullable(); // Cuándo se retiró (si aplica)
            $table->text('notas_instalacion')->nullable(); // Notas de la instalación
            $table->text('notas_retiro')->nullable(); // Notas del retiro

            // Información del responsable
            $table->string('responsable_entrega')->nullable(); // Quién entregó el equipo
            $table->string('responsable_recibe')->nullable(); // Quién recibió el equipo

            // Seguimiento y notas
            $table->text('observaciones')->nullable(); // Observaciones generales
            $table->json('historial_cambios')->nullable(); // Historial de cambios importantes

            // Información adicional
            $table->string('forma_pago')->nullable(); // Efectivo, transferencia, etc.
            $table->string('referencia_pago')->nullable(); // Número de cuenta, etc.

            // Metadatos
            $table->timestamps();
            $table->softDeletes(); // Para eliminación suave

            // Índices para optimización
            $table->index(['cliente_id', 'estado']);
            $table->index(['fecha_inicio', 'fecha_fin']);
            $table->index('estado');
            $table->index('ultimo_pago');
            $table->index('fecha_fin'); // Para encontrar próximos vencimientos
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentas');
    }
};
