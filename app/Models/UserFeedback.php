<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFeedback extends Model
{
    protected $table = 'facility_systems';
    protected $fillable = [
        'user_id',
        'feedback',
    ];
}
