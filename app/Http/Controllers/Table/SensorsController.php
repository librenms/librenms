<?php

namespace App\Http\Controllers\Table;

use App\Models\Sensor;
use App\Models\WirelessSensor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Validation\Rule;
use LibreNMS\Enum\Severity;
use LibreNMS\Util\Html;
use LibreNMS\Util\Url;

class SensorsController extends TableController
{
    protected $model = Sensor::class;

    protected $default_sort = ['device_hostname' => 'asc', 'sensor_descr' => 'asc'];

    protected function rules(): array
    {
        return [
            'view' => Rule::in(['detail', 'graphs']),
            'class' => Rule::in(array_merge(\LibreNMS\Enum\Sensor::values(), ['all'])),
            'status' => 'nullable|string',
        ];
    }

    protected function sortFields($request): array
    {
        return [
            'device_hostname',
            'sensor_descr',
            'sensor_current',
            'sensor_limit_low',
            'sensor_limit',
        ];
    }

    protected function searchFields(Request $request): array
    {
        return [
            'hostname',
            'display',
            'sensor_descr',
            'sensor_current',
        ];
    }

    protected function baseQuery(Request $request): Builder
    {
        $class = $request->input('class');
        $status = $request->input('status');
        $relations = [];
        if ($class == 'state' || $class == 'all') {
            $relations[] = 'translations';
        }

        $query = Sensor::query()
            ->hasAccess($request->user())
            ->when($request->get('searchPhrase'), fn ($q) => $q->leftJoin('devices', 'devices.device_id', '=', 'sensors.device_id'))
            ->with($relations)
            ->withAggregate('device', 'hostname');

        if($status != "") {
            $query->leftJoin('sensors_to_state_indexes', 'sensors_to_state_indexes.sensor_id', '=', 'sensors.sensor_id')
            ->leftJoin('state_translations', function ($j): void {
                $j->on( 'sensors_to_state_indexes.state_index_id', '=', 'state_translations.state_index_id')
                ->on( 'sensors.sensor_current', '=', 'state_translations.state_value');
            });
        }

        if($class != "all") {
            $query->where('sensor_class', $class);
        }

        switch($status) {
            case "unknown":
                $query->whereRaw('state_translations.state_generic_value = 3)');
                break;
            case "alert":
                $query->where('sensor_alert', 1);
            case "error":
                $query->whereRaw('(sensor_current < sensor_limit_low OR sensor_current > sensor_limit OR state_translations.state_generic_value = 2)');
                break;
            case "warning":
                $query->whereRaw('((sensor_current > sensor_limit_low AND sensor_current < sensor_limit'
                  . ' AND (sensor_current < sensor_limit_low_warn OR sensor_current > sensor_limit_warn)'
                  . ' OR state_translations.state_generic_value = 1))');
        }
        return $query;
    }

    /**
     * @param  Sensor|WirelessSensor  $sensor
     */
    public function formatItem($sensor): array
    {
        $request = \Illuminate\Support\Facades\Request::instance();
        $graph_array = [
            'type' => $sensor->getGraphType(),
            'popup_title' => htmlentities(strip_tags($sensor->device?->displayName() . ': ' . $sensor->sensor_descr)),
            'id' => $sensor->sensor_id,
            'from' => '-1d',
            'height' => 20,
            'width' => 80,
        ];

        $hostname = Blade::render('<x-device-link :device="$device" />', ['device' => $sensor->device]);
        $link = Url::generate(['page' => 'device', 'device' => $sensor['device_id'], 'tab' => 'health', 'metric' => $sensor->sensor_class]);
        $descr = Url::graphPopup($graph_array, $sensor->sensor_descr, $link);
        $mini_graph = Url::graphPopup($graph_array);
        $sensor_current = Html::severityToLabel($sensor->currentStatus(), $sensor->formatValue());
        $alert = $sensor->currentStatus() == Severity::Error ? '<i class="fa fa-flag fa-lg" style="color:red" aria-hidden="true"></i>' : '';

        // show graph row inline
        if ($request->input('view') == 'graphs') {
            $row = Html::graphRow(array_replace($graph_array, ['height' => 100, 'width' => 210]));
            $hostname = '<div class="tw:border-b tw:border-gray-200">' . $hostname . '</div><div style="width: 210px; margin-left: auto;">' . $row[0] . '</div>';
            $descr = '<div class="tw:border-b tw:border-gray-200">' . $descr . '</div><div style="width: 210px">' . $row[1] . '</div>';
            $mini_graph = '<div class="tw:border-b tw:border-gray-200" style="min-height:20px">' . $mini_graph . '</div><div style="width: 210px">' . $row[2] . '</div>';
            $sensor_current = '<div class="tw:border-b tw:border-gray-200">' . $sensor_current . '</div><div style="width: 210px">' . $row[3] . '</div>';
        }

        return [
            'device_hostname' => $hostname,
            'sensor_descr' => $descr,
            'graph' => $mini_graph,
            'alert' => $alert,
            'sensor_current' => $sensor_current,
            'sensor_limit_low' => Html::severityToLabel(Severity::Unknown, $sensor->formatValue('sensor_limit_low')),
            'sensor_limit' => Html::severityToLabel(Severity::Unknown, $sensor->formatValue('sensor_limit')),
        ];
    }

    /**
     * Get headers for CSV export
     *
     * @return array
     */
    protected function getExportHeaders()
    {
        return [
            'Device Hostname',
            'Sensor',
            'Current',
            'Limit Low',
            'Limit High',
            'Sensor Class',
            'Sensor Type',
        ];
    }

    /**
     * Format a row for CSV export
     *
     * @param  Sensor  $sensor
     * @return array
     */
    protected function formatExportRow($sensor)
    {
        return [
            $sensor->device ? $sensor->device->displayName() : '',
            $sensor->sensor_descr,
            $sensor->formatValue(),
            $sensor->formatValue('sensor_limit_low'),
            $sensor->formatValue('sensor_limit'),
            $sensor->sensor_class,
            $sensor->sensor_type,
        ];
    }

    /**
     * Export data as CSV with sensor class filter
     *
     * @param  Request  $request
     * @param  string|null  $class
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request, $class = null)
    {
        if ($class) {
            $request->merge(['class' => $class]);
        }

        $this->validate($request, $this->rules());

        return parent::export($request);
    }
}
