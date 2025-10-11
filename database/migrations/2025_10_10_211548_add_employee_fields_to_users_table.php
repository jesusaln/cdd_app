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
        Schema::table('users', function (Blueprint $table) {
            // Información personal del empleado
            $table->string('telefono')->nullable()->after('email');
            $table->date('fecha_nacimiento')->nullable()->after('telefono');
            $table->string('curp', 18)->nullable()->after('fecha_nacimiento');
            $table->string('rfc', 13)->nullable()->after('curp');
            $table->text('direccion')->nullable()->after('rfc');
            $table->string('nss', 11)->nullable()->after('direccion'); // Número de Seguro Social

            // Información laboral
            $table->string('puesto')->nullable()->after('nss');
            $table->string('departamento')->nullable()->after('puesto');
            $table->date('fecha_contratacion')->nullable()->after('departamento');
            $table->decimal('salario', 10, 2)->nullable()->after('fecha_contratacion');
            $table->string('tipo_contrato')->nullable()->after('salario'); // tiempo completo, medio tiempo, temporal
            $table->string('numero_empleado')->nullable()->unique()->after('tipo_contrato');

            // Información de contacto de emergencia
            $table->string('contacto_emergencia_nombre')->nullable()->after('numero_empleado');
            $table->string('contacto_emergencia_telefono')->nullable()->after('contacto_emergencia_nombre');
            $table->string('contacto_emergencia_parentesco')->nullable()->after('contacto_emergencia_telefono');

            // Información bancaria
            $table->string('banco')->nullable()->after('contacto_emergencia_parentesco');
            $table->string('numero_cuenta')->nullable()->after('banco');
            $table->string('clabe_interbancaria', 18)->nullable()->after('numero_cuenta');

            // Información adicional
            $table->text('observaciones')->nullable()->after('clabe_interbancaria');
            $table->boolean('es_empleado')->default(false)->after('observaciones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar campos de empleado
            $table->dropColumn([
                'telefono',
                'fecha_nacimiento',
                'curp',
                'rfc',
                'direccion',
                'nss',
                'puesto',
                'departamento',
                'fecha_contratacion',
                'salario',
                'tipo_contrato',
                'numero_empleado',
                'contacto_emergencia_nombre',
                'contacto_emergencia_telefono',
                'contacto_emergencia_parentesco',
                'banco',
                'numero_cuenta',
                'clabe_interbancaria',
                'observaciones',
                'es_empleado'
            ]);
        });
    }
};
