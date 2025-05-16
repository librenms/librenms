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
use Illuminate\Http\Request;
use LibreNMS\Config;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;

class MetricsController extends Controller
{
    /**
     * Expose Prometheus metrics on port 9100 only.
     */
    public function index(Request $request, AboutMetrics $aboutMetrics, CollectorRegistry $registry)
    {
        if (Config::get('prometheus_metrics.enable', false)) {
            if (Config::get('prometheus_metrics.port')) {
                if ($request->getPort() !== Config::get('prometheus_metrics_port')) {
                    abort(404);
                }
            }

            // Collect the same stats as /about
            $stats = $aboutMetrics->collect();

            // Register and set each gauge
            foreach ($stats as $name => $value) {
                $gauge = $registry->getOrRegisterGauge(
                    'librenms',        // namespace
                    "{$name}",   // metric name
                    ucfirst($name),    // help
                    []                 // no labels
                );
                $gauge->set($value);
            }

            // Render Prometheus text format
            $renderer = new RenderTextFormat();
            $body = $renderer->render($registry->getMetricFamilySamples());

            return response($body, 200)
               ->header('Content-Type', RenderTextFormat::MIME_TYPE);
        }
    }
}
