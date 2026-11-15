<?php

namespace App\Http\Controllers;

use App\ApiClients\Oxidized;
use App\Models\Device;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OxidizedController extends Controller
{
    public function index(Request $request, Oxidized $oxidized): View
    {
        $nodes = collect($oxidized->nodes() ?? []);
        $devices = Device::hasAccess($request->user())
            ->whereIn('hostname', $nodes->pluck('name'))
            ->get()
            ->keyBy('hostname');

        return view('oxidized', [
            'reachable' => $oxidized->lastError() === null,
            'nodes' => $nodes
                ->filter(fn (array $node) => $devices->has($node['name'] ?? ''))
                ->map(fn (array $node) => [
                    'device' => $devices->get($node['name']),
                    'name' => $node['name'],
                    'status' => $node['status'] ?? '',
                    'time' => $this->localTime($node['time'] ?? ''),
                    'model' => $node['model'] ?? '',
                    'group' => $node['group'] ?? '',
                ]),
        ]);
    }

    public function search(Request $request, Oxidized $oxidized): JsonResponse
    {
        $this->authorize('oxidized.search');

        $validated = $request->validate(['search' => 'required|string']);

        $results = $oxidized->searchConfigs($validated['search']);
        if ($results === null) {
            return response()->json(['message' => __('Oxidized is not reachable')], 503);
        }

        // link LibreNMS devices, and hide nodes of devices the user may not see
        $devices = Device::whereIn('hostname', array_column($results, 'node'))->get()->keyBy('hostname');
        $results = array_filter($results, fn (array $result) => ! $devices->has($result['node'] ?? '')
            || $request->user()->can('view', $devices->get($result['node'])));

        return response()->json([
            'results' => array_map(function (array $result) use ($devices) {
                $device = $devices->get($result['node'] ?? '');

                return $device
                    ? ['name' => $device->display, 'url' => route('device', ['device' => $device->device_id, 'tab' => 'config'])]
                    : ['name' => $result['full_name'] ?? $result['node'] ?? '', 'url' => null];
            }, array_values($results)),
        ]);
    }

    public function reload(Oxidized $oxidized): JsonResponse
    {
        $this->authorize('config-backup.refresh');

        $oxidized->reloadNodes();

        return response()->json(['message' => __('Oxidized node list was reloaded')]);
    }

    private function localTime(string $time): string
    {
        try {
            return Carbon::parse($time, 'UTC')->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s T');
        } catch (InvalidFormatException) {
            return $time;
        }
    }
}
