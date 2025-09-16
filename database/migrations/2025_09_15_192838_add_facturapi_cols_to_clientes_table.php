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
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('facturapi_customer_id')->nullable()->index()->after('notas');
            // Defaults para facturación
            $table->char('cfdi_default_use', 3)->nullable()->after('uso_cfdi');   // p.ej. G03
            $table->char('payment_form_default', 2)->nullable()->after('cfdi_default_use'); // p.ej. 03 (Transferencia)
        });
    }
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['facturapi_customer_id', 'cfdi_default_use', 'payment_form_default']);
        });
    }
};
