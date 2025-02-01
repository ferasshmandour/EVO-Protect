<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFeedback extends Model
{
    protected $table = 'user_feedbacks';
    protected $fillable = [
        'user_id',
        'feedback',
        'is_deleted'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
