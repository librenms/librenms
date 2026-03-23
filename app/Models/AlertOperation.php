<?php

namespace App\Models;

/**
 * Named alert operation (Zabbix-style): contains one or more {@see AlertOperationSegment} rows
 * (escalation ranges, timing, transports per segment).
 *
 * Assigned to alert rules via alert_rules.alert_operation_id.
 *
 * @see https://www.zabbix.com/documentation/current/en/manual/config/notifications/action/operation
 */

use Illuminate\Database\Eloquent\Relations\HasMany;

class AlertOperation extends BaseModel
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'default_operation_step_duration_seconds',
    ];

    protected $casts = [
        'default_operation_step_duration_seconds' => 'integer',
    ];

    /**
     * @return HasMany<AlertOperationSegment, $this>
     */
    public function segments(): HasMany
    {
        return $this->hasMany(AlertOperationSegment::class, 'alert_operation_id')->orderBy('position')->orderBy('id');
    }

    /**
     * Rules using this operation (for delete guard).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\AlertRule, $this>
     */
    public function alertRules(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AlertRule::class, 'alert_operation_id');
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        $this->loadMissing([
            'segments.transportSingles:alert_transports.transport_id,transport_type,transport_name',
            'segments.transportGroups:alert_transport_groups.transport_group_id,transport_group_name',
        ]);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'default_operation_step_duration_seconds' => $this->default_operation_step_duration_seconds,
            'segments' => $this->segments->map(static fn (AlertOperationSegment $s) => $s->toApiArray())->values()->all(),
        ];
    }
}
