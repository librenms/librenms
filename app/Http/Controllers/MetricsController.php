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

use App\Services\AboutAlerts;
use App\Services\AboutMetrics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use LibreNMS\Config;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;

class MetricsController extends Controller
{
    /**
     * Expose Prometheus metrics on port 9100 only.
     */
    public function index(Request $request, AboutMetrics $aboutMetrics, AboutAlerts $aboutAlerts, CollectorRegistry $registry)
    {
        if (! Config::get('prometheus_metrics.enable')) {
            abort(404);
        }

        $port = Config::get('prometheus_metrics.port');
        if ($port) {
            if ($request->getPort() !== $port) {
                abort(404, 'Metrics exposed on ' . $port);
            }
        }

        $stats = Cache::remember('about_metrics', 300, fn () => $aboutMetrics->collect());
        foreach ($stats as $stat => $val) {
            $g = $registry->getOrRegisterGauge('librenms', 'qty_' . preg_replace('/^stat_/', '', $stat), '', []);
            $g->set($val);
        }

        if (Config::get('prometheus_metrics.alerts', false)) {
            //  active alerts
            foreach ($aboutAlerts->active() as $ruleName => $openCount) {
                $g = $registry->getOrRegisterGauge(
                    'librenms',
                    'alerts_active',
                    'Number of currently open alerts',
                    ['rule']
                );
                $g->set($openCount, [$ruleName]);
            }

            foreach ($aboutAlerts->raisedLast5m() as $ruleName => $raisedCnt) {
                $g = $registry->getOrRegisterGauge(
                    'librenms',
                    'alerts_raised_last_5m',
                    'Alerts that changed to active in the past 5 minutes',
                    ['rule']
                );
                $g->set($raisedCnt, [$ruleName]);
            }
        }

        // Render Prometheus text format
        $renderer = new RenderTextFormat();
        $body = $renderer->render($registry->getMetricFamilySamples());

        return response($body, 200)
            ->header('Content-Type', RenderTextFormat::MIME_TYPE);
    }
}
