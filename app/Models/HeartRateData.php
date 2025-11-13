<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeartRateData extends Model
{
    protected $fillable = [
        'user_id',
        'bpm',
        'recorded_at',
    ];
}
