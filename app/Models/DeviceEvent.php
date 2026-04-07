<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'type',
        'timestamp',
    ];

    /**
     * Get the device associated with this event.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
