<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFeedback extends Model
{
    protected $table = 'facility_systems';
    protected $fillable = [
        'user_id',
        'feedback',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
