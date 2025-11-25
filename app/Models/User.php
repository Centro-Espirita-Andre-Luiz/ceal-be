<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    const TYPE_MANAGER = 'manager';
    const TYPE_HEALER = 'healer';

    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'birth_date',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        // Apenas gestores e magnetizadores acessam o painel admin
        return in_array($this->type, [self::TYPE_MANAGER, self::TYPE_HEALER]);
    }

    public function isManager(): bool
    {
        return $this->type === self::TYPE_MANAGER;
    }

    public function isHealer(): bool
    {
        return $this->type === self::TYPE_HEALER;
    }

    public function healerInfo()
    {
        return $this->hasOne(Healer::class);
    }

    // Relacionamento com pacientes (para gestores que podem gerenciar)
    public function managedPatients()
    {
        return $this->hasMany(Patient::class, 'manager_id');
    }
}