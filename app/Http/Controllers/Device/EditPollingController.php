<?php

namespace App\Http\Controllers\Device;

use App\Actions\Device\SetDeviceAvailability;
use App\Http\Interfaces\ToastInterface;
use App\Http\Requests\StorePollingMethodRequest;
use App\Http\Requests\UpdatePollingMethodRequest;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\PollingMethodRegistry;
use LibreNMS\Polling\Secrets\Definitions\SecretDefinition;

class EditPollingController
{
    use AuthorizesRequests;

    public function __construct(
        private readonly PollingMethodRegistry $pollingMethods,
    ) {
    }

    /**
     * @throws AuthorizationException
     */
    public function index(Device $device): View
    {
        $this->authorize('update', $device);

        $device->load('pollingMethods.secret');

        $allMethods = collect($this->pollingMethods->types())->map(
            fn (PollingMethodType $type): array => $this->buildMethodData($device, $type)
        );

        $configuredMethods = $allMethods->filter(fn (array $m): bool => $m['configured'])->values();
        $snmpConfigured = $configuredMethods->firstWhere('type', 'snmp');
        $defaultTab = ($snmpConfigured && ! empty($snmpConfigured['enabled'])) ? 'snmp' : $configuredMethods->first()['type'] ?? '';

        return view('device.edit.polling', [
            'device' => $device,
            'allMethods' => $allMethods,
            'configuredMethods' => $configuredMethods,
            'unconfiguredMethods' => $allMethods->filter(fn (array $m): bool => ! $m['configured'])->values(),
            'defaultTab' => $defaultTab,
            'availableSecrets' => Secret::query()
                ->when(auth()->user(), fn ($q, $user) => $q->hasAccess($user))
                ->orderBy('description')
                ->get()
                ->groupBy(fn (Secret $s): string => $s->secret_type->value),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildMethodData(Device $device, PollingMethodType $type): array
    {
        $method = $this->pollingMethods->require($type);
        /** @var DevicePollingMethod|null $row */
        $row = $device->pollingMethods->firstWhere('method_type', $type);
        $secret = $row?->secret;
        $canUnmaskSecrets = Gate::allows('unmask', Secret::class);
        $secretType = $method->secretType();
        $secretDef = SecretDefinition::for($secretType);
        $schema = $secretDef?->schema() ?? [];
        $schemaFields = $secretDef ? $secretDef->buildSchemaFields() : [];
        $settingsFields = $method->buildSchemaFields(dataVar: 'settingsData');
        $secretsForType = $secretType ? Secret::query()
            ->when(auth()->user(), fn ($q, $user) => $q->hasAccess($user))
            ->where('secret_type', $secretType)
            ->withCount('devices')
            ->orderBy('description')
            ->get() : collect();
        $secretMeta = $secretsForType->mapWithKeys(fn (Secret $availableSecret): array => [
            (string) $availableSecret->id => [
                'description' => $availableSecret->description,
                'usage_count' => $availableSecret->devices_count,
            ],
        ])->all();
        $secretFormDataById = $secretsForType->mapWithKeys(fn (Secret $availableSecret): array => [
            (string) $availableSecret->id => (object) collect($schemaFields)->mapWithKeys(fn (array $field): array => [
                $field['key'] => $canUnmaskSecrets ? (string) data_get($availableSecret->data, $field['key'], '') : '',
            ])->all(),
        ])->all();

        $settingsDefaults = $method->schemaDefaults();

        return [
            'type' => $type->value,
            'label' => __('poller.methods.' . $type->value),
            'icon' => $method->icon(),
            'schema_fields' => $schemaFields,
            'schema_defaults' => $secretDef?->schemaDefaults() ?? [],
            'settings_fields' => $settingsFields,
            'settings_defaults' => $settingsDefaults,
            'settings' => array_merge(
                $method->formDefaults(),
                $row->settings ?? [],
            ),
            'affects_availability' => $row ? $row->affects_availability : $method->defaultAffectsAvailability(),
            'secret' => $secret,
            'secret_form_data' => $secret ? collect($schema)->mapWithKeys(fn (array $field, string $key): array => [
                $key => $canUnmaskSecrets ? (string) data_get($secret->data, $key, '') : '',
            ])->all() : null,
            'secret_meta' => $secretMeta,
            'secret_form_data_by_id' => $secretFormDataById,
            'usage_count' => $secret?->devices()->count() ?? 0,
            'configured' => $row !== null,
            'enabled' => $row ? $row->enabled : true,
            'last_check_successful' => $row?->last_check_successful,
        ];
    }

    /**
     * @throws AuthorizationException|ValidationException
     */
    public function store(StorePollingMethodRequest $request, Device $device, ToastInterface $toast): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $device);

        $validated = $request->validated();
        $type = $request->pollingType() ?? PollingMethodType::from($validated['method_type']);
        $method = $this->pollingMethods->require($type);

        if ($method->hasSecret()) {
            $this->authorize('create', Secret::class);
        }

        $credentialMode = $validated['credential_mode'] ?? 'existing';
        $secretId = isset($validated['secret_id']) ? (int) $validated['secret_id'] : null;
        if ($method->hasSecret() && $credentialMode === 'existing' && ! $secretId) {
            throw ValidationException::withMessages([
                'secret_id' => __('poller.select_credential'),
            ]);
        }

        $forceSave = $request->boolean('force_save');

        if (! $forceSave) {
            $transientSettings = $request->validatedSettings();
            $transientSecretData = [];
            $transientSecret = null;
            if ($method->hasSecret()) {
                $secretType = $method->secretType();
                if ($credentialMode === 'existing' && $secretId !== null) {
                    $transientSecret = Secret::resolveForType($secretId, $secretType);
                } else {
                    $transientSecretData = $request->validatedSecretData();
                }
            }

            $transientMethod = new DevicePollingMethod([
                'method_type' => $type,
                'settings' => $method->filterOverrides($transientSettings),
                'affects_availability' => $method->defaultAffectsAvailability(),
                'enabled' => true,
            ]);
            $transientMethod->setRelation('device', $device);

            if ($transientSecret !== null) {
                $transientMethod->setRelation('secret', $transientSecret);
                $transientMethod->secret_id = $transientSecret->id;
            } elseif (! empty($transientSecretData)) {
                $transientMethod->setRelation('secret', new Secret([
                    'secret_type' => $method->secretType(),
                    'data' => $transientSecretData,
                ]));
            }

            $existingMethods = $device->pollingMethods->reject(fn ($m) => $m->method_type === $type);
            $testDevice = clone $device;
            $testDevice->setRelation('pollingMethods', $existingMethods->concat([$transientMethod]));

            $probeResult = $method->check($testDevice);

            if (! $probeResult->isSuccess()) {
                $errorDetails = $probeResult->errorMessage();

                if ($request->wantsJson()) {
                    return response()->json([
                        'status' => 'unreachable',
                        'message' => __('poller.reachability_failed', [
                            'hostname' => $device->hostname,
                            'method' => __('poller.methods.' . $type->value),
                        ]),
                        'error_details' => $errorDetails,
                    ], 422);
                }

                $toast->error(__('poller.reachability_failed', [
                    'hostname' => $device->hostname,
                    'method' => __('poller.methods.' . $type->value),
                ]));

                return redirect()->back();
            }
        }

        /** @var DevicePollingMethod $row */
        $row = DevicePollingMethod::firstOrNew([
            'device_id' => $device->device_id,
            'method_type' => $type,
        ]);
        $row->enabled = true;
        $row->affects_availability = $method->defaultAffectsAvailability();
        $row->settings = $method->filterOverrides($request->validatedSettings());
        $row->save();

        if ($method->hasSecret()) {
            $secretType = $method->secretType();
            if ($credentialMode === 'existing' && $secretId !== null) {
                $secret = Secret::resolveForType($secretId, $secretType);
                $row->secret()->associate($secret)->save();
            } else {
                $description = $validated['description'];
                $secret = Secret::create([
                    'secret_type' => $secretType,
                    'description' => $description,
                    'data' => $request->validatedSecretData(),
                ]);
                $row->secret()->associate($secret)->save();
            }
        }

        $row->last_check_successful = isset($probeResult) ? $probeResult->isSuccess() : false;
        $row->last_checked_at = now();
        $row->save();

        $toast->success(__('poller.method_added'));

        if ($request->wantsJson()) {
            $device->load('pollingMethods.secret');

            return response()->json([
                'status' => 'ok',
                'message' => __('poller.method_added'),
                'method' => $this->buildMethodData($device, $type),
            ]);
        }

        return redirect()->route('device.edit.polling', ['device' => $device, 'tab' => $type->value]);
    }

    /**
     * @throws AuthorizationException|ValidationException
     */
    public function update(
        UpdatePollingMethodRequest $request,
        Device $device,
        ToastInterface $toast,
        SetDeviceAvailability $setDeviceAvailability
    ): JsonResponse|RedirectResponse {
        $this->authorize('update', $device);

        $type = $request->pollingType();
        if (! $type) {
            abort(404, 'Polling method not found.');
        }

        $method = $this->pollingMethods->require($type);
        /** @var DevicePollingMethod $deviceMethod */
        $deviceMethod = $device->pollingMethods()->where('method_type', $type->value)->firstOrFail();
        $validated = $request->validated();

        $secretId = null;
        if ($method->hasSecret()) {
            if (array_key_exists('secret_id', $validated)) {
                $this->authorize('update', Secret::class);
                $secretId = (int) $validated['secret_id'];
                if (! $secretId) {
                    throw ValidationException::withMessages([
                        'secret_id' => __('poller.select_credential'),
                    ]);
                }
            } elseif ($request->has('secret_data')) {
                $this->authorize('update', Secret::class);
            }
        }

        $forceSave = $request->boolean('force_save');
        $enabled = (bool) ($validated['enabled'] ?? true);

        if ($enabled && ! $forceSave) {
            $transientSettings = $validated['settings'] ?? [];
            $transientSecretData = [];
            $transientSecret = null;
            if ($method->hasSecret()) {
                $secretType = $method->secretType();
                $isEditingSecret = (bool) $request->input('is_editing_secret', $request->has('secret_data'));
                $secretData = $request->has('secret_data') ? $request->validatedSecretData() : null;

                if ($secretId !== null && ! $isEditingSecret) {
                    $transientSecret = Secret::resolveForType($secretId, $secretType);
                } elseif ($secretData !== null) {
                    $transientSecretData = $secretData;
                } else {
                    $transientSecret = $deviceMethod->secret;
                }
            }

            $transientMethod = new DevicePollingMethod([
                'method_type' => $type,
                'settings' => $method->filterOverrides($transientSettings),
                'affects_availability' => (bool) ($validated['affects_availability'] ?? false),
                'enabled' => true,
            ]);
            $transientMethod->setRelation('device', $device);

            if ($transientSecret !== null) {
                $transientMethod->setRelation('secret', $transientSecret);
                $transientMethod->secret_id = $transientSecret->id;
            } elseif (! empty($transientSecretData)) {
                $transientMethod->setRelation('secret', new Secret([
                    'secret_type' => $method->secretType(),
                    'data' => $transientSecretData,
                ]));
            }

            $existingMethods = $device->pollingMethods->reject(fn ($m) => $m->method_type === $type);
            $testDevice = clone $device;
            $testDevice->setRelation('pollingMethods', $existingMethods->concat([$transientMethod]));

            $probeResult = $method->check($testDevice);

            if (! $probeResult->isSuccess()) {
                $errorDetails = $probeResult->errorMessage();

                if ($request->wantsJson()) {
                    return response()->json([
                        'status' => 'unreachable',
                        'message' => __('poller.reachability_failed', [
                            'hostname' => $device->hostname,
                            'method' => __('poller.methods.' . $type->value),
                        ]),
                        'error_details' => $errorDetails,
                    ], 422);
                }

                $toast->error(__('poller.reachability_failed', [
                    'hostname' => $device->hostname,
                    'method' => __('poller.methods.' . $type->value),
                ]));

                return redirect()->back();
            }
        }

        $deviceMethod->setRelation('device', $device);

        $deviceMethod->enabled = $enabled;
        $deviceMethod->affects_availability = (bool) ($validated['affects_availability'] ?? false);
        $deviceMethod->settings = $method->filterOverrides($validated['settings'] ?? [], $deviceMethod->settings ?? []);
        $deviceMethod->save();

        if ($method->hasSecret()) {
            $secretType = $method->secretType();
            $isEditingSecret = (bool) $request->input('is_editing_secret', $request->has('secret_data'));
            $secretData = $request->has('secret_data') ? $request->validatedSecretData() : null;
            $mode = $validated['secret_update_mode'] ?? 'update';
            $description = $validated['description'] ?? null;

            if ($secretId !== null && ! $isEditingSecret) {
                $secret = Secret::resolveForType($secretId, $secretType);
                $deviceMethod->secret()->associate($secret)->save();
            } elseif ($isEditingSecret || $secretData !== null) {
                $targetSecret = $secretId !== null ? Secret::resolveForType($secretId, $secretType) : $deviceMethod->secret;
                $isShared = $targetSecret ? ($targetSecret->devices()->count() > 1) : false;
                $shouldCreate = ($mode === 'create' && $isShared) || ! $targetSecret;

                if ($shouldCreate) {
                    $secret = Secret::create([
                        'secret_type' => $secretType,
                        'description' => $description ?: ('Custom ' . strtoupper($type->value) . ' (' . $device->hostname . ')'),
                        'data' => $secretData ?? ($targetSecret ? $targetSecret->data : []),
                    ]);
                    $deviceMethod->secret()->associate($secret)->save();
                } else {
                    $updateAttributes = [];
                    if ($secretData !== null) {
                        $updateAttributes['data'] = $secretData;
                    }
                    if ($description !== null && $description !== '') {
                        $updateAttributes['description'] = $description;
                    }
                    if (! empty($updateAttributes) && $targetSecret) {
                        $targetSecret->update($updateAttributes);
                    }
                    if ($targetSecret) {
                        $deviceMethod->secret()->associate($targetSecret)->save();
                    }
                }
            }
        }

        $setDeviceAvailability->execute($device, false);
        $device->saveQuietly();

        if ($enabled) {
            $deviceMethod->last_check_successful = isset($probeResult) ? $probeResult->isSuccess() : false;
            $deviceMethod->last_checked_at = now();
            $deviceMethod->save();
        }

        $toast->success(__('poller.method_updated'));

        if ($request->wantsJson()) {
            $device->load('pollingMethods.secret');

            return response()->json([
                'status' => 'ok',
                'message' => __('poller.method_updated'),
                'method' => $this->buildMethodData($device, $type),
            ]);
        }

        return redirect()->route('device.edit.polling', ['device' => $device, 'tab' => $type->value]);
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(
        Device $device,
        string $methodType,
        ToastInterface $toast,
        SetDeviceAvailability $setDeviceAvailability
    ): JsonResponse|RedirectResponse {
        $this->authorize('update', $device);

        $type = PollingMethodType::tryFrom($methodType) ?? abort(404);
        $pollingMethod = $device->pollingMethods()->where('method_type', $type->value)->firstOrFail();

        if ($this->pollingMethods->require($type)->hasSecret()) {
            $this->authorize('delete', Secret::class);
        }

        $pollingMethod->delete();

        $setDeviceAvailability->execute($device, false);
        $device->saveQuietly();

        $toast->success(__('poller.method_removed'));

        if (request()->wantsJson()) {
            return response()->json([
                'status' => 'ok',
                'message' => __('poller.method_removed'),
            ]);
        }

        return redirect()->route('device.edit.polling', ['device' => $device, 'tab' => $type->value]);
    }
}
