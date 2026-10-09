<?php

namespace App\Http\Controllers;

use App\Http\Requests\AlertScheduleRequest;
use App\Models\AlertSchedule;
use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\Location;
use App\Models\UserPref;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AlertScheduleController extends Controller
{
    public function show(AlertSchedule $alertSchedule): JsonResponse
    {
        $this->authorize('view', $alertSchedule);

        $targets = $alertSchedule->devices()->get()
            ->map(fn (Device $device) => ['id' => $device->device_id, 'text' => $device->display])
            ->concat($alertSchedule->deviceGroups()->get()
                ->map(fn (DeviceGroup $group) => ['id' => 'g' . $group->id, 'text' => $group->name]))
            ->concat($alertSchedule->locations()->get()
                ->map(fn (Location $location) => ['id' => 'l' . $location->id, 'text' => $location->location]));

        return response()->json(array_merge($alertSchedule->toArray(), [
            'targets' => $targets->values(),
        ]));
    }

    public function store(AlertScheduleRequest $request): JsonResponse
    {
        $alertSchedule = $this->save($request, new AlertSchedule);

        return response()->json([
            'message' => __('Maintenance scheduled'),
            'schedule_id' => $alertSchedule->schedule_id,
        ], 201);
    }

    public function update(AlertScheduleRequest $request, AlertSchedule $alertSchedule): JsonResponse
    {
        $this->save($request, $alertSchedule);

        return response()->json([
            'message' => __('Maintenance updated'),
            'schedule_id' => $alertSchedule->schedule_id,
        ]);
    }

    public function end(AlertSchedule $alertSchedule): JsonResponse
    {
        $this->authorize('update', $alertSchedule);

        $alertSchedule->end = Carbon::now();
        $alertSchedule->save();

        return response()->json([
            'message' => __('Maintenance has been ended'),
        ]);
    }

    public function destroy(AlertSchedule $alertSchedule): JsonResponse
    {
        $this->authorize('delete', $alertSchedule);

        DB::transaction(function () use ($alertSchedule): void {
            $alertSchedule->devices()->detach();
            $alertSchedule->deviceGroups()->detach();
            $alertSchedule->locations()->detach();
            $alertSchedule->delete();
        });

        return response()->json([
            'message' => __('Maintenance schedule has been removed'),
        ]);
    }

    private function save(AlertScheduleRequest $request, AlertSchedule $alertSchedule): AlertSchedule
    {
        $validated = $request->validated();

        $alertSchedule->fill([
            'title' => $validated['title'],
            'notes' => $validated['notes'] ?? '',
            'behavior' => (int) $validated['behavior'],
            'recurring' => (int) $validated['recurring'],
        ]);

        if ($validated['recurring']) {
            $alertSchedule->start = Carbon::parse($validated['start_recurring_dt'] . ' ' . $validated['start_recurring_hr']);
            $alertSchedule->end = Carbon::parse(($validated['end_recurring_dt'] ?? '9000-09-09') . ' ' . $validated['end_recurring_hr']);
            $alertSchedule->recurring_day = empty($validated['recurring_day']) ? null : $validated['recurring_day'];
        } else {
            $start = Carbon::parse($validated['start']);
            if (! empty($validated['duration'])) {
                [$hours, $minutes] = explode(':', (string) $validated['duration']);
                $end = $start->copy()->addHours((int) $hours)->addMinutes((int) $minutes);
            } else {
                $end = Carbon::parse($validated['end']);
            }

            $alertSchedule->start = $start;
            $alertSchedule->end = $end;
            $alertSchedule->recurring_day = null;
        }

        [$devices, $groups, $locations] = $this->parseTargets($validated['maps']);

        DB::transaction(function () use ($alertSchedule, $devices, $groups, $locations): void {
            $alertSchedule->save();
            $alertSchedule->devices()->sync($devices);
            $alertSchedule->deviceGroups()->sync($groups);
            $alertSchedule->locations()->sync($locations);
        });

        if ($alertSchedule->notes && UserPref::getPref($request->user(), 'add_schedule_note_to_device')) {
            $this->addNoteToDevices($devices, $alertSchedule->notes);
        }

        return $alertSchedule;
    }

    /**
     * Split prefixed select2 ids into device, device group, and location ids
     *
     * @param  string[]  $maps
     * @return array{int[], int[], int[]}
     */
    private function parseTargets(array $maps): array
    {
        $devices = [];
        $groups = [];
        $locations = [];

        foreach ($maps as $target) {
            if (str_starts_with($target, 'g')) {
                $groups[] = (int) substr($target, 1);
            } elseif (str_starts_with($target, 'l')) {
                $locations[] = (int) substr($target, 1);
            } else {
                $devices[] = (int) $target;
            }
        }

        return [$devices, $groups, $locations];
    }

    /**
     * @param  int[]  $device_ids
     */
    private function addNoteToDevices(array $device_ids, string $notes): void
    {
        $note = Carbon::now()->format('Y-m-d H:i') . ' Alerts delayed: ' . $notes;

        Device::whereIntegerInRaw('device_id', $device_ids)->get()->each(function (Device $device) use ($note): void {
            $device->notes = empty($device->notes) ? $note : $device->notes . PHP_EOL . $note;
            $device->save();
        });
    }
}
