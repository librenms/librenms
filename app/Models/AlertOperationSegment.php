<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * One escalation row / transport mapping inside a named {@see AlertOperation}.
 *
 * @property int $id
 * @property int $alert_operation_id
 * @property int $position
 * @property string $operation_phase
 * @property int $escalation_step_from
 * @property int|null $escalation_step_to
 * @property int $start_in_seconds
 * @property int $step_duration_seconds
 * @property bool $notifications_suppressed
 */
class AlertOperationSegment extends BaseModel
{
    public $timestamps = false;

    protected $table = 'alert_operation_segments';

    protected $fillable = [
        'alert_operation_id',
        'position',
        'operation_phase',
        'escalation_step_from',
        'escalation_step_to',
        'start_in_seconds',
        'step_duration_seconds',
        'notifications_suppressed',
    ];

    protected $casts = [
        'notifications_suppressed' => 'boolean',
    ];

    /**
     * @return BelongsTo<AlertOperation, $this>
     */
    public function alertOperation(): BelongsTo
    {
        return $this->belongsTo(AlertOperation::class, 'alert_operation_id');
    }

    /**
     * @return BelongsToMany<AlertTransport, $this>
     */
    public function transportSingles(): BelongsToMany
    {
        return $this->belongsToMany(AlertTransport::class, 'alert_operation_transport_map', 'segment_id', 'transport_or_group_id')
            ->withPivot('target_type')
            ->wherePivot('target_type', 'single');
    }

    /**
     * @return BelongsToMany<AlertTransportGroup, $this>
     */
    public function transportGroups(): BelongsToMany
    {
        return $this->belongsToMany(AlertTransportGroup::class, 'alert_operation_transport_map', 'segment_id', 'transport_or_group_id')
            ->withPivot('target_type')
            ->wherePivot('target_type', 'group');
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        $this->loadMissing([
            'transportSingles:alert_transports.transport_id,transport_type,transport_name',
            'transportGroups:alert_transport_groups.transport_group_id,transport_group_name',
        ]);

        $transports = [];
        foreach ($this->transportSingles as $transport) {
            $transports[] = [
                'id' => (string) $transport->transport_id,
                'text' => ucfirst((string) $transport->transport_type) . ': ' . $transport->transport_name,
            ];
        }
        foreach ($this->transportGroups as $group) {
            $transports[] = [
                'id' => 'g' . $group->transport_group_id,
                'text' => 'Group: ' . $group->transport_group_name,
            ];
        }

        return [
            'id' => $this->id,
            'position' => $this->position,
            'operation_phase' => $this->operation_phase,
            'escalation_step_from' => $this->escalation_step_from,
            'escalation_step_to' => $this->escalation_step_to,
            'start_in_seconds' => $this->start_in_seconds,
            'step_duration_seconds' => $this->step_duration_seconds,
            'transports' => $transports,
        ];
    }
}
