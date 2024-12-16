<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionalInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id', 
        'ethnicity',
        'addictions',
        'is_accompanied',
        'benefits',
        'is_lactating',
        'has_disability',
        'reason_for_accommodation', 
        'has_religion', 
        'religion', 
        'has_chronic_disease', 
        'chronic_disease', 
        'education_level',
        'nationality', 
        'room_id',     
        'bed_id',
        'stay_duration',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    // Relacionamento com Room
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // Relacionamento com Bed
    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }
    
}
