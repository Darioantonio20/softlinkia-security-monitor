<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceEvent;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Simular un evento externo vía API.
     */
    public function simulate(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,id',
            'type' => 'required|string',
        ]);

        $event = DeviceEvent::create([
            'device_id' => $request->device_id,
            'type' => $request->type,
            'timestamp' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Evento procesado correctamente.',
            'event' => $event,
        ]);
    }
}
