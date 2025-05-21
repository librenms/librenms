<?php

namespace App\Http\Controllers;

use App\Models\WirelessSensor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class WirelessSensorController
{
    public function index(Request $request, string $metric = '', string $legacyview = ''): View
    {
        $metric = str_replace('metric=', '', $metric);
        $view = str_replace('view=', '', $legacyview) ?: $request->get('view', 'detail');
        $metrics = $this->getMetrics($request);
        $metric = $metric ?: array_key_first($metrics);

        if (! array_key_exists($metric, $metrics)) {
            abort(404);
        }

        $title = 'Wireless :: ' . __('wireless.' . $metric . '.short');
        $views = [
            'graphs' => ['text' => __('Graphs'), 'link' => $request->fullUrlWithQuery(['view' => 'graphs'])],
            'detail' => ['text' => __('No Graphs'), 'link' => $request->fullUrlWithoutQuery('view')],
        ];

        return view('wireless-sensor.index', [
            'title' => $title,
            'metrics' => $metrics,
            'metric' => $metric,
            'views' => $views,
            'view' => $view,
        ]);
    }

    /**
     * @return array<array<string, string>>
     */
    private function getMetrics(Request $request): array
    {
        $types = \LibreNMS\Device\WirelessSensor::getTypes();

        return WirelessSensor::distinct()->pluck('sensor_class')->flip()->map(function ($index, $class) use ($types, $request) {
            return [
                'text' => __("wireless.$class.short"),
                'link' => route('wireless.index', $request->all() + ['metric' => $class]),
                'icon' => 'fa-' . $types[$class]['icon'],
            ];
        })->all();
    }
}
