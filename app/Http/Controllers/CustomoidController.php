<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomoidRequest;
use App\Models\Customoid;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SnmpQuery;

class CustomoidController extends Controller
{
    /**
     * Store a newly created custom OID in storage.
     */
    public function store(CustomoidRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $deviceId = $validated['device_id'];
        $name = strip_tags((string) $validated['name']);

        if (Customoid::where('customoid_descr', $name)->where('device_id', $deviceId)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => "OID named <i>" . e($name) . "</i> on this device already exists",
            ]);
        }

        $customoid = new Customoid();
        $customoid->fill([
            'device_id' => $deviceId,
            'customoid_descr' => $name,
            'customoid_oid' => strip_tags((string) $validated['oid']),
            'customoid_datatype' => strip_tags((string) $validated['datatype']),
            'customoid_unit' => strip_tags((string) ($validated['unit'] ?? '')),
            'customoid_divisor' => is_numeric($validated['divisor'] ?? null) ? $validated['divisor'] : 1,
            'customoid_multiplier' => is_numeric($validated['multiplier'] ?? null) ? $validated['multiplier'] : 1,
            'customoid_limit' => $validated['limit'] ?? null,
            'customoid_limit_warn' => $validated['limit_warn'] ?? null,
            'customoid_limit_low' => $validated['limit_low'] ?? null,
            'customoid_limit_low_warn' => $validated['limit_low_warn'] ?? null,
            'customoid_alert' => ($validated['alerts'] ?? null) === 'on' ? 1 : 0,
            'customoid_passed' => ($validated['passed'] ?? null) === 'on' ? 1 : 0,
            'user_func' => $validated['user_func'] ?? null,
        ]);

        if ($customoid->save()) {
            return response()->json([
                'status' => 'ok',
                'message' => "Added OID: <i>" . e($name) . "</i>",
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => "Failed to add OID: <i>" . e($name) . "</i>",
        ]);
    }

    /**
     * Display the specified custom OID.
     */
    public function show(Customoid $customoid): JsonResponse
    {
        $this->authorize('view', $customoid);

        return response()->json([
            'name' => $customoid->customoid_descr,
            'oid' => $customoid->customoid_oid,
            'datatype' => $customoid->customoid_datatype,
            'unit' => $customoid->customoid_unit,
            'divisor' => $customoid->customoid_divisor,
            'multiplier' => $customoid->customoid_multiplier,
            'limit' => $customoid->customoid_limit,
            'limit_warn' => $customoid->customoid_limit_warn,
            'limit_low' => $customoid->customoid_limit_low,
            'limit_low_warn' => $customoid->customoid_limit_low_warn,
            'alerts' => (bool) $customoid->customoid_alert,
            'cpassed' => (bool) $customoid->customoid_passed,
            'passed' => $customoid->customoid_passed ? 'on' : '',
            'user_func' => $customoid->user_func,
        ]);
    }

    /**
     * Update the specified custom OID in storage.
     */
    public function update(CustomoidRequest $request, Customoid $customoid): JsonResponse
    {
        $validated = $request->validated();
        $name = strip_tags((string) $validated['name']);

        if (Customoid::where('customoid_descr', $name)
            ->where('device_id', $customoid->device_id)
            ->where('customoid_id', '!=', $customoid->customoid_id)
            ->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => "OID named <i>" . e($name) . "</i> on this device already exists",
            ]);
        }

        $customoid->fill([
            'customoid_descr' => $name,
            'customoid_oid' => strip_tags((string) $validated['oid']),
            'customoid_datatype' => strip_tags((string) ($validated['datatype'] ?? $customoid->customoid_datatype)),
            'customoid_unit' => strip_tags((string) ($validated['unit'] ?? '')),
            'customoid_divisor' => is_numeric($validated['divisor'] ?? null) ? $validated['divisor'] : 1,
            'customoid_multiplier' => is_numeric($validated['multiplier'] ?? null) ? $validated['multiplier'] : 1,
            'customoid_limit' => $validated['limit'] ?? null,
            'customoid_limit_warn' => $validated['limit_warn'] ?? null,
            'customoid_limit_low' => $validated['limit_low'] ?? null,
            'customoid_limit_low_warn' => $validated['limit_low_warn'] ?? null,
            'customoid_alert' => ($validated['alerts'] ?? null) === 'on' ? 1 : 0,
            'customoid_passed' => ($validated['passed'] ?? null) === 'on' ? 1 : 0,
            'user_func' => $validated['user_func'] ?? null,
        ]);

        if ($customoid->save()) {
            return response()->json([
                'status' => 'ok',
                'message' => "Edited OID: <i>" . e($name) . "</i>",
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => "Failed to edit OID <i>" . e($name) . "</i>",
        ]);
    }

    /**
     * Remove the specified custom OID from storage.
     */
    public function destroy(Customoid $customoid): JsonResponse
    {
        $this->authorize('delete', $customoid);

        if ($customoid->delete()) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Custom OID has been deleted.',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'ERROR: Custom OID has not been deleted.',
        ]);
    }

    /**
     * Test the SNMP connection for custom OID.
     */
    public function test(Request $request, ?Customoid $customoid = null): JsonResponse
    {
        if ($customoid) {
            $this->authorize('update', $customoid);
            $device = $customoid->device;
        } else {
            $this->authorize('create', Customoid::class);
            $deviceId = $request->input('device_id');
            $device = Device::findOrFail($deviceId);
        }

        $request->validate([
            'name' => 'required|string|max:200',
            'oid' => 'required|string|max:255',
            'unit' => 'nullable|string|max:10',
        ]);

        $name = strip_tags((string) $request->input('name'));
        $oid = strip_tags((string) $request->input('oid'));
        $unit = strip_tags((string) $request->input('unit'));

        $response = SnmpQuery::device($device)->get($oid);

        if (! $response->isValid()) {
            return response()->json([
                'status' => 'error',
                'message' => "Invalid data in SNMP reply: " . e($response->getErrorMessage() ?: 'Timeout or bad response'),
            ]);
        }

        $rawdata = $response->value();

        $oidValue = null;
        if (is_numeric($rawdata)) {
            $oidValue = $rawdata;
        } elseif (
            ! empty($unit) &&
            Str::contains($rawdata, $unit, ignoreCase: true) &&
            is_numeric(trim(str_replace($unit, '', $rawdata)))
        ) {
            $oidValue = trim(str_replace($unit, '', $rawdata));
        } else {
            $floatVal = sprintf('%.2f', $rawdata);
            if (is_numeric($floatVal)) {
                $oidValue = $floatVal;
            }
        }

        if (is_numeric($oidValue)) {
            if ($customoid) {
                $customoid->customoid_passed = 1;
                $customoid->save();
            }

            return response()->json([
                'status' => 'ok',
                'message' => "Test successful for <i>" . e($name) . "</i>, value " . e($rawdata) . " received",
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => "Invalid data in SNMP reply, value " . e($rawdata) . " received",
        ]);
    }
}
