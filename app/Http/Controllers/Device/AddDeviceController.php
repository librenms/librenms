<?php

namespace App\Http\Controllers\Device;

use App\Actions\Device\ValidateDeviceAndCreate;
use App\Facades\LibrenmsConfig;
use App\Http\Interfaces\ToastInterface;
use App\Http\Requests\StoreDeviceRequest;
use App\Models\Device;
use App\Models\PollerGroup;
use App\Models\Secret;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\PortAssociationMode;
use LibreNMS\Exceptions\HostUnreachableException;

class AddDeviceController
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('create', Device::class);

        $availableMethods = collect(PollingMethodType::cases())->map(function (PollingMethodType $type): array {
            $definition = $type->definition();
            $secretDefinition = $definition->secretDefinition();
            $schemaFields = $secretDefinition ? $secretDefinition->buildSchemaFields(dataVar: "methods['" . $type->value . "'].formData") : [];

            return [
                'type' => $type->value,
                'label' => __('poller.methods.' . $type->value),
                'icon' => $definition->icon(),
                'schema_fields' => $schemaFields,
                'schema_defaults' => $secretDefinition?->schemaDefaults() ?? [],
                'settings_fields' => $definition->buildSchemaFields(dataVar: "methods['" . $type->value . "'].settingsData"),
                'settings_defaults' => $definition->schemaDefaults(),
            ];
        })->all();

        $defaultPollerGroup = LibrenmsConfig::get('default_poller_group', 0);
        $pollerGroups = PollerGroup::orderBy('group_name')->get();
        $defaultPortAssocMode = LibrenmsConfig::get('default_port_association_mode', 'ifIndex');
        $portAssocModes = PortAssociationMode::getModes();
        $secrets = Secret::all();

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
                    'default' => (bool) old("polling_methods.{$type}.default"),
                    'formData' => old("polling_methods.{$type}.secret_data", $method['schema_defaults'] ?? []),
                    'settingsData' => old("polling_methods.{$type}.settings", $method['settings_defaults'] ?? []),
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
            'default_port_association_mode' => $defaultPortAssocMode,
            'port_association_modes' => $portAssocModes,
            'secrets' => $secrets,
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
        $device->port_association_mode = $validated['port_assoc_mode']
            ?? (int) LibrenmsConfig::get('default_port_association_mode', 1);

        /** @var array<string, array<string, mixed>> $rawMethods */
        $rawMethods = $validated['polling_methods'] ?? [];

        // When SNMP is explicitly disabled / inactive in the submitted payload
        if (empty($rawMethods['snmp']['active'])) {
            $device->setAttribute('snmp_disable', true);
            $device->sysName = $validated['sysName'] ?: '';
            $device->os = $validated['os'] ?: 'ping';
            $device->hardware = $validated['hardware'] ?: '';
        } else {
            $device->setAttribute('snmp_disable', false);
        }

        // Per-method validate flags: validate if *any* active method requests it.
        // The SNMP method's validate flag doubles as the old force_add inverse.
        $forceAdd = collect($rawMethods)
            ->filter(fn (array $data): bool => (bool) ($data['active'] ?? false))
            ->every(fn (array $data): bool => empty($data['validate']));

        try {
            $validator = new ValidateDeviceAndCreate($device, $forceAdd, false, ['methods' => $rawMethods]);
            $success = $validator->execute();

            if (! $success) {
                return response()->json([
                    'message' => __('Failed to save device.'),
                    'errors' => ['hostname' => [__('Failed to save device.')]],
                ], 422);
            }
        } catch (HostUnreachableException $e) {
            $errors = array_merge([$e->getMessage()], $e->getReasons());

            return response()->json([
                'message' => $e->getMessage(),
                'errors' => ['hostname' => $errors],
            ], 422);
        } catch (\Exception $e) {
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
