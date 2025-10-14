<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Support\Facades\Storage; // Asegúrate de agregar esta línea


class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles, HasFactory, HasProfilePhoto, HasTeams, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'telefono',
        'fecha_nacimiento',
        'curp',
        'rfc',
        'direccion',
        'nss',
        'puesto',
        'departamento',
        'fecha_contratacion',
        'salario',
        'tipo_contrato',
        'numero_empleado',
        'contacto_emergencia_nombre',
        'contacto_emergencia_telefono',
        'contacto_emergencia_parentesco',
        'banco',
        'numero_cuenta',
        'clabe_interbancaria',
        'observaciones',
        'es_empleado',
        'activo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'fecha_nacimiento' => 'date',
            'fecha_contratacion' => 'date',
            'salario' => 'decimal:2',
            'es_empleado' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    /**
     * Enviar la notificación de restablecimiento de contraseña.
     *
     * @param string $token
     * @return void
     */


    /**
     * Accessor para la propiedad 'profile_photo_url'.
     */
    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo_path
            ? Storage::url($this->profile_photo_path)
            : asset('images/default-profile.png'); // Foto por defecto si no tiene una foto
    }

    // Relación con técnico
    public function tecnico()
    {
        return $this->hasOne(Tecnico::class);
    }

    // Ventas realizadas por este usuario
    public function ventas()
    {
        return $this->morphMany(Venta::class, 'vendedor');
    }

    // Vacaciones del empleado
    public function vacaciones()
    {
        return $this->hasMany(Vacacion::class);
    }

    // Vacaciones aprobadas del empleado
    public function vacacionesAprobadas()
    {
        return $this->hasMany(Vacacion::class)->where('estado', 'aprobada');
    }

    // Registro anual de vacaciones
    public function registroVacaciones()
    {
        return $this->hasMany(RegistroVacaciones::class);
    }

    // Registro de vacaciones del año actual
    public function registroVacacionesActual()
    {
        return $this->hasOne(RegistroVacaciones::class)->where('anio', now()->year);
    }

    // Ganancia total de todas las ventas realizadas por este usuario
    public function getGananciaTotalAttribute()
    {
        return $this->ventas->sum('ganancia_total');
    }

    // Métodos para empleados
    public function getNombreCompletoAttribute()
    {
        return $this->name;
    }

    public function getEdadAttribute()
    {
        if (!$this->fecha_nacimiento) {
            return null;
        }
        return now()->diffInYears($this->fecha_nacimiento);
    }

    public function getAntiguedadAttribute()
    {
        if (!$this->fecha_contratacion) {
            return null;
        }
        return now()->diffInYears($this->fecha_contratacion);
    }

    // Scope para empleados activos
    public function scopeEmpleados($query)
    {
        return $query->where('es_empleado', true);
    }

    // Scope para empleados activos
    public function scopeEmpleadosActivos($query)
    {
        return $query->where('es_empleado', true)->where('activo', true);
    }

}
