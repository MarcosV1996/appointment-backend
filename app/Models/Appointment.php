<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'last_name',
        'cpf',
        'mother_name',
        'date',
        'time',
        'state',
        'city',
        'phone',
        'observation',
        'foreign_country',
        'no_phone',
        'photo',
        'gender',
        'birth_date',
        'arrival_date',
        'isHidden',
    ];

<<<<<<< HEAD
    // Relacionamento um-para-um com AdditionalInfo
    // Em app/Models/Appointment.php

=======
>>>>>>> Initial commit - Laravel backend
    public function additionalInfo()
    {
        return $this->hasOne(AdditionalInfo::class, 'appointment_id');
    }
    
<<<<<<< HEAD
    
    // No modelo AdditionalInfo
=======
>>>>>>> Initial commit - Laravel backend
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }
}