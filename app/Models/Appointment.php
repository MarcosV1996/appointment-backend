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
<<<<<<< HEAD
    // Relacionamento um-para-um com AdditionalInfo
    // Em app/Models/Appointment.php

=======
>>>>>>> Initial commit - Laravel backend
=======
    // Relacionamento um-para-um com AdditionalInfo
>>>>>>> Atualização de Testes
    public function additionalInfo()
    {
        return $this->hasOne(AdditionalInfo::class, 'appointment_id');
    }
    
<<<<<<< HEAD
<<<<<<< HEAD
    
    // No modelo AdditionalInfo
=======
>>>>>>> Initial commit - Laravel backend
=======
    // No modelo AdditionalInfo
>>>>>>> Atualização de Testes
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }
}