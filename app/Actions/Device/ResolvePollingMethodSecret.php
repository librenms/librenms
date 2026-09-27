<?php

namespace App\Actions\Device;

use App\Models\Device;
use App\Models\Secret;
use Illuminate\Validation\ValidationException;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretMode;
use LibreNMS\Polling\Method\PollingMethodRegistry;

readonly class ResolvePollingMethodSecret
{
    public function __construct(
        private PollingMethodRegistry $pollingMethods,
    ) {
    }

    /**
     * The secret selected by polling method form input. New and edited secrets are not saved.
     * Returns null when no secret was selected.
     *
     * @param  array<string, mixed>  $input  secret_mode, secret_id, description and secret_data
     *
     * @throws ValidationException
     */
    public function execute(Device $device, PollingMethodType $type, array $input): ?Secret
    {
        $secretType = $this->pollingMethods->get($type)->secretType();
        if ($secretType === null) {
            return null;
        }

        $mode = SecretMode::tryFrom($input['secret_mode'] ?? '') ?? SecretMode::Default;
        $description = $input['description'] ?? null;
        $data = $input['secret_data'] ?? [];

        if ($mode === SecretMode::Existing || $mode === SecretMode::Edit) {
            $secret = Secret::resolveForType((int) ($input['secret_id'] ?? 0), $secretType);

            if ($mode === SecretMode::Edit) {
                $secret->data = $data;
                $secret->description = $description ?: $secret->description;
            }

            return $secret;
        }

        // legacy callers send secret data without a mode
        if ($mode === SecretMode::New || ! empty($data)) {
            return new Secret([
                'secret_type' => $secretType,
                'description' => $description ?: Secret::defaultDescription($type, $device->hostname),
                'data' => $data,
            ]);
        }

        return null;
    }
}
