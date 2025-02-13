<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemValue extends Model
{
    protected $table = 'system_values';
    protected $fillable = [
        'facility_id',
        'system_id',
        'temperature',
        'smoke',
        'movement',
        'face_status',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function system(): BelongsTo
    {
        return $this->belongsTo(EvoSystem::class);
    }
}
