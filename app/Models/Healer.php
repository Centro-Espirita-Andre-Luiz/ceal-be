<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Healer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'specialty',
        'license_number',
        'bio',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scope para carregar o usuário automaticamente
    public function scopeWithUser($query)
    {
        return $query->with('user');
    }

    // Accessor para o nome do usuário
    public function getUserNameAttribute()
    {
        return $this->user->name ?? 'N/A';
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'healer_id');
    }

    public function patients()
    {
        return $this->hasMany(Patient::class, 'preferred_healer_id');
    }
}
