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
            $message = htmlentities(str_replace(' ', '&nbsp;', (string) $service->service_message));
            $color = $colors->get($service->service_status, 'default');
            $type = htmlentities(strtolower((string) $service->service_type));
            $name = htmlentities((string) $service->service_name);
            $name_type = ($name == '' || $name == $type) ? $type : $name . ' (' . $type . ')';

            return "<span class='label label-$color' title='$message'>$name_type</span>";
        })->implode(' ');

    $services = ObjectCache::serviceCounts(['total', 'ok', 'warning', 'critical'], $device['device_id']);
    echo '<div class="overview-panel tw:mb-5">
                    <div class="overview-panel-heading">
                        <a href="' . Url::deviceUrl($device['device_id'], ['tab' => 'services']) . '"><i class="fa fa-cogs fa-lg icon-theme" aria-hidden="true"></i> <strong>Services</strong></a>
                    </div>
                    <div class="tw:flex tw:flex-wrap tw:gap-3 tw:p-3">
                        <a class="lnms-btn lnms-btn-default" role="button" href="' . Url::deviceUrl($device['device_id'], ['tab' => 'services']) . '">Total: <span class="lnms-btn-badge">' . $services['total'] . '</span></a>
                        <a class="lnms-btn lnms-btn-success" role="button" href="' . Url::deviceUrl($device['device_id'], ['tab' => 'services']) . '">Ok: <span class="lnms-btn-badge">' . $services['ok'] . '</span></a>
                        <a class="lnms-btn lnms-btn-warning" role="button" href="' . Url::deviceUrl($device['device_id'], ['tab' => 'services']) . '">Warning: <span class="lnms-btn-badge">' . $services['warning'] . '</span></a>
                        <a class="lnms-btn lnms-btn-danger" role="button" href="' . Url::deviceUrl($device['device_id'], ['tab' => 'services']) . '">Critical: <span class="lnms-btn-badge">' . $services['critical'] . '</span></a>
                    </div>
                    <div class="overview-panel-footer">' . $output . '</div>
                </div>';
}
