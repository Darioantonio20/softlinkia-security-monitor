<?php

namespace App\Livewire\Forms;

use App\Models\Device;
use App\Models\AuditLog;
use App\Jobs\ProcessAuditLog;
use Livewire\Attributes\Rule;
use Livewire\Form;

class DeviceForm extends Form
{
    public ?Device $device;

    #[Rule('required|min:3', as: 'Nombre')]
    public $name = '';

    #[Rule('required', as: 'Tipo')]
    public $type = '';

    #[Rule('required', as: 'Ubicación')]
    public $location = '';

    #[Rule('required|exists:users,id', as: 'Cliente')]
    public $client_id = '';

    #[Rule('required|in:activo,inactivo,alerta', as: 'Estado')]
    public $status = 'activo';

    public function setDevice(Device $device)
    {
        $this->device = $device;
        $this->name = $device->name;
        $this->type = $device->type;
        $this->location = $device->location;
        $this->client_id = $device->client_id;
        $this->status = $device->status;
    }

    public function store()
    {
        $this->validate();

        $device = Device::create($this->all());

        ProcessAuditLog::dispatch([
            'user_id' => auth()->id(),
            'action' => 'DEVICE_CREATED',
            'description' => "Se registró nuevo equipo: {$device->name}",
            'ip_address' => request()->ip(),
        ]);

        $this->reset();
    }

    public function update()
    {
        $this->validate();

        $this->device->update($this->all());

        ProcessAuditLog::dispatch([
            'user_id' => auth()->id(),
            'action' => 'DEVICE_UPDATED',
            'description' => "Se actualizó información técnica de: {$this->name}",
            'ip_address' => request()->ip(),
        ]);
    }
}
