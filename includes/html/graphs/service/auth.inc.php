<?php

if (is_numeric($vars['id'] ?? null)) {
    $service = \App\Models\Service::with(['device' => fn ($q) => $q->select(['device_id', 'hostname'])])->find($vars['id']);

    if (is_numeric($service->device_id) && ($auth || device_permitted($service->device_id))) {
        $title = ' :: Service :: ' . $service->service_type . ' - ' . $service->service_desc;
        $auth = true;
    }
}
