<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Herramienta extends Model
{
    use HasFactory;
    protected $fillable = ['nombre', 'numero_serie', 'foto', 'tecnico_id'];

    public function tecnico()
    {
        return $this->belongsTo(Tecnico::class, 'tecnico_id');
    }
}
