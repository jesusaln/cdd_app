<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_razon_social',
        'tipo_persona',
        'tipo_identificacion',
        'identificacion',
        'curp',
        'rfc',
        'regimen_fiscal',
        'uso_cfdi',
        'email',
        'telefono',
        'calle',
        'numero_exterior',
        'numero_interior',
        'colonia',
        'codigo_postal',
        'municipio',
        'estado',
        'pais',
        // WhatsApp fields
        'whatsapp_enabled',
        'whatsapp_business_account_id',
        'whatsapp_phone_number_id',
        'whatsapp_sender_phone',
        'whatsapp_access_token',
        'whatsapp_app_secret',
        'whatsapp_webhook_verify_token',
        'whatsapp_default_language',
        'whatsapp_template_payment_reminder',
    ];

    protected $casts = [
        'whatsapp_access_token' => 'encrypted',
        'whatsapp_app_secret' => 'encrypted',
        'whatsapp_enabled' => 'boolean',
    ];
}
