<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $targets = [
            ['table' => 'pedido_items', 'column' => 'pedible_type'],
            ['table' => 'venta_items', 'column' => 'ventable_type'],
            ['table' => 'compra_items', 'column' => 'comprable_type'],
            ['table' => 'cotizacion_items', 'column' => 'cotizable_type'],
        ];

        foreach ($targets as $t) {
            if (!Schema::hasTable($t['table'])) {
                continue;
            }

            $col = $t['column'];
            // Obtener tipos distintos para normalizar
            $types = DB::table($t['table'])
                ->select($col . ' as type')
                ->distinct()
                ->pluck('type')
                ->filter()
                ->all();

            foreach ($types as $type) {
                $normalized = $this->normalizeType((string) $type);
                if ($normalized !== $type) {
                    DB::table($t['table'])
                        ->where($col, '=', $type)
                        ->update([$col => $normalized]);
                }
            }
        }
    }

    public function down(): void
    {
        $targets = [
            ['table' => 'pedido_items', 'column' => 'pedible_type'],
            ['table' => 'venta_items', 'column' => 'ventable_type'],
            ['table' => 'compra_items', 'column' => 'comprable_type'],
            ['table' => 'cotizacion_items', 'column' => 'cotizable_type'],
        ];

        foreach ($targets as $t) {
            if (!Schema::hasTable($t['table'])) {
                continue;
            }

            $col = $t['column'];

            // Revertir aliases a FQCN por compatibilidad si fuera necesario
            DB::table($t['table'])
                ->where($col, '=', 'producto')
                ->update([$col => 'App\\Models\\Producto']);

            DB::table($t['table'])
                ->where($col, '=', 'servicio')
                ->update([$col => 'App\\Models\\Servicio']);
        }
    }

    private function normalizeType(string $type): string
    {
        $trim = trim($type);
        $lower = strtolower($trim);

        if ($lower === 'producto' || stripos($trim, 'App\\Models\\Producto') !== false) {
            return 'producto';
        }
        if ($lower === 'servicio' || stripos($trim, 'App\\Models\\Servicio') !== false) {
            return 'servicio';
        }

        // También normaliza variantes con mayúsculas/minúsculas o espacios
        if (preg_match('/producto/i', $trim)) {
            return 'producto';
        }
        if (preg_match('/servicio/i', $trim)) {
            return 'servicio';
        }

        return $trim;
    }
};

