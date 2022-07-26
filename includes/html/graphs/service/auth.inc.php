<?php

if (isset($vars['id']) && is_numeric($vars['id'])) {
    $service = \App\Models\Service::hasAccess(Auth::user())->with('device')->find($vars['id']);

    if ($service) {
        $rrd_filename = Rrd::name($service->device->hostname, ['services', $service->service_id]);

        $title = \LibreNMS\Util\Url::deviceLink($service->device);
        $title .= ' :: Service :: ' . htmlentities($service->service_type);
        if (isset($service->service_desc)) {
            $title .= ' - ' . htmlentities($service->service_desc);
        }
        $auth = true;
    }
}
