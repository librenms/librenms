<?php

namespace App\Http\Controllers\Device;

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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\PortAssociationMode;
use LibreNMS\Enum\SecretType;
use LibreNMS\Exceptions\HostUnreachableException;
use LibreNMS\Polling\Secrets\SecretService;

class AddDeviceController
{
    use AuthorizesRequests;

    public function __construct(
        private readonly SecretService        $secretService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('create', Device::class);

        $availableMethods = collect(PollingMethodType::cases())->map(function (PollingMethodType $type): array {
            $methodClass = $type->methodClass();
            $schema = $type->hasSecret() ? $type->secretClass()::getUiSchema() : [];
            $schemaFields = PollingMethodType::buildSchemaFields($schema);
            $settingsSchema = $methodClass::getSettingsSchema();

            return [
                'type'            => $type->value,
                'label'           => __('poller.methods.' . $type->value),
                'schema_fields'   => $schemaFields,
                'schema_defaults' => collect($schema)->mapWithKeys(
                    fn(array $field, string $key): array => [
                        $key => $field['default'] ?? (isset($field['options']) ? array_key_first($field['options']) : ''),
                    ]
                )->all(),
                'settings_fields' => PollingMethodType::buildSchemaFields($settingsSchema, 'settingsData'),
            ];
        });

        $availableSecrets = Secret::query()->orderBy('description')->get()->groupBy(
            fn(Secret $s): string => $s->secret_type->value
        );

        return view('device.add', [
            'availableMethods'             => $availableMethods,
            'availableSecrets'             => $availableSecrets,
            'poller_groups'                => PollerGroup::orderBy('group_name')->pluck('group_name', 'id'),
            'default_poller_group'         => LibrenmsConfig::get('default_poller_group', 0),
            'port_association_modes'       => PortAssociationMode::getModes(),
            'default_port_association_mode' => LibrenmsConfig::get('default_port_association_mode', 'ifIndex'),
        ]);
    }

    public function store(StoreDeviceRequest $request, ToastInterface $toast): RedirectResponse
    {
        $this->authorize('create', Device::class);

        $validated = $request->validated();

        $device = new Device([
            'hostname'             => $validated['hostname'],
            'poller_group'         => $validated['poller_group'] ?? LibrenmsConfig::get('default_poller_group', 0),
            'port_association_mode' => PortAssociationMode::getId($validated['port_assoc_mode'] ?? 'ifIndex') ?? 1,
        ]);

        $rawMethods = $validated['polling_methods'] ?? [];
        $snmpActive = (bool) ($rawMethods['snmp']['active'] ?? false);

        $pollingMethods = collect();

        foreach ($rawMethods as $method => $data) {
            if (empty($data['active'])) {
                continue;
            }

            $type = PollingMethodType::tryFrom($method);
            if (! $type) {
                continue;
            }

            $settings = $data['settings'] ?? [];

            // SNMP port/transport live in settings on the form; promote them onto
            // the device row where the legacy schema expects them.
            if ($type === PollingMethodType::Snmp) {
                $device->port      = (int) ($settings['port'] ?? LibrenmsConfig::get('snmp.port', 161));
                $device->transport = $settings['transport'] ?? LibrenmsConfig::get('snmp.transports.0', 'udp');
                unset($settings['port'], $settings['transport']);
            }

            $pollingMethod = new DevicePollingMethod([
                'method_type'          => $type,
                'enabled'              => true,
                'affects_availability' => (bool) ($data['affects_availability'] ?? false),
                'settings'             => $settings,
            ]);

            if ($type->hasSecret()) {
                $secret = $this->resolveSecret($type, $data);
                if ($secret !== null) {
                    $pollingMethod->setRelation('secret', $secret);
                }
            }

            $pollingMethods->push($pollingMethod);
        }

        $device->setRelation('pollingMethods', $pollingMethods);

        if (! $snmpActive) {
            $device->snmp_disable = 1;
            $device->os           = $validated['os'] ?: 'ping';
            $device->sysName      = $validated['sysName'] ?: '';
            $device->hardware     = $validated['hardware'] ?: '';
        } else {
            $device->snmp_disable = 0;
        }

        // Per-method validate flags: validate if *any* active method requests it.
        // The SNMP method's validate flag doubles as the old force_add inverse.
        $forceAdd = collect($rawMethods)
            ->filter(fn(array $data): bool => (bool) ($data['active'] ?? false))
            ->every(fn(array $data): bool => empty($data['validate']));

        try {
            $validator = new ValidateDeviceAndCreate($device, $forceAdd);
            $success   = $validator->execute();

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

    /**
     * Resolve the secret for a polling method from the submitted credential_mode data.
     * Returns null for "default" mode (caller should not attach a secret relation).
     */
    private function resolveSecret(PollingMethodType $type, array $data): ?Secret
    {
        $mode = $data['credential_mode'] ?? 'default';

        if ($mode === 'existing') {
            return $this->secretService->resolveExisting((int) ($data['secret_id'] ?? 0), $type);
        }

        if ($mode === 'new') {
            return new Secret([
                'secret_type' => SecretType::tryFrom($type->value),
                'description' => $data['description'] ?? '',
                'default'     => (bool) ($data['default'] ?? false),
                'data'        => $data['secret_data'] ?? [],
            ]);
        }

        return null;
    }
}
