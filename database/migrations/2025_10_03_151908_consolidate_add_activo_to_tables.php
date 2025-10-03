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
        // Add activo to proveedores
        Schema::table('proveedores', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('pais');
        });

        // Add fields to tecnicos
        Schema::table('tecnicos', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('direccion');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->after('activo');
            $table->decimal('margen_venta_productos', 5, 2)->default(0)->after('user_id'); // porcentaje
            $table->decimal('margen_venta_servicios', 5, 2)->default(0)->after('margen_venta_productos'); // porcentaje
            $table->decimal('comision_instalacion', 8, 2)->default(0)->after('margen_venta_servicios'); // monto fijo
        });

        // Add activo to users
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('profile_photo_path');
        });

        // Add activo to citas
        Schema::table('citas', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('foto_identificacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove activo from proveedores
        Schema::table('proveedores', function (Blueprint $table) {
            $table->dropColumn('activo');
        });

        // Remove fields from tecnicos
        Schema::table('tecnicos', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['activo', 'user_id', 'margen_venta_productos', 'margen_venta_servicios', 'comision_instalacion']);
        });

        // Remove activo from users
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('activo');
        });

        // Remove activo from citas
        Schema::table('citas', function (Blueprint $table) {
            $table->dropColumn('activo');
        });
    }
};
