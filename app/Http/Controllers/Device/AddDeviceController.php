<?php

namespace App\Http\Controllers\Device;

use App\Actions\Device\BuildDefaultPollingMethods;
use App\Actions\Device\ValidateDeviceAndCreate;
use App\Facades\LibrenmsConfig;
use App\Http\Interfaces\ToastInterface;
use App\Http\Requests\StoreDeviceRequest;
use App\Models\Device;
use App\Models\PollerGroup;
use App\Models\Secret;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\PortAssociationMode;
use LibreNMS\Exceptions\HostUnreachableException;
use LibreNMS\Polling\Method\PollingMethodDefinition;
use LibreNMS\Polling\Method\PollingMethodManager;

class AddDeviceController
{
    use AuthorizesRequests;

    public function __construct(
        private readonly PollingMethodManager $pollingMethodManager = new PollingMethodManager,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('create', Device::class);

        $availableMethods = collect(PollingMethodType::cases())->map(function (PollingMethodType $type): array {
            $definition = PollingMethodDefinition::for($type);
            $schema = $definition->secretDefinition()?->schema() ?? [];
            $schemaFields = PollingMethodDefinition::buildSchemaFields($schema, "methods['" . $type->value . "'].formData");
            $settingsSchema = $definition->schema();

            return [
                'type' => $type->value,
                'label' => __('poller.methods.' . $type->value),
                'icon' => $definition->icon(),
                'schema_fields' => $schemaFields,
                'schema_defaults' => $definition->secretDefinition()?->schemaDefaults() ?? [],
                'settings_fields' => PollingMethodDefinition::buildSchemaFields($settingsSchema, "methods['" . $type->value . "'].settingsData"),
                'settings_defaults' => $definition->schemaDefaults(),
            ];
        });

        $availableSecrets = Secret::query()->orderBy('description')->get()->groupBy(
            fn (Secret $s): string => $s->secret_type->value
        );

        return view('device.add', [
            'availableMethods' => $availableMethods,
            'availableSecrets' => $availableSecrets,
            'poller_groups' => PollerGroup::orderBy('group_name')->pluck('group_name', 'id'),
            'default_poller_group' => LibrenmsConfig::get('default_poller_group', 0),
            'port_association_modes' => PortAssociationMode::getModes(),
            'default_port_association_mode' => LibrenmsConfig::get('default_port_association_mode', 'ifIndex'),
            'oldActiveMethods' => old('active_methods', ['snmp', 'icmp']),
        ]);
    }

    public function store(StoreDeviceRequest $request, ToastInterface $toast): RedirectResponse
    {
        $this->authorize('create', Device::class);

        $validated = $request->validated();

        /** @var array<string, array<string, mixed>> $rawMethods */
        $rawMethods = $validated['polling_methods'] ?? [];
        $snmpActive = (bool) ($rawMethods['snmp']['active'] ?? false);

        $portAssocModeStr = $rawMethods['snmp']['settings']['port_association_mode']
            ?? $validated['port_assoc_mode']
            ?? LibrenmsConfig::get('default_port_association_mode', 'ifIndex');

        $device = new Device([
            'hostname' => $validated['hostname'],
            'poller_group' => $validated['poller_group'] ?? LibrenmsConfig::get('default_poller_group', 0),
            'port_association_mode' => PortAssociationMode::getId($portAssocModeStr) ?? 1,
        ]);

        $pollingMethods = collect();

        if (isset($rawMethods['snmp']['settings'])) {
            $settings = $rawMethods['snmp']['settings'];
            $device->port = (int) ($settings['port'] ?? LibrenmsConfig::get('snmp.port', 161));
            $device->transport = $settings['transport'] ?? LibrenmsConfig::get('snmp.transports.0', 'udp');
            if (isset($settings['port_association_mode'])) {
                $device->port_association_mode = PortAssociationMode::getId($settings['port_association_mode']) ?? 1;
            }
        }

        $pollingMethods = (new BuildDefaultPollingMethods())->execute($device, ['methods' => $rawMethods]);
        $device->setRelation('pollingMethods', $pollingMethods);

        if (! $snmpActive) {
            $device->snmp_disable = true;
            $device->os = $validated['os'] ?: 'ping';
            $device->sysName = $validated['sysName'] ?: '';
            $device->hardware = $validated['hardware'] ?: '';
        } else {
            $device->snmp_disable = false;
        }

        // Per-method validate flags: validate if *any* active method requests it.
        // The SNMP method's validate flag doubles as the old force_add inverse.
        $forceAdd = collect($rawMethods)
            ->filter(fn (array $data): bool => (bool) ($data['active'] ?? false))
            ->every(fn (array $data): bool => empty($data['validate']));

        try {
            $validator = new ValidateDeviceAndCreate($device, $forceAdd);
            $success = $validator->execute();

            if (! $success) {
                return back()->withInput()->withErrors(['hostname' => __('Failed to save device.')]);
            }
        } catch (HostUnreachableException $e) {
            $errors = array_merge([$e->getMessage()], $e->getReasons());

            return back()->withInput()->withErrors([
                'hostname' => $errors,
            ]);
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['hostname' => $e->getMessage()]);
        }

        $toast->success(__('Device added successfully'));

        return redirect()->route('device', ['device' => $device->device_id]);
    }
}
