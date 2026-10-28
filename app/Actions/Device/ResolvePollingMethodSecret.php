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
     * @param  array<string, mixed>  $input  secret_mode, secret_id, secret_type, description and secret_data
     * @param  string  $errorKeyPrefix  prefix for the validation error keys, where the input is nested in the request
     *
     * @throws ValidationException
     */
    public function execute(Device $device, PollingMethodType $type, array $input, string $errorKeyPrefix = ''): ?Secret
    {
        $method = $this->pollingMethods->get($type);
        $secretTypes = $method->secretTypes();
        if (empty($secretTypes)) {
            return null;
        }

        $mode = SecretMode::tryFrom($input['secret_mode'] ?? '') ?? SecretMode::Default;
        $description = $input['description'] ?? null;
        $data = $input['secret_data'] ?? [];

        if ($mode === SecretMode::Existing || $mode === SecretMode::Edit) {
            $secret = Secret::resolveForTypes((int) ($input['secret_id'] ?? 0), $secretTypes, errorKey: $errorKeyPrefix . 'secret_id');

            if ($mode === SecretMode::Edit) {
                $secret->data = $data;
                $secret->description = $description ?: $secret->description;
            }

            return $secret;
        }

        if ($mode === SecretMode::New) {
            $secretType = $method->secretTypeFrom($input['secret_type'] ?? null);
            if ($secretType === null) {
                throw ValidationException::withMessages([$errorKeyPrefix . 'secret_type' => __('poller.credential_type_mismatch')]);
            }

            return new Secret([
                'secret_type' => $secretType,
                'description' => $description ?: Secret::defaultDescription($type, $device->hostname),
                'data' => $data,
            ]);
        }

        return null;
    }
}
