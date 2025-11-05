<?php

namespace App\Api\Controllers;

use App\Api\Controllers\MetricsApi\AccessPointsMetrics;
use App\Api\Controllers\MetricsApi\AlertsMetrics;
use App\Api\Controllers\MetricsApi\ApplicationsMetrics;
use App\Api\Controllers\MetricsApi\CustomoidsMetrics;
use App\Api\Controllers\MetricsApi\DevicesMetrics;
use App\Api\Controllers\MetricsApi\MempoolsMetrics;
use App\Api\Controllers\MetricsApi\PollersMetrics;
use App\Api\Controllers\MetricsApi\PortsMetrics;
use App\Api\Controllers\MetricsApi\PortsStatisticsMetrics;
use App\Api\Controllers\MetricsApi\ProcessorsMetrics;
use App\Api\Controllers\MetricsApi\SensorsMetrics;
use App\Api\Controllers\MetricsApi\ServicesMetrics;
use App\Api\Controllers\MetricsApi\StoragesMetrics;
use App\Api\Controllers\MetricsApi\WirelessSensorsMetrics;
use Illuminate\Http\Request;

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
        $html[] = '<li><a href="/api/v0/metrics/access_points">/api/v0/metrics/access_points</a> — access point metrics</li>';
        $html[] = '<li><a href="/api/v0/metrics/alerts">/api/v0/metrics/alerts</a> — alert metrics</li>';
        $html[] = '<li><a href="/api/v0/metrics/applications">/api/v0/metrics/applications</a> — application metric values (app_type, instance, metric)</li>';
        $html[] = '<li><a href="/api/v0/metrics/customoids">/api/v0/metrics/customoids</a> — customoid metrics</li>';
        $html[] = '<li><a href="/api/v0/metrics/devices">/api/v0/metrics/devices</a> — device-level metrics (uptime, last poll, status)</li>';
        $html[] = '<li><a href="/api/v0/metrics/mempools">/api/v0/metrics/mempools</a> — mempool (memory) usage metrics</li>';
        $html[] = '<li><a href="/api/v0/metrics/ports">/api/v0/metrics/ports</a> — ports metrics (octets, packets, errors)</li>';
        $html[] = '<li><a href="/api/v0/metrics/ports_statistics">/api/v0/metrics/ports_statistics</a> — higher-cardinality per-port statistics</li>';
        $html[] = '<li><a href="/api/v0/metrics/processors">/api/v0/metrics/processors</a> — processor usage metrics</li>';
        $html[] = '<li><a href="/api/v0/metrics/sensors">/api/v0/metrics/sensors</a> — health sensors (temperature, power, etc.)</li>';
        $html[] = '<li><a href="/api/v0/metrics/services">/api/v0/metrics/services</a> — service check status metrics</li>';
        $html[] = '<li><a href="/api/v0/metrics/storages">/api/v0/metrics/storages</a> — storage usage metrics</li>';
        $html[] = '<li><a href="/api/v0/metrics/wireless_sensors">/api/v0/metrics/wireless_sensors</a> — wireless sensor metrics</li>';
        $html[] = '<li><a href="/api/v0/metrics/pollers">/api/v0/metrics/pollers</a> — poller performance and cluster metrics</li>';
        $html[] = '</ul>';
        $html[] = '<p>Each metrics endpoint returns text in the Prometheus exposition format (text/plain).</br>';
        $html[] = 'Use your API token via the X-Auth-Token header when scraping.</p>';
        $html[] = '<p>Example curl (replace TOKEN):<br><code>curl -H "X-Auth-Token: TOKEN" https://your.librenms.example/api/v0/metrics/applications</code></p>';

        $html[] = '<h2>Filtering</h2>';
        $html[] = '<p>All metrics endpoints support optional query parameters to filter results to specific devices or device groups. Supported parameters:</p>';
        $html[] = '<ul>';
        $html[] = '<li><code>device_id</code> or <code>device_ids</code> — single or comma-separated device IDs</li>';
        $html[] = '<li><code>hostname</code> or <code>hostnames</code> — single or comma-separated hostnames (matches <code>hostname</code> and <code>sysName</code>)</li>';
        $html[] = '<li><code>device_group</code> — a device group id or name; the group will be expanded to its member devices</li>';
        $html[] = '</ul>';
        $html[] = '<p>Examples:<br>';
        $html[] = '<code>curl -H "X-Auth-Token: TOKEN" "https://your.librenms.example/api/v0/metrics/ports?device_id=1,2,3"</code></br>';
        $html[] = '<code>curl -H "X-Auth-Token: TOKEN" "https://your.librenms.example/api/v0/metrics/mempools?hostnames=sw1,sw2"</br></code>';
        $html[] = '<code>curl -H "X-Auth-Token: TOKEN" "https://your.librenms.example/api/v0/metrics/sensors?device_group=4,5"</code></br>';
        $html[] = '<code>curl -H "X-Auth-Token: TOKEN" "https://your.librenms.example/api/v0/metrics/devices?device_group=switches"</code>';
        $html[] = '</p>';
        $html[] = '</body></html>';

        return response(implode("\n", $html), 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function accessPoints(Request $request)
    {
        $body = app(AccessPointsMetrics::class)->render($request);

        return response($body, 200, ['Content-Type' => 'text/plain; version=0; charset=utf-8']);
    }

    public function alerts(Request $request)
    {
        $body = app(AlertsMetrics::class)->render($request);

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

    public function devices(Request $request)
    {
        $body = app(DevicesMetrics::class)->render($request);

        return response($body, 200, ['Content-Type' => 'text/plain; version=0; charset=utf-8']);
    }

    public function mempools(Request $request)
    {
        $body = app(MempoolsMetrics::class)->render($request);

        return response($body, 200, ['Content-Type' => 'text/plain; version=0; charset=utf-8']);
    }

    public function pollers(Request $request)
    {
        $body = app(PollersMetrics::class)->render($request);

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

    public function services(Request $request)
    {
        $body = app(ServicesMetrics::class)->render($request);

        return response($body, 200, ['Content-Type' => 'text/plain; version=0; charset=utf-8']);
    }

    public function storages(Request $request)
    {
        $body = app(StoragesMetrics::class)->render($request);

        return response($body, 200, ['Content-Type' => 'text/plain; version=0; charset=utf-8']);
    }

    public function wirelessSensors(Request $request)
    {
        $body = app(WirelessSensorsMetrics::class)->render($request);

        return response($body, 200, ['Content-Type' => 'text/plain; version=0; charset=utf-8']);
    }
}
