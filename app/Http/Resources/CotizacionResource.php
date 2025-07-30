<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CotizacionResource extends JsonResource
{
    public function toArray($request)
    {
        $items = collect($this->productos)->map(function ($producto) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'tipo' => 'producto',
                'pivot' => [
                    'cantidad' => $producto->pivot->cantidad,
                    'precio' => $producto->pivot->precio,
                ],
            ];
        })->merge(collect($this->servicios)->map(function ($servicio) {
            return [
                'id' => $servicio->id,
                'nombre' => $servicio->nombre,
                'tipo' => 'servicio',
                'pivot' => [
                    'cantidad' => $servicio->pivot->cantidad,
                    'precio' => $servicio->pivot->precio,
                ],
            ];
        }));

        return [
            'id' => $this->id,
            'cliente' => $this->cliente,
            'productos' => $items,
            'total' => $this->total,
            'fecha' => $this->created_at,
            'estado' => $this->estado->value,
            'estado_label' => $this->estado->label(),
            'estado_color' => $this->estado->color(),
            'puede_convertir' => $this->estado->puedeConvertir(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'cliente_nombre' => $this->cliente->nombre_razon_social ?? '',
            'cliente_email' => $this->cliente->email ?? '',
        ];
    }
}
