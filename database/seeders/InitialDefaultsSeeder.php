<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InitialDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        // Seed almacén principal
        if (Schema::hasTable('almacenes')) {
            $exists = DB::table('almacenes')->where('nombre', 'Almacén Principal')->exists();
            if (!$exists) {
                $data = [ 'nombre' => 'Almacén Principal' ];
                if (Schema::hasColumn('almacenes', 'descripcion')) { $data['descripcion'] = 'Almacén por defecto para operaciones iniciales'; }
                if (Schema::hasColumn('almacenes', 'ubicacion')) { $data['ubicacion'] = 'Principal'; }
                if (Schema::hasColumn('almacenes', 'direccion')) { $data['direccion'] = null; }
                if (Schema::hasColumn('almacenes', 'telefono')) { $data['telefono'] = null; }
                if (Schema::hasColumn('almacenes', 'responsable')) { $data['responsable'] = null; }
                if (Schema::hasColumn('almacenes', 'estado')) { $data['estado'] = 'activo'; }
                if (Schema::hasColumn('almacenes', 'created_at')) { $data['created_at'] = now(); }
                if (Schema::hasColumn('almacenes', 'updated_at')) { $data['updated_at'] = now(); }
                DB::table('almacenes')->insert($data);
            }
        }

        // Seed categoría general
        if (Schema::hasTable('categorias')) {
            $exists = DB::table('categorias')->where('nombre', 'General')->exists();
            if (!$exists) {
                $data = [ 'nombre' => 'General' ];
                if (Schema::hasColumn('categorias', 'descripcion')) { $data['descripcion'] = 'Categoría por defecto'; }
                if (Schema::hasColumn('categorias', 'estado')) { $data['estado'] = 'activo'; }
                if (Schema::hasColumn('categorias', 'created_at')) { $data['created_at'] = now(); }
                if (Schema::hasColumn('categorias', 'updated_at')) { $data['updated_at'] = now(); }
                DB::table('categorias')->insert($data);
            }
        }

        // Seed marca genérica (si existe la tabla)
        if (Schema::hasTable('marcas')) {
            $exists = DB::table('marcas')->where('nombre', 'Genérica')->exists();
            if (!$exists) {
                $data = [ 'nombre' => 'Genérica' ];
                if (Schema::hasColumn('marcas', 'descripcion')) { $data['descripcion'] = 'Marca por defecto'; }
                if (Schema::hasColumn('marcas', 'activo')) { $data['activo'] = true; }
                if (Schema::hasColumn('marcas', 'created_at')) { $data['created_at'] = now(); }
                if (Schema::hasColumn('marcas', 'updated_at')) { $data['updated_at'] = now(); }
                DB::table('marcas')->insert($data);
            }
        }
    }
}
