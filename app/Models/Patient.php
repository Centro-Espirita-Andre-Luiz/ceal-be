<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', // Nome do paciente (não está mais vinculado a User)
        'email',
        'birth_date',
        'phone',
        'emergency_contact',
        'health_notes',
        'preferred_healer_id',
        'access_code',
        'manager_id', // ID do gestor que cadastrou
    ];

    protected $casts = [
        'birth_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function preferredHealer(): BelongsTo
    {
        return $this->belongsTo(Healer::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function absences(): HasMany
    {
        return $this->hasMany(Absence::class);
    }
}
