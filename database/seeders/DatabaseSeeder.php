<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Llama a otros seeders, como RolesAndPermissionsSeeder
        $this->call(RolesAndPermissionsSeeder::class);
    }
}
