<?php

/**
 * LegacyGraphScript.php
 *
 * Runs the auth and graph files in includes/html/graphs, which communicate through local variables
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Data\Graphing\Legacy;

use App\Data\Graphing\GraphParameters;
use App\Facades\DeviceCache;
use App\Models\Device;
use App\Models\Port;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Exceptions\NoGraphData;
use LibreNMS\Exceptions\RrdNotFoundException;
use LibreNMS\RRD\RrdPath;
use Throwable;

final readonly class LegacyGraphScript
{
    private function __construct(
        private string $authFile,
        private string $graphFile,
    ) {
    }

    /**
     * Find the files for the graph type, null when there are none
     */
    public static function find(string $type, string $subtype): ?self
    {
        $authFile = base_path("includes/html/graphs/$type/auth.inc.php");
        if (! is_file($authFile)) {
            return null;
        }

        foreach ([$subtype, 'generic'] as $name) {
            $graphFile = base_path("includes/html/graphs/$type/$name.inc.php");
            if (is_file($graphFile)) {
                return new self($authFile, $graphFile);
            }
        }

        return null;
    }

    /**
     * The graphs in the legacy directory of the graph type
     *
     * @return array<int, string>
     */
    public static function subtypes(string $type): array
    {
        $files = glob(base_path("includes/html/graphs/$type/*.inc.php")) ?: [];

        return array_values(array_diff(array_map(fn (string $file) => basename($file, '.inc.php'), $files), ['auth']));
    }

    /**
     * Auth files may resolve the device and port when they were not requested directly
     *
     * @param  array<string, mixed>  $vars
     *
     * @throws GraphException
     */
    public function runAuth(?Device $device, ?Port $port, array $vars): LegacyAuthResult
    {
        if ($device) {
            DeviceCache::setPrimary($device->device_id);
        }

        $scope = $this->include($this->authFile, [
            'device' => $device ?? new Device,
            'port' => $port,
            'vars' => $vars,
            'auth' => auth()->guest(),
        ]);

        if ($device === null) {
            $device = $this->deviceFromScope($scope);

            if ($device) {
                DeviceCache::setPrimary($device->device_id);
            }
        }

        if ($port === null && ($scope['port'] ?? null) instanceof Port) {
            $port = $scope['port'];
        }

        $title = $scope['title'] ?? null;

        return new LegacyAuthResult(
            authorized: (bool) ($scope['auth'] ?? false),
            device: $device,
            port: $port,
            subtitle: is_string($title) && $title !== '' ? $title : null,
            scope: $scope,
        );
    }

    /**
     * @throws GraphException
     */
    public function runGraph(LegacyAuthResult $auth, GraphParameters $params): LegacyGraphResult
    {
        $legacyParams = new LegacyGraphParameters($params);

        $scope = $this->include($this->graphFile, [
            ...$auth->scope,
            'graph_params' => $legacyParams,
            'type' => $params->type,
            'subtype' => $params->subtype,
            'height' => $params->height,
            'width' => $params->width,
            'from' => $params->from,
            'to' => $params->to,
            'period' => $params->period,
            'prev_from' => $params->prev_from,
            'inverse' => $params->inverse,
            'in' => $params->in,
            'out' => $params->out,
            'float_precision' => 2,
            'title' => $params->visible('title'),
            'nototal' => ! $params->visible('total'),
            'nodetails' => ! $params->visible('details'),
            'noagg' => ! $params->visible('aggregate'),
            'rrd_options' => [],
        ]);

        return new LegacyGraphResult(
            rrdOptions: $scope['rrd_options'] ?? [],
            options: $legacyParams->toOptions(),
            title: $legacyParams->title,
            rrdFiles: $this->rrdFilesFromScope($scope),
        );
    }

    /**
     * @param  array<string, mixed>  $scope
     */
    private function deviceFromScope(array $scope): ?Device
    {
        $device = $scope['device'] ?? null;

        if (is_array($device) && isset($device['device_id'])) {
            $device = DeviceCache::get($device['device_id']);
        }

        return $device instanceof Device && $device->exists ? $device : null;
    }

    /**
     * @param  array<string, mixed>  $scope
     * @return array<int, RrdPath>
     */
    private function rrdFilesFromScope(array $scope): array
    {
        if (isset($scope['rrd_list']) && is_array($scope['rrd_list'])) {
            $files = array_column($scope['rrd_list'], 'filename');
        } elseif (isset($scope['rrd_filenames']) && is_array($scope['rrd_filenames'])) {
            $files = $scope['rrd_filenames'];
        } else {
            $files = [$scope['rrd_filename'] ?? $scope['filename'] ?? null];
        }

        return array_values(array_filter($files, fn ($file) => $file instanceof RrdPath));
    }

    /**
     * Include a legacy file with the given local variables
     *
     * @param  array<string, mixed>  $scope
     * @return array<string, mixed> the local variables after the include
     *
     * @throws GraphException
     */
    private function include(string $file, array $scope): array
    {
        $previousCwd = getcwd();
        chdir(base_path());

        try {
            include_once base_path('includes/common.php');
            include_once base_path('includes/html/functions.inc.php');
            include_once base_path('includes/dbFacile.php');
            include_once base_path('includes/rewrites.php');

            return (static function (string $__file, array $__scope): array {
                extract($__scope, EXTR_SKIP);
                unset($__scope);

                include $__file;
                unset($__file);

                return get_defined_vars();
            })($file, $scope);
        } catch (GraphException $e) {
            throw $e;
        } catch (RrdNotFoundException $e) {
            throw new NoGraphData('No Data file', $e);
        } catch (Throwable $e) {
            // legacy scripts fail with warnings and errors on unexpected input, keep the details out of the image
            report($e);

            throw new GraphException('Graph Error', 'Error', $e);
        } finally {
            if ($previousCwd !== false) {
                chdir($previousCwd);
            }
        }
    }
}
