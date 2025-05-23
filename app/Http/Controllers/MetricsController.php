<?php

/*
 * MetricsController.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2025 Peter Childs
 * @author     Peter Childs <pjchilds@gmail.com>
 */

namespace App\Http\Controllers;

use App\Services\AboutMetrics;
use App\Services\AlertMetrics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use LibreNMS\Config;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;

class MetricsController extends Controller
{
    /**
     * Expose Prometheus metrics if enabled, and on configured port only (if port is configured)
     */
    public function index(
        Request $request,
        AboutMetrics $aboutMetrics,
        AlertMetrics $alertMetrics,
        CollectorRegistry $registry
    ) {
        if (! Config::get('prometheus_metrics.enable')) {
            abort(404);
        }

        $this->authorizePort($request);
        $this->registerGeneralMetrics($registry, $aboutMetrics);

        if (Config::get('prometheus_metrics.alerts', false)) {
            $this->registerActiveAlertMetrics($registry, $alertMetrics);
            $this->registerRaisedAlertMetrics($registry, $alertMetrics);
        }

        return $this->renderMetrics($registry);
    }

    /**
     * Ensure request port matches configured metrics port
     */
    private function authorizePort(Request $request): void
    {
        $port = Config::get('prometheus_metrics.port');
        if ($port && $request->getPort() !== $port) {
            abort(404, 'Metrics exposed on ' . $port);
        }
    }

    /**
     * Register general application metrics - qty of Devices, Ports, etc
     */
    private function registerGeneralMetrics(
        CollectorRegistry $registry,
        AboutMetrics $aboutMetrics
    ): void {
        $stats = Cache::remember('about_metrics', 300, fn () => $aboutMetrics->collect());
        foreach ($stats as $stat => $val) {
            $gauge = $registry->getOrRegisterGauge(
                'librenms',
                'qty_' . preg_replace('/^stat_/', '', $stat),
                '',
                []
            );
            $gauge->set($val);
        }
    }

    /**
     * Register metrics for currently active/open alerts
     */
    private function registerActiveAlertMetrics(
        CollectorRegistry $registry,
        AlertMetrics $alertMetrics
    ): void {
        $gauge = $registry->getOrRegisterGauge(
            'librenms',
            'alerts_active',
            'Number of currently open alerts',
            ['rule']
        );
        foreach ($alertMetrics->active() as $rule => $count) {
            $gauge->set($count, [$rule]);
        }
    }

    /**
     * Register metrics for alerts raised in the last 5 minutes
     */
    private function registerRaisedAlertMetrics(
        CollectorRegistry $registry,
        AlertMetrics $alertMetrics
    ): void {
        $gauge = $registry->getOrRegisterGauge(
            'librenms',
            'alerts_raised_last_5m',
            'Alerts that changed to active in the past 5 minutes',
            ['rule']
        );
        foreach ($alertMetrics->raisedLast5m() as $rule => $count) {
            $gauge->set($count, [$rule]);
        }
    }

    /**
     * Render the metrics in Prometheus text format
     */
    private function renderMetrics(CollectorRegistry $registry)
    {
        $renderer = new RenderTextFormat();
        $body = $renderer->render($registry->getMetricFamilySamples());

        return response($body, 200)
            ->header('Content-Type', RenderTextFormat::MIME_TYPE);
    }
}
