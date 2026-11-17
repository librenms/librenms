<?php

namespace App\Http\Controllers\Device;

use App\Facades\LibrenmsConfig;
use App\Models\Application;
use App\Models\Device;
use App\Models\Eventlog;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LibreNMS\Enum\Severity;
use LibreNMS\Util\StringHelpers;

class EditApplicationsController
{
    use AuthorizesRequests;

    public function index(Device $device): View
    {
        $this->authorize('update', $device);

        $enabled = $device->applications()->pluck('discovered', 'app_type');
        $autoDiscovery = $device->getAttrib('poll_applications') || LibrenmsConfig::getOsSetting($device->os, 'poller_modules.applications');

        return view('device.edit.apps', [
            'device' => $device,
            'canUpdate' => auth()->user()->can('update', Application::class),
            'applications' => collect($this->types())->map(fn (string $type) => [
                'type' => $type,
                'name' => StringHelpers::niceCase($type),
                'enabled' => $enabled->has($type),
                'discovered' => $autoDiscovery && $enabled->get($type),
            ])->all(),
        ]);
    }

    public function update(Request $request, Device $device, string $type): JsonResponse
    {
        $this->authorize('view', $device);
        $this->authorize('update', Application::class);
        abort_unless(in_array($type, $this->types()), 404);

        $enable = $request->validate(['enabled' => 'required|boolean'])['enabled'];
        $application = $device->applications()->withTrashed()->firstOrNew(['app_type' => $type]);
        $username = $request->user()->username;

        if ($enable) {
            $application->deleted_at = null;
            $application->save();
            Eventlog::log("Application enabled by user $username: $application", $device->device_id, 'application', Severity::Ok);

            return response()->json(['message' => __('Application enabled')]);
        }

        if ($application->exists && ! $application->trashed()) {
            $application->delete();
            Eventlog::log("Application disabled by user $username: $application", $device->device_id, 'application', Severity::Notice);
        }

        return response()->json(['message' => __('Application disabled')]);
    }

    /**
     * Application types that have a poller
     *
     * @return string[]
     */
    private function types(): array
    {
        return array_map(fn ($file) => basename($file, '.inc.php'), glob(base_path('includes/polling/applications/*.inc.php')) ?: []);
    }
}
