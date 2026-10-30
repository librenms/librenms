<?php

namespace App\Queue;

use App\Jobs\Concerns\DeviceJob;

/**
 * LibreNMS data added to queued job payloads, read back from failed jobs, retries and Horizon's job records
 */
class JobPayload
{
    /**
     * Data to add to a payload as it is created (Queue::createPayloadUsing), the command is not serialized yet
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public static function extra(array $payload): array
    {
        $command = $payload['data']['command'] ?? null;

        return is_object($command) && in_array(DeviceJob::class, class_uses_recursive($command))
            ? ['device_id' => $command->deviceId()]
            : [];
    }

    /**
     * The device a job is for
     *
     * @param  array<string, mixed>  $payload
     */
    public static function deviceId(array $payload): ?int
    {
        return isset($payload['device_id']) ? (int) $payload['device_id'] : null;
    }

    /**
     * Job class basename, displayName is the class unless the job names itself
     *
     * @param  array<string, mixed>  $payload
     */
    public static function name(array $payload): string
    {
        return class_basename((string) ($payload['displayName'] ?? ''));
    }
}
