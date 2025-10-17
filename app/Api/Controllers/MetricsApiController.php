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
    public function index(Request $request)
    {
        $html = [];
        $html[] = '<!doctype html>';
        $html[] = '<html><head><meta charset="utf-8"><title>LibreNMS Prometheus metrics</title></head><body>';
        $html[] = '<h1>LibreNMS Prometheus metrics</h1>';
        $html[] = '<p>This endpoint exposes multiple Prometheus-compatible metric endpoints. Each endpoint requires a valid API token with global-read access.</br>';
        $html[] = 'Scrape the specific metric paths below (relative links).</p>';
        $html[] = '<ul>';
        $html[] = '<li><a href="access_points">/metrics/access_points</a> — access point metrics</li>';
        $html[] = '<li><a href="applications">/metrics/applications</a> — application metric values (app_type, instance, metric)</li>';
        $html[] = '<li><a href="customoids">/metrics/customoids</a> — custom oid metrics</li>';
        $html[] = '<li><a href="devices">/metrics/devices</a> — device-level metrics (uptime, last poll, status)</li>';
        $html[] = '<li><a href="mempools">/metrics/mempools</a> — mempool (memory) usage metrics</li>';
        $html[] = '<li><a href="ports">/metrics/ports</a> — ports metrics (octets, packets, errors)</li>';
        $html[] = '<li><a href="ports_statistics">/metrics/ports_statistics</a> — higher-cardinality per-port statistics</li>';
        $html[] = '<li><a href="processors">/metrics/processors</a> — processor usage metrics</li>';
        $html[] = '<li><a href="sensors">/metrics/sensors</a> — health sensors (temperature, power, etc.)</li>';
        $html[] = '</ul>';
        $html[] = '<p>Each metrics endpoint returns text in the Prometheus exposition format (text/plain).</br>';
        $html[] = 'Use your API token via the X-Auth-Token header when scraping.</p>';
        $html[] = '<p>Example curl (replace TOKEN):<br><code>curl -H "X-Auth-Token: TOKEN" https://your.librenms.example/api/v0/metrics/applications</code></p>';
        $html[] = '</body></html>';

        return response(implode("\n", $html), 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }
    public function devices(Request $request)
    {
        $body = app(DevicesMetrics::class)->render($request);
        return response($body, 200, ['Content-Type' => 'text/plain; version=0; charset=utf-8']);
    }

    public function accessPoints(Request $request)
    {
        $body = app(AccessPointsMetrics::class)->render($request);
        return response($body, 200, ['Content-Type' => 'text/plain; version=0; charset=utf-8']);
    }

    public function ports(Request $request)
    {
        $body = app(PortsMetrics::class)->render($request);
        return response($body, 200, ['Content-Type' => 'text/plain; version=0; charset=utf-8']);
    }

    public function portsStatistics(Request $request)
    {
        $body = app(PortsStatisticsMetrics::class)->render($request);
        return response($body, 200, ['Content-Type' => 'text/plain; version=0; charset=utf-8']);
    }

    public function mempools(Request $request)
    {
        $body = app(MempoolsMetrics::class)->render($request);
        return response($body, 200, ['Content-Type' => 'text/plain; version=0; charset=utf-8']);
    }

    public function processors(Request $request)
    {
        $body = app(ProcessorsMetrics::class)->render($request);
        return response($body, 200, ['Content-Type' => 'text/plain; version=0; charset=utf-8']);
    }

    public function sensors(Request $request)
    {
        $body = app(SensorsMetrics::class)->render($request);
        return response($body, 200, ['Content-Type' => 'text/plain; version=0; charset=utf-8']);
    }

    public function applications(Request $request)
    {
        $body = app(ApplicationsMetrics::class)->render($request);
        return response($body, 200, ['Content-Type' => 'text/plain; version=0; charset=utf-8']);
    }

    public function customoids(Request $request)
    {
        $body = app(CustomoidsMetrics::class)->render($request);
        return response($body, 200, ['Content-Type' => 'text/plain; version=0; charset=utf-8']);
    }
}
