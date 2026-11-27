<?php

namespace App\Http\Controllers\Device;

use App\Models\Customoid;
use App\Models\Device;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EditCustomoidsController
{
    use AuthorizesRequests;

    public function index(Device $device): View
    {
        $this->authorize('update', $device);

        return view('device.edit.customoids', [
            'device' => $device,
            'customoids' => $device->customoids()->orderBy('customoid_descr')->get(),
            'canCreate' => auth()->user()->can('create', Customoid::class),
        ]);
    }
}
