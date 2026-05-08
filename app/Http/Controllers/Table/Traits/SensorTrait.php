<?php

/**
 * SensorTrait.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Table\Traits;

use App\Models\SensorModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use LibreNMS\Enum\Severity;
use LibreNMS\Util\Html;
use LibreNMS\Util\Url;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @template TModel of SensorModel
 */
trait SensorTrait
{
    protected function sortFields(Request $request): array
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

    /**
     * @param  TModel  $model
     * @return array<string, scalar>
     */
    public function formatItem(Model $model): array
    {
        $request = \Illuminate\Support\Facades\Request::instance();
        $graph_array = [
            'type' => $model->getGraphType(),
            'popup_title' => htmlentities(strip_tags($model->device?->displayName() . ': ' . $model->sensor_descr)),
            'id' => $model->sensor_id,
            'from' => '-1d',
            'height' => 20,
            'width' => 80,
        ];

        $hostname = Blade::render('<x-device-link :device="$device" />', ['device' => $model->device]);
        $sensor_class = $model->sensor_class instanceof \BackedEnum ? $model->sensor_class->value : $model->sensor_class;
        $link = Url::generate(['page' => 'device', 'device' => $model['device_id'], 'tab' => 'health', 'metric' => $sensor_class]);
        $descr = Url::graphPopup($graph_array, $model->sensor_descr, $link);
        $mini_graph = Url::graphPopup($graph_array);
        $sensor_current = Html::severityToLabel($model->currentStatus(), $model->formatValue());
        $alert = $model->currentStatus() == Severity::Error ? '<i class="fa fa-flag fa-lg" style="color:red" aria-hidden="true"></i>' : '';

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
            'sensor_limit_low' => Html::severityToLabel(Severity::Unknown, $model->formatValue('sensor_limit_low')),
            'sensor_limit' => Html::severityToLabel(Severity::Unknown, $model->formatValue('sensor_limit')),
        ];
    }

    /**
     * Get headers for CSV export
     */
    protected function getExportHeaders(): array
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
     * @param  TModel  $sensor
     * @return array
     */
    protected function formatExportRow(Model $sensor): array
    {
        return [
            $sensor->device ? $sensor->device->displayName() : '',
            $sensor->sensor_descr,
            $sensor->formatValue(),
            $sensor->formatValue('sensor_limit_low'),
            $sensor->formatValue('sensor_limit'),
            $sensor->sensor_class instanceof \BackedEnum ? $sensor->sensor_class->value : $sensor->sensor_class,
            $sensor->sensor_type,
        ];
    }

    /**
     * Export data as CSV with sensor class filter
     */
    public function export(Request $request, ?string $class = null): StreamedResponse
    {
        if ($class) {
            $request->merge(['class' => $class]);
        }

        $this->validate($request, $this->rules());

        return parent::export($request);
    }
}
