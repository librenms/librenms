<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DeviceDependencyController extends Controller
{
    public function index(): View
    {
        $this->authorize('device.update');

        return view('device-dependencies.index');
    }

    /**
     * Set the parents of the given devices. No parents removes their dependencies.
     */
    public function update(Request $request): JsonResponse
    {
        $this->authorize('device.update');

        $validated = $request->validate([
            'device_ids' => 'required|array|min:1',
            'device_ids.*' => 'integer|exists:devices,device_id',
            'parent_ids' => 'nullable|array',
            'parent_ids.*' => ['integer', 'exists:devices,device_id', Rule::notIn((array) $request->input('device_ids', []))],
        ], [
            'parent_ids.*.not_in' => __('A device cannot depend on itself'),
        ], [
            'device_ids' => __('child devices'),
            'device_ids.*' => __('child device'),
            'parent_ids.*' => __('parent device'),
        ]);
        $parent_ids = $validated['parent_ids'] ?? [];

        DB::transaction(function () use ($validated, $parent_ids): void {
            Device::whereIntegerInRaw('device_id', $validated['device_ids'])->get()
                ->each(fn (Device $device) => $device->parents()->sync($parent_ids));
        });

        return response()->json([
            'message' => empty($parent_ids)
                ? __('Device dependencies have been removed')
                : __('Device dependencies have been saved'),
        ]);
    }

    /**
     * Remove all children from the given parent devices.
     */
    public function destroy(Request $request): JsonResponse
    {
        $this->authorize('device.update');

        $validated = $request->validate([
            'parent_ids' => 'required|array|min:1',
            'parent_ids.*' => 'integer',
        ], [], [
            'parent_ids' => __('parent devices'),
        ]);

        DB::table('device_relationships')->whereIntegerInRaw('parent_device_id', $validated['parent_ids'])->delete();

        return response()->json([
            'message' => __('Device dependencies have been removed'),
        ]);
    }
}
