<?php

namespace App\Casts;

use App\Models\TransceiverMetric;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use LibreNMS\Enum\TransceiverMetricStatus;

class SyncMetricStatus implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): float|null
    {
        return $value === null ? null : (float) $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  TransceiverMetric  $model
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        $changes = [
            $key => $value === null ? null : (float) $value,
        ];

        if ($key === 'value') {
            $changes['value_prev'] = $attributes['value'] ?? null;
        }

        if ($model->hasThresholds()) {
            $changes['status'] = $this->calculateStatus($key === 'value' ? $value : $attributes['value'], $attributes);
        }

        return $changes;
    }

    protected function calculateStatus(float|null $value, array $attributes): TransceiverMetricStatus
    {
        if ($value === null) {
            return TransceiverMetricStatus::Unknown;
        }

        if (isset($attributes['threshold_min_critical']) && $value <= $attributes['threshold_min_critical']) {
            return TransceiverMetricStatus::ExceededMinCritical;
        }

        if (isset($attributes['threshold_max_critical']) && $value >= $attributes['threshold_max_critical']) {
            return TransceiverMetricStatus::ExceededMaxCritical;
        }

        if (isset($attributes['threshold_min_warning']) && $value <= $attributes['threshold_min_warning']) {
            return TransceiverMetricStatus::ExceededMinWarning;
        }

        if (isset($attributes['threshold_max_warning']) && $value >= $attributes['threshold_max_warning']) {
            return TransceiverMetricStatus::ExceededMaxWarning;
        }

        return TransceiverMetricStatus::Ok;
    }
}
