<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkSnmpRequest;
use App\Models\DeviceGroup;
use App\Services\BulkSnmpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BulkSnmpController extends Controller
{
    public function __construct(protected BulkSnmpService $service)
    {
        $this->middleware('auth');
    }

    /**
     * Render the bulk SNMP edit form for a Device Group.
     */
    public function show(int $deviceGroup): View
    {
        // Non-admins get a friendly explanation instead of a raw 403 page.
        if (! Auth::user()?->hasRole('admin')) {
            return view('device-group.bulk-snmp-denied');
        }

        $group = DeviceGroup::findOrFail($deviceGroup);

        return view('device-group.bulk-snmp', [
            'group' => $group,
            'deviceCount' => $group->devices()->count(),
            'authAlgos' => BulkSnmpService::AUTH_ALGOS,
            'privAlgos' => BulkSnmpService::PRIV_ALGOS,
            'securityLevels' => BulkSnmpService::SECURITY_LEVELS,
        ]);
    }

    /**
     * Test the supplied credentials against all devices in the group.
     * Authorization handled by BulkSnmpRequest::authorize().
     */
    public function test(BulkSnmpRequest $request, int $deviceGroup): JsonResponse
    {
        $group = DeviceGroup::findOrFail($deviceGroup);
        $devices = $this->getDevices($group, (bool) $request->input('skip_down'));

        $fields = $this->service->buildUpdateFields($request->validated());
        $results = $this->service->testCredentials($devices, $fields);

        $passed = collect($results)->where('success', true)->count();

        return response()->json([
            'status' => 'ok',
            'total' => $devices->count(),
            'passed' => $passed,
            'failed' => $devices->count() - $passed,
            'results' => $results,
        ]);
    }

    /**
     * Apply the supplied credentials to all devices in the group.
     * Authorization handled by BulkSnmpRequest::authorize().
     */
    public function apply(BulkSnmpRequest $request, int $deviceGroup): JsonResponse
    {
        $group = DeviceGroup::findOrFail($deviceGroup);
        $devices = $this->getDevices($group, (bool) $request->input('skip_down'));

        $fields = $this->service->buildUpdateFields($request->validated());
        $result = $this->service->applyCredentials($devices, $fields);

        return response()->json([
            'status' => 'ok',
            'total' => $devices->count(),
            'success_count' => count($result['success']),
            'failed_count' => count($result['failed']),
            'success' => $result['success'],
            'failed' => $result['failed'],
        ]);
    }

    /**
     * Fetch the device collection for a group, optionally skipping down devices.
     *
     * @return Collection<int,\App\Models\Device>
     */
    protected function getDevices(DeviceGroup $group, bool $skipDown): Collection
    {
        $query = $group->devices();

        if ($skipDown) {
            $query = $query->where('status', 1);
        }

        return $query->get();
    }
}
