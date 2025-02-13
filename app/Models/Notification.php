<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $fillable = [
        'message',
        'temperature',
        'smoke',
        'movement',
        'face_status',
        'mac_address',
    ];
}
