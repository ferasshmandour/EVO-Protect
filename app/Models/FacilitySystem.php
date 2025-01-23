<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilitySystem extends Model
{
    protected $table = 'facility_systems';
    protected $fillable = [
        'facility_id',
        'system_id',
    ];
}
