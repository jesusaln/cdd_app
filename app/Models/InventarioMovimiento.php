<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioMovimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'producto_id',
        'tipo',
        'cantidad',
        'motivo',
        'referencia',
        'user_id',
        'metadatos',
    ];

    protected $casts = [
        'metadatos' => 'array',
    ];

    /** @return BelongsTo<Producto, InventarioMovimiento> */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    /** @return BelongsTo<User, InventarioMovimiento> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
