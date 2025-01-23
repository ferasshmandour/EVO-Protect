<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    protected $table = 'maintenance_requests';
    protected $fillable = [
        'user_id',
        'facility_id',
        'cause_of_maintenance',
    ];
}
