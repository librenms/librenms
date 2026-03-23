<?php

/**
 * Zabbix-style alert rule operation (problem / recovery / update operations).
 *
 * @see https://www.zabbix.com/documentation/current/en/manual/config/notifications/action/operation
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AlertRuleOperation extends BaseModel
{
    public $timestamps = false;

    protected $fillable = [
        'rule_id',
        'position',
        'operation_phase',
        'escalation_step_from',
        'escalation_step_to',
        'start_in_seconds',
        'step_duration_seconds',
    ];

    /**
     * @return BelongsTo<AlertRule, $this>
     */
    public function alertRule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class, 'rule_id');
    }

    /**
     * @return BelongsToMany<AlertTransport, $this>
     */
    public function transportSingles(): BelongsToMany
    {
        return $this->belongsToMany(AlertTransport::class, 'alert_rule_operation_transport_map', 'operation_id', 'transport_or_group_id')
            ->withPivot('target_type')
            ->wherePivot('target_type', 'single');
    }

    /**
     * @return BelongsToMany<AlertTransportGroup, $this>
     */
    public function transportGroups(): BelongsToMany
    {
        return $this->belongsToMany(AlertTransportGroup::class, 'alert_rule_operation_transport_map', 'operation_id', 'transport_or_group_id')
            ->withPivot('target_type')
            ->wherePivot('target_type', 'group');
    }
}
