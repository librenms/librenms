<?php

use LibreNMS\Util\ObjectCache;
use LibreNMS\Util\Url;

if (ObjectCache::serviceCounts(['total'], $device['device_id'])['total'] > 0) {
    $colors = new \Illuminate\Support\Collection(['success', 'warning', 'danger']);
    $output = \App\Models\Service::query()
        ->where('device_id', $device['device_id'])
        ->orderBy('service_type')
        ->get(['service_type', 'service_status', 'service_message', 'service_name'])
        ->map(function ($service) use ($colors) {
            $message = htmlentities(str_replace(' ', '&nbsp;', $service->service_message));
            $color = $colors->get($service->service_status, 'default');
            $type = htmlentities(strtolower((string) $service->service_type));
            $name = htmlentities((string) $service->service_name);
            $name_type = ($name == '' || $name == $type) ? $type : $name . ' (' . $type . ')';

            return "<span class='label label-$color' title='$message'>$name_type</span>";
        })->implode(' ');

    $services = ObjectCache::serviceCounts(['total', 'ok', 'warning', 'critical'], $device['device_id']);
    echo '<div class="row">
            <div class="col-md-12">
                <div class="panel panel-default panel-condensed">
                    <div class="panel-heading">
                        <a href="' . Url::deviceUrl($device['device_id'], ['tab' => 'services']) . '"><i class="fa fa-cogs fa-lg icon-theme" aria-hidden="true"></i> <strong>Services</strong></a>
                    </div>
                    <div class="panel-body">
                        <a class="btn btn-default" role="button" href="' . Url::deviceUrl($device['device_id'], ['tab' => 'services']) . '">Total: <span class="badge">' . $services['total'] . '</span></a>
                        <a class="btn btn-success" role="button" href="' . Url::deviceUrl($device['device_id'], ['tab' => 'services']) . '">Ok: <span class="badge">' . $services['ok'] . '</span></a>
                        <a class="btn btn-warning" role="button" href="' . Url::deviceUrl($device['device_id'], ['tab' => 'services']) . '">Warning: <span class="badge">' . $services['warning'] . '</span></a>
                        <a class="btn btn-danger" role="button" href="' . Url::deviceUrl($device['device_id'], ['tab' => 'services']) . '">Critical: <span class="badge">' . $services['critical'] . '</span></a>
                    </div>
                    <div class="panel-footer">' . $output . '</div>
                </div>
            </div>
        </div>';
}
