<?php

namespace App\Restify\Actions;

use App\Models\DeviceGroup;
use App\Restify\Actions\Concerns\SchedulesMaintenance;
use Binaryk\LaravelRestify\Actions\Action;
use Binaryk\LaravelRestify\Http\Requests\ActionRequest;
use Illuminate\Http\JsonResponse;

class MaintenanceDeviceGroupAction extends Action
{
    use SchedulesMaintenance;

    public static string $uriKey = 'maintenance';

    public string $description = 'Place every device in this group into a scheduled maintenance window.';

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return $this->maintenanceRules();
    }

    public function handle(ActionRequest $request, DeviceGroup $group): JsonResponse
    {
        $schedule = $this->createMaintenanceSchedule(
            $group,
            'deviceGroups',
            $group->name,
            $this->validateMaintenancePayload($request),
        );

        return response()->json([
            'data' => [
                'schedule_id' => $schedule->schedule_id,
                'device_group_id' => $group->id,
                'start' => $schedule->start->format('Y-m-d H:i:s'),
                'end' => $schedule->end?->format('Y-m-d H:i:s'),
                'message' => "Device group {$group->name} placed into maintenance",
            ],
        ], 201);
    }
}
