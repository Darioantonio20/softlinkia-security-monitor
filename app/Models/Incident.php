<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Incident extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'device_id',
        'type',
        'status',
        'description',
        'assigned_user_id',
    ];

    /**
     * Get the device associated with this incident.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Get the user assigned to this incident.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * Get the history records for this incident.
     */
    public function history(): HasMany
    {
        return $this->hasMany(IncidentHistory::class);
    }
}
