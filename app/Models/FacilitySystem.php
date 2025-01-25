<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacilitySystem extends Model
{
    protected $table = 'facility_systems';
    protected $fillable = [
        'facility_id',
        'system_id',
        'status',
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
