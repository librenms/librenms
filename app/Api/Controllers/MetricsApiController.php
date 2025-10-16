<?php

namespace App\Api\Controllers;

use Illuminate\Http\Request;

class MetricsApiController
{
    public function devices(Request $request)
    {
    // Forward to the MetricsApi controller for implementation (resolve instance to avoid static call)
    return app()->call([app(\App\Api\Controllers\MetricsApi\Controller::class), 'devices'], ['request' => $request]);
    }

    public function accessPoints(Request $request)
    {
    // Forward to the MetricsApi controller for implementation (resolve instance to avoid static call)
    return app()->call([app(\App\Api\Controllers\MetricsApi\Controller::class), 'accessPoints'], ['request' => $request]);
    }

    public function ports(Request $request)
    {
    // Forward to the MetricsApi controller for implementation (resolve instance to avoid static call)
    return app()->call([app(\App\Api\Controllers\MetricsApi\Controller::class), 'ports'], ['request' => $request]);
    }
}
