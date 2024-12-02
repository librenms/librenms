<?php

namespace App\Http\Controllers\Device\Tabs;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Config;
use LibreNMS\Util\Module;

class ModuleController extends Controller
{
    public function update(Device $device, string $module, Request $request): JsonResponse
    {
        Gate::authorize('update', $device);

        $this->validate($request, [
            'discovery' => 'in:true,false,clear',
            'polling' => 'in:true,false,clear',
        ]);

        if ($request->has('discovery')) {
            $discovery = $request->get('discovery');
            if ($discovery == 'clear') {
                $device->forgetAttrib('discover_' . $module);
            } else {
                $device->setAttrib('discover_' . $module, $discovery == 'true' ? 1 : 0);
            }
        }

        if ($request->has('polling')) {
            $polling = $request->get('polling');
            if ($polling == 'clear') {
                $device->forgetAttrib('poll_' . $module);
            } else {
                $device->setAttrib('poll_' . $module, $polling == 'true' ? 1 : 0);
            }
        }

        // return the module status
        return response()->json([
            'discovery' => (bool) $device->getAttrib('discover_' . $module, Config::getCombined($device->os, 'discovery_modules')[$module] ?? false),
            'polling' => (bool) $device->getAttrib('poll_' . $module, Config::getCombined($device->os, 'poller_modules')[$module] ?? false),
        ]);
    }

    public function delete(Device $device, string $module): JsonResponse
    {
        Gate::authorize('delete', $device);

        $deleted = Module::fromName($module)->cleanup($device);

        return response()->json([
            'deleted' => $deleted,
        ]);
    }
}
