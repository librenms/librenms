<?php

namespace LibreNMS\Polling\Secrets;

use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Validation\ValidationException;
use LibreNMS\Enum\PollingMethodType;

class SecretService
{
    public function resolveExisting(int $id, PollingMethodType $type): Secret
    {
        $secret = Secret::findOrFail($id);

        if ($secret->secret_type->value !== $type->value) {
            throw ValidationException::withMessages([
                'secret_id' => __('poller.credential_type_mismatch'),
            ]);
        }

        return $secret;
    }

    public function updateOrCreate(DevicePollingMethod $row, PollingMethodType $type, array $data, string $mode): Secret
    {
        if (! $row->secret || $mode === 'create') {
            return $this->create($type, $data, [
                'description' => 'Custom ' . strtoupper($type->value),
                'default' => false,
            ]);
        }

        $row->secret->update(['data' => $data]);

        return $row->secret;
    }

    public function create(PollingMethodType $type, array $data, array $meta): Secret
    {
        return Secret::create([
            'description' => $meta['description'],
            'secret_type' => $type->value,
            'default' => $meta['default'] ?? false,
            'data' => $data,
        ]);
    }
}
