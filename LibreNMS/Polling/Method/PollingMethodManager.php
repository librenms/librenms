<?php

/**
 * PollingMethodManager.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Polling\Method;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Validation\ValidationException;
use LibreNMS\Enum\PollingMethodType;

class PollingMethodManager
{
    /**
     * Build an unsaved DevicePollingMethod model with optional Secret relation.
     *
     * @param  array<string, mixed>  $settings
     * @param  array<string, mixed>  $secretData
     */
    public function build(
        PollingMethodType $type,
        array $settings = [],
        array $secretData = [],
        string $credentialMode = 'default',
        ?int $secretId = null,
        ?string $secretDescription = null,
        bool $secretDefault = false,
        bool $enabled = true,
        ?bool $affectsAvailability = null,
        ?Device $device = null,
    ): DevicePollingMethod {
        $definition = PollingMethodDefinition::for($type);
        $resolvedSettings = $definition->resolveSettings($settings);
        $affectsAvail = $affectsAvailability ?? (bool) ($definition->defaults()['affects_availability'] ?? false);

        $method = new DevicePollingMethod([
            'method_type' => $type,
            'enabled' => $enabled,
            'affects_availability' => $affectsAvail,
            'settings' => $resolvedSettings,
        ]);

        if ($device !== null) {
            $method->device_id = $device->device_id;
        }

        if ($definition->secretDefinition() !== null) {
            $secret = $this->resolveSecretForBuild($type, $credentialMode, $secretId, $secretData, $secretDescription, $secretDefault, $device);
            if ($secret !== null) {
                $method->setRelation('secret', $secret);
                if ($secret->exists) {
                    $method->secret_id = $secret->id;
                }
            }
        }

        return $method;
    }

    /**
     * Save or update a DevicePollingMethod row and its Secret on a device.
     *
     * @param  array<string, mixed>  $settings
     * @param  array<string, mixed>  $secretData
     */
    public function save(
        Device $device,
        PollingMethodType $type,
        array $settings = [],
        array $secretData = [],
        string $credentialMode = 'default',
        ?int $secretId = null,
        ?string $secretDescription = null,
        bool $secretDefault = false,
        bool $enabled = true,
        ?bool $affectsAvailability = null,
    ): DevicePollingMethod {
        $definition = PollingMethodDefinition::for($type);

        /** @var DevicePollingMethod $method */
        $method = DevicePollingMethod::with('secret')
            ->firstOrNew([
                'device_id' => $device->device_id,
                'method_type' => $type,
            ]);

        $affectsAvail = $affectsAvailability ?? (bool) ($definition->defaults()['affects_availability'] ?? false);
        $method->enabled = $enabled;
        $method->affects_availability = $affectsAvail;
        $method->settings = $definition->resolveSettings($settings, $method->settings ?? []);

        if ($definition->secretDefinition() !== null) {
            if ($credentialMode === 'existing' && $secretId !== null) {
                $secret = $this->resolveExistingSecret($secretId, $type);
                $method->secret_id = $secret->id;
                $method->setRelation('secret', $secret);
            } elseif ($method->secret) {
                if (! empty($secretData)) {
                    $method->secret->update(['data' => array_merge($method->secret->data, $secretData)]);
                }
            } elseif (! empty($secretData) || $credentialMode === 'new') {
                $description = $secretDescription ?: (strtoupper($type->value) . ' ' . $device->hostname);
                $secret = $this->createSecret($type, $secretData, $description, $secretDefault);
                $method->secret_id = $secret->id;
                $method->setRelation('secret', $secret);
            }
        }

        $method->save();
        $device->load('pollingMethods.secret');

        return $method;
    }

    /**
     * Update an existing DevicePollingMethod row and its secret.
     *
     * @param  array<string, mixed>  $settings
     * @param  array<string, mixed>  $secretData
     */
    public function update(
        DevicePollingMethod $method,
        array $settings = [],
        array $secretData = [],
        ?string $secretUpdateMode = null,
        ?int $secretId = null,
        ?bool $enabled = null,
        ?bool $affectsAvailability = null,
    ): DevicePollingMethod {
        $type = $method->method_type;
        $definition = PollingMethodDefinition::for($type);

        if ($enabled !== null) {
            $method->enabled = $enabled;
        }

        if ($affectsAvailability !== null) {
            $method->affects_availability = $affectsAvailability;
        }

        $method->settings = $definition->resolveSettings($settings, $method->settings ?? []);

        if ($definition->secretDefinition() !== null) {
            if ($secretId !== null) {
                $secret = $this->resolveExistingSecret($secretId, $type);
                $method->secret_id = $secret->id;
                $method->setRelation('secret', $secret);
            } elseif (! empty($secretData)) {
                $mode = $secretUpdateMode ?? 'update';
                $secret = $this->updateOrCreateSecret($method, $type, $secretData, $mode);
                $method->secret_id = $secret->id;
                $method->setRelation('secret', $secret);
            }
        }

        $method->save();

        return $method;
    }

    /**
     * Resolve an existing Secret by ID and verify its type matches the polling method type.
     *
     * @throws ValidationException
     */
    public function resolveExistingSecret(int $id, PollingMethodType $type): Secret
    {
        $secret = Secret::findOrFail($id);

        if ($secret->secret_type->value !== $type->value) {
            throw ValidationException::withMessages([
                'secret_id' => __('poller.credential_type_mismatch'),
            ]);
        }

        return $secret;
    }

    /**
     * Create a new Secret model.
     *
     * @param  array<string, mixed>  $data
     */
    public function createSecret(PollingMethodType $type, array $data, string $description, bool $default = false): Secret
    {
        return Secret::create([
            'description' => $description,
            'secret_type' => $type->value,
            'default' => $default,
            'data' => $data,
        ]);
    }

    /**
     * Update an existing secret relation or create a new custom secret.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateOrCreateSecret(DevicePollingMethod $row, PollingMethodType $type, array $data, string $mode): Secret
    {
        if (! $row->secret || $mode === 'create') {
            return $this->createSecret($type, $data, 'Custom ' . strtoupper($type->value));
        }

        $row->secret->update(['data' => $data]);

        return $row->secret;
    }

    /**
     * @param  array<string, mixed>  $secretData
     */
    private function resolveSecretForBuild(
        PollingMethodType $type,
        string $credentialMode,
        ?int $secretId,
        array $secretData,
        ?string $secretDescription,
        bool $secretDefault,
        ?Device $device,
    ): ?Secret {
        if ($credentialMode === 'existing' && $secretId) {
            return $this->resolveExistingSecret($secretId, $type);
        }

        if ($credentialMode === 'new' || ! empty($secretData)) {
            $description = $secretDescription ?: ($device ? strtoupper($type->value) . ' ' . $device->hostname : '');

            return new Secret([
                'secret_type' => $type->value,
                'description' => $description,
                'default' => $secretDefault,
                'data' => $secretData,
            ]);
        }

        return null;
    }
}
