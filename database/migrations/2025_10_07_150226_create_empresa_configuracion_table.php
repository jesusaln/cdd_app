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
        Schema::create('empresa_configuracion', function (Blueprint $table) {
            $table->id();

            // Información básica de la empresa
            $table->string('nombre_empresa');
            $table->string('rfc', 13)->nullable();
            $table->string('razon_social')->nullable();
            $table->text('direccion')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('sitio_web')->nullable();

            // Información fiscal
            $table->string('codigo_postal', 10)->nullable();
            $table->string('ciudad')->nullable();
            $table->string('estado')->nullable();
            $table->string('pais')->default('México');

            // Configuración del sistema
            $table->string('logo_path')->nullable(); // Ruta al archivo del logo
            $table->string('favicon_path')->nullable(); // Ruta al favicon
            $table->text('descripcion_empresa')->nullable();
            $table->string('color_principal')->default('#3B82F6'); // Color primario del sistema
            $table->string('color_secundario')->default('#1E40AF'); // Color secundario del sistema

            // Configuración de documentos
            $table->text('pie_pagina_facturas')->nullable(); // Texto que aparece al pie de facturas
            $table->text('pie_pagina_cotizaciones')->nullable(); // Texto que aparece al pie de cotizaciones
            $table->text('terminos_condiciones')->nullable(); // Términos y condiciones generales
            $table->text('politica_privacidad')->nullable(); // Política de privacidad

            // Configuración financiera
            $table->decimal('iva_porcentaje', 5, 2)->default(16.00); // Porcentaje de IVA
            $table->string('moneda', 3)->default('MXN'); // Moneda del sistema
            $table->string('formato_numeros')->default('es-ES'); // Formato de números

            // Configuración del sistema
            $table->boolean('mantenimiento')->default(false); // Modo mantenimiento
            $table->text('mensaje_mantenimiento')->nullable(); // Mensaje cuando está en mantenimiento
            $table->boolean('registro_usuarios')->default(true); // Permitir registro de usuarios
            $table->boolean('notificaciones_email')->default(true); // Activar notificaciones por email

            // Configuración de reportes
            $table->string('logo_reportes')->nullable(); // Logo específico para reportes
            $table->string('formato_fecha')->default('d/m/Y'); // Formato de fecha
            $table->string('formato_hora')->default('H:i:s'); // Formato de hora

            // Configuración de backups
            $table->boolean('backup_automatico')->default(true); // Realizar backups automáticos
            $table->integer('frecuencia_backup')->default(7); // Días entre backups
            $table->integer('retencion_backups')->default(30); // Días de retención

            // Configuración de seguridad
            $table->integer('intentos_login')->default(5); // Número de intentos de login permitidos
            $table->integer('tiempo_bloqueo')->default(15); // Minutos de bloqueo después de intentos fallidos
            $table->boolean('requerir_2fa')->default(false); // Requerir autenticación de dos factores

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresa_configuracion');
    }
};
