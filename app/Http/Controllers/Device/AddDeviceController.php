<?php

namespace App\Http\Controllers\Device;

use App\Actions\Device\BuildDefaultPollingMethods;
use App\Actions\Device\ValidateDeviceAndCreate;
use App\Facades\LibrenmsConfig;
use App\Http\Interfaces\ToastInterface;
use App\Http\Requests\StoreDeviceRequest;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\PollerGroup;
use App\Models\Secret;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Exceptions\HostExistsException;
use LibreNMS\Exceptions\HostIpExistsException;
use LibreNMS\Exceptions\HostSysnameExistsException;
use LibreNMS\Exceptions\HostUnreachableException;
use LibreNMS\Exceptions\MissingSecretException;
use LibreNMS\Exceptions\SnmpVersionUnsupportedException;
use LibreNMS\Polling\Method\PollingMethodRegistry;

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

        $availableMethods = collect(PollingMethodType::cases())->map(function (PollingMethodType $type): array {
            $method = $this->pollingMethods->get($type);
            $definition = $method->definition();
            $secretDefinition = $method->secretType()?->definition();

            return [
                'type' => $type->value,
                'label' => $type->label(),
                'icon' => $definition->icon(),
                'schema_fields' => $secretDefinition?->buildSchemaFields(dataVar: "methods['" . $type->value . "'].formData") ?? [],
                'schema_defaults' => $secretDefinition?->schemaDefaults() ?? [],
                'settings_fields' => $definition->settingsFields($method->defaults(), "methods['" . $type->value . "'].settingsData"),
                'settings_keys' => array_keys($definition->fields()),
                'default_affects_availability' => $method->defaultAffectsAvailability(),
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
            'active_tab' => old('active_tab', in_array(PollingMethodType::Snmp->value, $oldActiveMethods, true) ? PollingMethodType::Snmp->value : ($oldActiveMethods[0] ?? '')),
            'active_methods' => $oldActiveMethods,
            'methods' => collect($availableMethods)->mapWithKeys(function (array $method) {
                $type = $method['type'];

                return [$type => [
                    'validate' => old("polling_methods.{$type}.validate") !== null ? (bool) old("polling_methods.{$type}.validate") : true,
                    'affects_availability' => old("polling_methods.{$type}.affects_availability") !== null ? (bool) old("polling_methods.{$type}.affects_availability") : $method['default_affects_availability'],
                    'secret_mode' => old("polling_methods.{$type}.secret_mode", 'default'),
                    'secret_id' => old("polling_methods.{$type}.secret_id", ''),
                    'description' => old("polling_methods.{$type}.description", ''),
                    'formData' => old("polling_methods.{$type}.secret_data", $method['schema_defaults'] ?? []),
                    'settingsData' => old("polling_methods.{$type}.settings", array_fill_keys($method['settings_keys'], '')),
                ]];
            })->all(),
            'all_types' => collect($availableMethods)->map(fn ($m) => ['type' => $m['type'], 'label' => $m['label']])->values()->all(),
            'store_url' => route('device.add.store'),
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

    public function store(StoreDeviceRequest $request, ToastInterface $toast, BuildDefaultPollingMethods $buildMethods): JsonResponse
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

        $pollingMethods = $buildMethods->execute($device, ['methods' => $rawMethods]);

        if ($pollingMethods->isEmpty()) {
            return response()->json([
                'message' => __('At least one polling method is required'),
                'errors' => ['polling_methods' => [__('At least one polling method is required')]],
            ], 422);
        }

        if ($pollingMethods->contains(fn (DevicePollingMethod $m): bool => $m->secret?->exists === false)) {
            $this->authorize('create', Secret::class);
        }

        // Methods with validation unchecked are saved without checking them, the duplicate checks still run unless forced
        $uncheckedMethods = $pollingMethods
            ->filter(fn (DevicePollingMethod $m): bool => empty($rawMethods[$m->method_type->value]['validate']))
            ->pluck('method_type')
            ->all();

        try {
            $validator = new ValidateDeviceAndCreate($device, $pollingMethods, $request->boolean('force_add'), false, $uncheckedMethods);
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
        } catch (HostIpExistsException|HostSysnameExistsException $e) {
            // add anyway (force_add) skips these checks, a duplicate hostname is always rejected
            return response()->json([
                'status' => 'duplicate',
                'message' => $e->getMessage(),
                'errors' => ['hostname' => [$e->getMessage()]],
            ], 422);
        } catch (HostExistsException|SnmpVersionUnsupportedException|MissingSecretException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => ['hostname' => [$e->getMessage()]],
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => __('Failed to save device.'),
                'errors' => ['hostname' => [__('Failed to save device.')]],
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
