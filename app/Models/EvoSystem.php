<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvoSystem extends Model
{
    protected $table = 'evo_systems';
    protected $fillable = [
        'name',
        'devices',
        'description',
    ];
}
