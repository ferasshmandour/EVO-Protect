<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JoinRequest extends Model
{
    protected $table = 'join_requests';
    protected $fillable = [
        'user_id',
        'status',
    ];
}
