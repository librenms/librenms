<?php

namespace App\Http\Controllers\Table;

use App\Models\Sensor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Validation\Rule;
use LibreNMS\Enum\Severity;
use LibreNMS\Util\Html;
use LibreNMS\Util\Number;
use LibreNMS\Util\Url;

class SensorsController extends TableController
{
    protected $default_sort = ['device_hostname' => 'asc', 'sensor_descr' => 'asc'];

    protected function rules(): array
    {
        return [
            'view' => Rule::in(['detail', 'graphs']),
            'class' => Rule::in(Sensor::getTypes()),
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
            'device_hostname',
            'sensor_descr',
            'sensor_current',
            'sensor_group',
        ];
    }

    protected function baseQuery(Request $request): Builder
    {
        $class = $request->input('class');
        $relations = ['device', 'device.location'];
        if ($class == 'state') {
            $relations[] = 'translations';
        }

        return Sensor::query()
            ->hasAccess($request->user())
            ->where('sensor_class', $class)
            ->with($relations)
            ->withAggregate('device', 'hostname');
    }

    /**
     * @param  Sensor  $sensor
     */
    public function formatItem($sensor): array
    {
        $request = \Illuminate\Support\Facades\Request::instance();
        $graph_type = 'sensor_' . $request->input('class');
        $graph_array = [
            'type' => $graph_type,
            'popup_title' => htmlentities(strip_tags($sensor->device->displayName() . ': ' . $sensor->sensor_descr)),
            'id' => $sensor->sensor_id,
            'from' => '-1d',
            'height' => 20,
            'width' => 80,
        ];

        $hostname = Blade::render('<x-device-link :device="$device" />', ['device' => $sensor->device]);
        $link = Url::generate(['page' => 'device', 'device' => $sensor['device_id'], 'tab' => 'health', 'metric' => $sensor->sensor_class]);
        $descr = Url::graphPopup($graph_array, $sensor->sensor_descr, $link);
        $mini_graph = Url::graphPopup($graph_array);
        $sensor_current = $sensor->sensor_current === null ? 'NaN' : Html::severityToLabel($sensor->currentStatus(), $sensor->formatValue());
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
            'sensor_limit_low' => is_null($sensor->sensor_limit_low) ? '-' :
                '<span class=\'label label-default\'>' . trim(Number::formatSi($sensor->sensor_limit_low, 2, 0, '') . $sensor->unit()) . '</span>',
            'sensor_limit' => is_null($sensor->sensor_limit) ? '-' :
                '<span class=\'label label-default\'>' . trim(Number::formatSi($sensor->sensor_limit, 2, 0, '') . $sensor->unit()) . '</span>',

        ];
    }
}
