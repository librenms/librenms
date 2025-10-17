<?php

namespace App\Api\Controllers;

use Illuminate\Http\Request;
use App\Api\Controllers\MetricsApi\DevicesMetrics;
use App\Api\Controllers\MetricsApi\AccessPointsMetrics;
use App\Api\Controllers\MetricsApi\PortsMetrics;
use App\Api\Controllers\MetricsApi\PortsStatisticsMetrics;
use App\Api\Controllers\MetricsApi\MempoolsMetrics;
use App\Api\Controllers\MetricsApi\ProcessorsMetrics;
use App\Api\Controllers\MetricsApi\SensorsMetrics;
use App\Api\Controllers\MetricsApi\ApplicationsMetrics;
use App\Api\Controllers\MetricsApi\CustomoidsMetrics;

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

    public function mempools(Request $request)
    {
        $body = app(MempoolsMetrics::class)->render($request);
        return response($body, 200, ['Content-Type' => 'text/plain; version=0.0.4; charset=utf-8']);
    }

    public function processors(Request $request)
    {
        $body = app(ProcessorsMetrics::class)->render($request);
        return response($body, 200, ['Content-Type' => 'text/plain; version=0.0.4; charset=utf-8']);
    }

    public function sensors(Request $request)
    {
        $body = app(SensorsMetrics::class)->render($request);
        return response($body, 200, ['Content-Type' => 'text/plain; version=0.0.4; charset=utf-8']);
    }

    public function applications(Request $request)
    {
        $body = app(ApplicationsMetrics::class)->render($request);
        return response($body, 200, ['Content-Type' => 'text/plain; version=0.0.4; charset=utf-8']);
    }

    public function customoids(Request $request)
    {
        $body = app(CustomoidsMetrics::class)->render($request);
        return response($body, 200, ['Content-Type' => 'text/plain; version=0.0.4; charset=utf-8']);
    }
}
