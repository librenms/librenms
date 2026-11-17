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
        $nodeData = $oxidized->nodes();
        $nodes = collect($nodeData ?? []);
        $devices = Device::hasAccess($request->user())
            ->whereIn('hostname', $nodes->pluck('name'))
            ->get()
            ->keyBy('hostname');

        return view('oxidized', [
            'reachable' => $nodeData !== null && $oxidized->lastError() === null,
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

        $nodeNames = array_column($results, 'node');
        $devices = Device::hasAccess($request->user())
            ->whereIn('hostname', $nodeNames)->get()->keyBy('hostname');
        $knownHostnames = Device::whereIn('hostname', $nodeNames)->pluck('hostname')->flip();
        $results = array_filter($results, fn (array $result) => ! $knownHostnames->has($result['node'] ?? '')
            || $devices->has($result['node'] ?? ''));

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

        $reloaded = $oxidized->reloadNodes();
        if ($reloaded === null) {
            return response()->json(['message' => __('Oxidized node list reload is disabled')], 409);
        }
        if (! $reloaded) {
            return response()->json(['message' => __('Oxidized node list could not be reloaded')], 503);
        }

        return response()->json(['message' => __('Oxidized node list was reloaded')]);
    }

    private function localTime(string $time): string
    {
        if ($time === '') {
            return '';
        }

        try {
            return Carbon::parse($time, 'UTC')->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s T');
        } catch (InvalidFormatException) {
            return $time;
        }
    }
}
