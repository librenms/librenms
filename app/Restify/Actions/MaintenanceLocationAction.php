<?php

namespace App\Restify\Actions;

use App\Models\Location;
use App\Restify\Actions\Concerns\SchedulesMaintenance;
use Binaryk\LaravelRestify\Actions\Action;
use Binaryk\LaravelRestify\Http\Requests\ActionRequest;
use Illuminate\Http\JsonResponse;

class MaintenanceLocationAction extends Action
{
    use SchedulesMaintenance;

    public static string $uriKey = 'maintenance';

    public string $description = 'Place every device at this location into a scheduled maintenance window.';

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return $this->maintenanceRules();
    }

    public function handle(ActionRequest $request, Location $location): JsonResponse
    {
        $schedule = $this->createMaintenanceSchedule(
            $location,
            'locations',
            $location->location,
            $this->validateMaintenancePayload($request),
        );

        return response()->json([
            'data' => [
                'schedule_id' => $schedule->schedule_id,
                'location_id' => $location->id,
                'start' => $schedule->start->format('Y-m-d H:i:s'),
                'end' => $schedule->end?->format('Y-m-d H:i:s'),
                'message' => "Location {$location->location} placed into maintenance",
            ],
        ], 201);
    }
}
