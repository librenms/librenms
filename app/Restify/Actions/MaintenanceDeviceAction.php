<?php

namespace App\Restify\Actions;

use App\Models\Device;
use App\Restify\Actions\Concerns\SchedulesMaintenance;
use Binaryk\LaravelRestify\Actions\Action;
use Binaryk\LaravelRestify\Http\Requests\ActionRequest;
use Illuminate\Http\JsonResponse;

class MaintenanceDeviceAction extends Action
{
    use SchedulesMaintenance;

    public static string $uriKey = 'maintenance';

    public string $description = 'Place this device into a scheduled maintenance window.';

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return $this->maintenanceRules();
    }

    public function handle(ActionRequest $request, Device $device): JsonResponse
    {
        $schedule = $this->createMaintenanceSchedule(
            $device,
            'devices',
            $device->displayName(),
            $this->validateMaintenancePayload($request),
        );

        return response()->json([
            'data' => [
                'schedule_id' => $schedule->schedule_id,
                'device_id' => $device->device_id,
                'start' => $schedule->start->format('Y-m-d H:i:s'),
                'end' => $schedule->end?->format('Y-m-d H:i:s'),
                'message' => "Device {$device->hostname} placed into maintenance",
            ],
        ], 201);
    }
}
