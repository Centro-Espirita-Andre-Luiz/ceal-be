<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'healer_id',
        'appointment_id',
        'checkin_time',
        'start_time',
        'end_time',
        'status',
        'queue_number',
        'notes',
        'symptoms_status',
        'pre_notes',
        'wellness_score',
        'procedure_report',
        'magnetizer_notes'
    ];

    protected $casts = [
        'checkin_time' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function healer(): BelongsTo
    {
        return $this->belongsTo(Healer::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
