<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'type',
        'report_date',
        'data',
        'summary',
        'user_id'
    ];

    protected $casts = [
        'data' => 'array',
        'report_date' => 'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}