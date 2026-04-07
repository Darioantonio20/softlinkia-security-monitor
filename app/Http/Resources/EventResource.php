<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'device_id' => $this->device_id,
            'device_name' => $this->device->name,
            'event_type' => strtoupper($this->type),
            'timestamp' => $this->timestamp,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'status' => 'processed',
        ];
    }
}
