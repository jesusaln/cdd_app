<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE clientes ALTER COLUMN calle DROP NOT NULL");
            DB::statement("ALTER TABLE clientes ALTER COLUMN numero_exterior DROP NOT NULL");
            DB::statement("ALTER TABLE clientes ALTER COLUMN colonia DROP NOT NULL");
            DB::statement("ALTER TABLE clientes ALTER COLUMN codigo_postal DROP NOT NULL");
            DB::statement("ALTER TABLE clientes ALTER COLUMN municipio DROP NOT NULL");
            DB::statement("ALTER TABLE clientes ALTER COLUMN estado DROP NOT NULL");
        } elseif ($driver === 'mysql') {
            // Cambios equivalentes para MySQL sin requerir doctrine/dbal
            DB::statement("ALTER TABLE clientes MODIFY calle VARCHAR(150) NULL");
            DB::statement("ALTER TABLE clientes MODIFY numero_exterior VARCHAR(30) NULL");
            DB::statement("ALTER TABLE clientes MODIFY numero_interior VARCHAR(30) NULL");
            DB::statement("ALTER TABLE clientes MODIFY colonia VARCHAR(150) NULL");
            DB::statement("ALTER TABLE clientes MODIFY codigo_postal CHAR(5) NULL");
            DB::statement("ALTER TABLE clientes MODIFY municipio VARCHAR(120) NULL");
            DB::statement("ALTER TABLE clientes MODIFY estado VARCHAR(100) NULL");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE clientes ALTER COLUMN calle SET NOT NULL");
            DB::statement("ALTER TABLE clientes ALTER COLUMN numero_exterior SET NOT NULL");
            DB::statement("ALTER TABLE clientes ALTER COLUMN colonia SET NOT NULL");
            DB::statement("ALTER TABLE clientes ALTER COLUMN codigo_postal SET NOT NULL");
            DB::statement("ALTER TABLE clientes ALTER COLUMN municipio SET NOT NULL");
            DB::statement("ALTER TABLE clientes ALTER COLUMN estado SET NOT NULL");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE clientes MODIFY calle VARCHAR(150) NOT NULL");
            DB::statement("ALTER TABLE clientes MODIFY numero_exterior VARCHAR(30) NOT NULL");
            DB::statement("ALTER TABLE clientes MODIFY colonia VARCHAR(150) NOT NULL");
            DB::statement("ALTER TABLE clientes MODIFY codigo_postal CHAR(5) NOT NULL");
            DB::statement("ALTER TABLE clientes MODIFY municipio VARCHAR(120) NOT NULL");
            DB::statement("ALTER TABLE clientes MODIFY estado VARCHAR(100) NOT NULL");
        }
    }
};

