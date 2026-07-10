<?php

namespace App\Http\Controllers\Table;

use App\Http\Controllers\Table\Traits\SensorTrait;
use App\Models\Sensor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use LibreNMS\Enum\SensorState;

/**
 * @extends TableController<Sensor>
 */
class SensorsController extends TableController
{
    /** @use SensorTrait<Sensor> */
    use SensorTrait;

    protected ?string $model = Sensor::class;

    protected array $default_sort = ['device_hostname' => 'asc', 'sensor_descr' => 'asc'];

    protected function rules(): array
    {
        return [
            'view' => Rule::in(['detail', 'graphs']),
            'class' => Rule::in(array_merge(\LibreNMS\Enum\Sensor::values(), ['all'])),
            'status' => 'nullable|string',
        ];
    }

    protected function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', Sensor::class);

        $class = $request->input('class');
        $status = $request->input('status');
        $relations = [];
        if ($class == 'state' || $class == 'all') {
            $relations[] = 'translations';
        }

        return Sensor::query()
            ->hasAccess($request->user())
            ->when($request->input('searchPhrase'), fn ($q) => $q->leftJoin('devices', 'devices.device_id', '=', 'sensors.device_id'))
            ->when($class != 'all', fn ($q) => $q->where('sensor_class', $class))
            ->with($relations)
            ->withAggregate('device', 'hostname')
            ->when($status == 'unknown', fn ($q) => (new Sensor)->scopeStateUnknown($q))
            ->when($status == 'alert', fn ($q) => $q->where('sensor_alert', 1))
            ->when(in_array($status, ['alert', 'error']), function ($q): void {
                $q->where(function ($q): void {
                    (new Sensor)->scopeIsCritical($q)
                        ->orWhere(fn ($q) => (new Sensor)->scopeStateEq($q, SensorState::Error));
                });
            })
            ->when($status == 'warning', function ($q): void {
                $q->where(function ($q): void {
                    (new Sensor)->scopeStateEq($q, SensorState::Warning)
                        ->orWhere(function ($q): void {
                            $q->whereNot(fn ($q) => (new Sensor)->scopeIsCritical($q));
                            (new Sensor)->scopeIsWarning($q);
                        });
                });
            });
    }
}
