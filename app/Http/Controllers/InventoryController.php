<?php

namespace App\Http\Controllers;

use App\Models\EntPhysical;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InventoryController extends Controller
{
    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', EntPhysical::class);

        $this->validate($request, [
            'device' => 'nullable|int',
            'descr' => 'nullable|string',
            'model' => 'nullable|string',
            'serial' => 'nullable|string',
        ]);

        $device = \App\Models\Device::hasAccess($request->user())
            ->select(['device_id', 'hostname', 'ip', 'sysName', 'display'])
            ->firstWhere('device_id', $request->input('device'));

        $model_filter = ['field' => 'model'];
        $device_selected = '';
        if ($device) {
            $device_selected = ['id' => $device->device_id, 'text' => $device->displayName()];
            $model_filter['device_id'] = $device->device_id;
        }

        return view('inventory', [
            'device_selected' => $device_selected,
            'filter' => [
                'device' => $device?->device_id,
                'descr' => $request->input('descr'),
                'model' => $request->input('model'),
                'serial' => $request->input('serial'),
            ],
            'model_filter' => $model_filter,
            'show_purge' => Gate::allows('purge', EntPhysical::class) && EntPhysical::whereDoesntHave('device')->exists(),
        ]);
    }

    public function purge()
    {
        $this->authorize('purge', EntPhysical::class);

        EntPhysical::whereDoesntHave('device')->delete();

        return redirect()->back();
    }
}
