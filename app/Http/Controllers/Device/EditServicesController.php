<?php

namespace App\Http\Controllers\Device;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class EditServicesController extends Controller
{
    public function __invoke(Request $request, Device $device): View
    {
        $this->authorize('update', $device);
        $this->authorize('viewAny', Service::class);

        return view('device.edit.services', [
            'device' => $device,
            'services' => $device->services()->hasAccess($request->user())->ordered()->get(),
        ]);
    }
}
