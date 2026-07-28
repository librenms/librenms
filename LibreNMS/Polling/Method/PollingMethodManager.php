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
     * Save or update a DevicePollingMethod row settings for a device.
     *
     * @param  array<string, mixed>  $settings
     */
    public function save(
        Device $device,
        PollingMethodType $type,
        array $settings = [],
        bool $enabled = true,
        ?bool $affectsAvailability = null,
    ): DevicePollingMethod {
        $definition = PollingMethodDefinition::for($type);

        /** @var DevicePollingMethod $method */
        $method = DevicePollingMethod::firstOrNew([
            'device_id' => $device->device_id,
            'method_type' => $type,
        ]);

        $affectsAvail = $affectsAvailability ?? $definition->defaultAffectsAvailability();
        $method->enabled = $enabled;
        $method->affects_availability = $affectsAvail;
        $method->settings = $definition->resolveValues($settings, $method->settings ?? []);

        $method->save();

        return $method;
    }

    /**
     * Build an unsaved, transient in-memory DevicePollingMethod model.
     *
     * @param  array<string, mixed>  $settings
     * @param  array<string, mixed>  $secretData
     */
    public function transient(
        PollingMethodType $type,
        array $settings = [],
        array $secretData = [],
        ?Device $device = null,
        ?bool $affectsAvailability = null,
    ): DevicePollingMethod {
        $definition = PollingMethodDefinition::for($type);
        $resolvedSettings = $definition->resolveValues($settings);
        $affectsAvail = $affectsAvailability ?? $definition->defaultAffectsAvailability();

        $method = new DevicePollingMethod([
            'method_type' => $type,
            'enabled' => true,
            'affects_availability' => $affectsAvail,
            'settings' => $resolvedSettings,
        ]);

        if ($device !== null) {
            $method->device_id = $device->device_id;
            $method->setRelation('device', $device);
        }

        if ($definition->secretDefinition() !== null && ! empty($secretData)) {
            $secret = new Secret([
                'secret_type' => $type->value,
                'description' => $device ? strtoupper($type->value) . ' ' . $device->hostname : '',
                'default' => false,
                'data' => $secretData,
            ]);
            $method->setRelation('secret', $secret);
        }

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
}
