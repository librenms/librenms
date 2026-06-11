<?php

namespace App\Restify\Actions\Concerns;

use App\Facades\LibrenmsConfig;
use App\Models\AlertSchedule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use LibreNMS\Enum\MaintenanceBehavior;

/**
 * Shared logic for the device / device-group / location maintenance actions.
 * Mirrors `maintenance_*` functions in `includes/html/api_functions.inc.php`.
 */
trait SchedulesMaintenance
{
    /**
     * Restify's `Action` base class does not auto-run `rules()`; do it manually
     * so the action enforces its own contract.
     *
     * @return array<string, mixed>
     */
    private function validateMaintenancePayload(Request $request): array
    {
        return Validator::make($request->all(), $this->maintenanceRules())->validate();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function createMaintenanceSchedule(Model $target, string $relation, string $defaultTitle, array $payload): AlertSchedule
    {
        $behavior = MaintenanceBehavior::tryFrom((int) ($payload['behavior'] ?? -1))
            ?? LibrenmsConfig::get('alert.scheduled_maintenance_default_behavior');

        $start = $payload['start'] ?? Carbon::now()->format('Y-m-d H:i:00');

        $schedule = new AlertSchedule([
            'title' => $payload['title'] ?? $defaultTitle,
            'notes' => $payload['notes'] ?? '',
            'behavior' => $behavior,
            'recurring' => 0,
        ]);
        $schedule->start = $start;

        $duration = (string) ($payload['duration'] ?? '');
        if (str_contains($duration, ':')) {
            [$hours, $minutes] = explode(':', $duration);
            $schedule->end = Carbon::createFromFormat('Y-m-d H:i:s', $start)
                ->addHours((float) $hours)
                ->addMinutes((float) $minutes)
                ->format('Y-m-d H:i:00');
        }

        $schedule->save();
        $schedule->{$relation}()->attach($target);

        return $schedule;
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function maintenanceRules(): array
    {
        return [
            'duration' => ['required', 'string', 'regex:/^\\d+:\\d{1,2}$/'],
            'notes' => ['nullable', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
            'start' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'behavior' => ['nullable', 'integer'],
        ];
    }
}
