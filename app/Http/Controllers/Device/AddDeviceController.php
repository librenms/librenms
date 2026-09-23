<?php

namespace App\Http\Controllers\Device;

use App\Actions\Device\ValidateDeviceAndCreate;
use App\Facades\LibrenmsConfig;
use App\Http\Interfaces\ToastInterface;
use App\Http\Requests\StoreDeviceRequest;
use App\Models\Device;
use App\Models\PollerGroup;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Exceptions\HostUnreachableException;
use LibreNMS\Polling\Method\PollingMethodRegistry;
use LibreNMS\Polling\Secrets\Definitions\SecretDefinition;

class AddDeviceController
{
    use AuthorizesRequests;

    public function __construct(
        private readonly PollingMethodRegistry $pollingMethods,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('create', Device::class);

        $availableMethods = collect($this->pollingMethods->types())->map(function (PollingMethodType $type): array {
            $method = $this->pollingMethods->require($type);
            $secretDefinition = SecretDefinition::for($method->secretType());
            $schemaFields = $secretDefinition ? $secretDefinition->buildSchemaFields(dataVar: "methods['" . $type->value . "'].formData") : [];

            return [
                'type' => $type->value,
                'label' => __('poller.methods.' . $type->value),
                'icon' => $method->icon(),
                'schema_fields' => $schemaFields,
                'schema_defaults' => $secretDefinition?->schemaDefaults() ?? [],
                'settings_fields' => $method->buildSchemaFields(dataVar: "methods['" . $type->value . "'].settingsData"),
                'settings_defaults' => $method->schemaDefaults(),
                'settings_form_defaults' => $method->formDefaults(),
            ];
        })->all();

        $defaultPollerGroup = LibrenmsConfig::get('default_poller_group', 0);
        $pollerGroups = PollerGroup::orderBy('group_name')->get();

        $oldActiveMethods = old('active_methods', [PollingMethodType::Icmp->value, PollingMethodType::Snmp->value]);
        $defaultDisplayTemplate = LibrenmsConfig::get('device_display_default', '{{ $hostname }}');

        $addDeviceConfig = [
            'hostname' => old('hostname', ''),
            'display_template' => old('display_template', ''),
            'default_display_template' => $defaultDisplayTemplate,
            'poller_group' => old('poller_group', $defaultPollerGroup),
            'sysName' => old('sysName', ''),
            'hardware' => old('hardware', ''),
            'os' => old('os', ''),
            'active_tab' => old('active_tab', 'snmp'),
            'active_methods' => $oldActiveMethods,
            'methods' => collect($availableMethods)->mapWithKeys(function (array $method) {
                $type = $method['type'];

                return [$type => [
                    'validate' => old("polling_methods.{$type}.validate") !== null ? (bool) old("polling_methods.{$type}.validate") : true,
                    'affects_availability' => old("polling_methods.{$type}.affects_availability") !== null ? (bool) old("polling_methods.{$type}.affects_availability") : in_array($type, ['snmp', 'icmp']),
                    'credential_mode' => old("polling_methods.{$type}.credential_mode", 'default'),
                    'secret_id' => old("polling_methods.{$type}.secret_id", ''),
                    'description' => old("polling_methods.{$type}.description", ''),
                    'formData' => old("polling_methods.{$type}.secret_data", $method['schema_defaults'] ?? []),
                    'settingsData' => old("polling_methods.{$type}.settings", $method['settings_form_defaults'] ?? []),
                ]];
            })->all(),
            'all_types' => collect($availableMethods)->map(fn ($m) => ['type' => $m['type'], 'label' => $m['label']])->values()->all(),
            'store_url' => route('device.add.store'),
            'csrf_token' => csrf_token(),
        ];

        return view('device.add', [
            'availableMethods' => $availableMethods,
            'default_poller_group' => $defaultPollerGroup,
            'poller_groups' => $pollerGroups,
            'oldActiveMethods' => $oldActiveMethods,
            'default_display_template' => $defaultDisplayTemplate,
            'add_device_config' => $addDeviceConfig,
        ]);
    }

    public function store(StoreDeviceRequest $request, ToastInterface $toast): JsonResponse
    {
        $this->authorize('create', Device::class);

        $validated = $request->validated();

        $device = new Device;
        $device->hostname = $validated['hostname'];
        $device->display_template = $validated['display_template'] ?? null;
        $device->poller_group = $validated['poller_group'] ?? LibrenmsConfig::get('default_poller_group', 0);

        if (! empty($validated['sysName'])) {
            $device->sysName = $validated['sysName'];
        }
        if (! empty($validated['os'])) {
            $device->os = $validated['os'];
        }
        if (! empty($validated['hardware'])) {
            $device->hardware = $validated['hardware'];
        }

        /** @var array<string, array<string, mixed>> $rawMethods */
        $rawMethods = $validated['polling_methods'] ?? [];

        // Per-method validate flags: validate if *any* active method requests it.
        // The SNMP method's validate flag doubles as the old force_add inverse.
        $forceAdd = $request->boolean('force_add') || collect($rawMethods)
            ->filter(fn (array $data): bool => (bool) ($data['active'] ?? false))
            ->every(fn (array $data): bool => empty($data['validate']));

        $pollingMethods = (new \App\Actions\Device\BuildDefaultPollingMethods($this->pollingMethods))->execute($device, ['methods' => $rawMethods]);

        try {
            $validator = new ValidateDeviceAndCreate($device, $pollingMethods, $forceAdd);
            $success = $validator->execute();

            if (! $success) {
                return response()->json([
                    'message' => __('Failed to save device.'),
                    'errors' => ['hostname' => [__('Failed to save device.')]],
                ], 422);
            }
        } catch (HostUnreachableException $e) {
            $reasons = $e->getReasons();
            $errors = array_merge([$e->getMessage()], $reasons);

            return response()->json([
                'status' => 'unreachable',
                'message' => $e->getMessage(),
                'error_details' => ! empty($reasons) ? implode("\n", $reasons) : null,
                'errors' => ['hostname' => $errors],
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => ['hostname' => [$e->getMessage()]],
            ], 422);
        }

        $toast->success(__('Device added successfully'));

        return response()->json([
            'status' => 'ok',
            'message' => __('Device added successfully'),
            'redirect' => route('device', ['device' => $device->device_id ?? 0]),
        ]);
    }
}
