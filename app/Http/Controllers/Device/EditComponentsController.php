<?php

namespace App\Http\Controllers\Device;

use App\Models\Component;
use App\Models\Device;
use App\Models\Eventlog;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LibreNMS\Enum\Severity;

class EditComponentsController
{
    use AuthorizesRequests;

    public function index(Device $device): View
    {
        $this->authorize('update', $device);

        return view('device.edit.components', [
            'device' => $device,
            'canUpdate' => auth()->user()->can('update', Component::class),
            'components' => $device->components()
                ->orderBy('type')->orderBy('label')
                ->get(['id', 'type', 'label', 'status', 'disabled', 'ignore'])
                ->map(fn (Component $component) => [
                    'id' => $component->id,
                    'type' => $component->type,
                    'label' => $component->label,
                    'status' => $component->status,
                    'disabled' => (bool) $component->disabled,
                    'ignore' => (bool) $component->ignore,
                ]),
        ]);
    }

    public function update(Request $request, Device $device): JsonResponse
    {
        $this->authorize('view', $device);
        $this->authorize('update', Component::class);

        $settings = $request->validate([
            'components' => 'required|array',
            'components.*.disabled' => 'required|boolean',
            'components.*.ignore' => 'required|boolean',
        ])['components'];

        $updated = 0;
        foreach ($device->components()->whereIn('id', array_keys($settings))->get() as $component) {
            $component->fill($settings[$component->id]);
            if ($component->isDirty()) {
                $changes = collect($component->getDirty())->map(fn ($value, $key) => "$key => $value")->implode(', ');
                $component->save();
                Eventlog::log("Component $component->id has been modified: $changes", $device->device_id, 'component', Severity::Notice, $component->id);
                $updated++;
            }
        }

        return response()->json([
            'message' => trans_choice('{0} No changes to save|{1} :count component updated|[2,*] :count components updated', $updated),
        ]);
    }
}
