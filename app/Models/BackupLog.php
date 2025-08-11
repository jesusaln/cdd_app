<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BackupLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'filename',
        'path',
        'size',
        'type',
        'method',
        'status',
        'message',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public const UPDATED_AT = null;

    public function getSizeHumanAttribute()
    {
        if (!$this->size) return 'N/A';

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = log($this->size, 1024);
        return round(pow(1024, $base - floor($base)), 2) . ' ' . $units[floor($base)];
    }
}
