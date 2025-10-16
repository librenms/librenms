<?php

namespace App\Api\Controllers;

use Illuminate\Http\Request;
use App\Api\Controllers\MetricsApi\DevicesMetrics;
use App\Api\Controllers\MetricsApi\AccessPointsMetrics;
use App\Api\Controllers\MetricsApi\PortsMetrics;
use App\Api\Controllers\MetricsApi\PortsStatisticsMetrics;

class MetricsApiController
{
    public function devices(Request $request)
    {
        $body = app(DevicesMetrics::class)->render($request);
        return response($body, 200, ['Content-Type' => 'text/plain; version=0.0.4; charset=utf-8']);
    }

    public function accessPoints(Request $request)
    {
        $body = app(AccessPointsMetrics::class)->render($request);
        return response($body, 200, ['Content-Type' => 'text/plain; version=0.0.4; charset=utf-8']);
    }

    public function ports(Request $request)
    {
        $body = app(PortsMetrics::class)->render($request);
        return response($body, 200, ['Content-Type' => 'text/plain; version=0.0.4; charset=utf-8']);
    }

    public function portsStatistics(Request $request)
    {
        $body = app(PortsStatisticsMetrics::class)->render($request);
        return response($body, 200, ['Content-Type' => 'text/plain; version=0.0.4; charset=utf-8']);
    }
}
