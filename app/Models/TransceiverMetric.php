<?php

namespace App\Models;

use App\Casts\SyncMetricStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LibreNMS\Enum\TransceiverMetricStatus;
use LibreNMS\Interfaces\Models\Keyable;

/** @property TransceiverMetricStatus $status Work around issue with phpstan */
class TransceiverMetric extends DeviceRelatedModel implements Keyable
{
    use HasFactory;
    protected $fillable = [
        'transceiver_id',
        'channel',
        'type',
        'description',
        'oid',
        'value',
        'multiplier',
        'divisor',
        'status',
        'transform_function',
        'threshold_min_critical',
        'threshold_min_warning',
        'threshold_max_warning',
        'threshold_max_critical',
    ];
    protected $attributes = ['channel' => 0];
    protected $casts = [
        'value' => SyncMetricStatus::class,
        'value_prev' => 'double',
        'threshold_min_critical' => SyncMetricStatus::class,
        'threshold_min_warning' => SyncMetricStatus::class,
        'threshold_max_warning' => SyncMetricStatus::class,
        'threshold_max_critical' => SyncMetricStatus::class,
    ];

    protected static function boot()
    {
        parent::boot();

        // default order
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderByRaw("(CASE `transceiver_metrics`.`type`
                WHEN 'power-rx' THEN 100 + `transceiver_metrics`.`channel`
                WHEN 'power-tx' THEN 200 + `transceiver_metrics`.`channel`
                WHEN 'temperature' THEN 300 + `transceiver_metrics`.`channel`
                WHEN 'bias' THEN 400 + `transceiver_metrics`.`channel`
                WHEN 'voltage' THEN 500 + `transceiver_metrics`.`channel`
                ELSE 900 + `transceiver_metrics`.`channel`
                END)");
        });
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn (int|null $status) => TransceiverMetricStatus::tryFrom($status) ?? TransceiverMetricStatus::Unknown,
            set: fn (TransceiverMetricStatus $status) => $status->value,
        );
    }

    public function hasThresholds(): bool
    {
        return isset($this->attributes['threshold_min_critical']) || isset($this->attributes['threshold_max_critical']) || isset($this->attributes['threshold_min_warning']) || isset($this->attributes['threshold_max_warning']);
    }

    public function transceiver(): BelongsTo
    {
        return $this->belongsTo(Transceiver::class);
    }

    public function getCompositeKey(): string
    {
        return $this->transceiver_id . '|' . $this->channel . '|' . $this->type;
    }
}
