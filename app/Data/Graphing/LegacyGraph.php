<?php

/**
 * LegacyGraph.php
 *
 * Adapter for the graph include files in includes/html/graphs
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

namespace App\Data\Graphing;

use App\Facades\DeviceCache;
use App\Models\Device;
use App\Models\Port;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Exceptions\InvalidGraph;
use LibreNMS\Exceptions\RrdNotFoundException;
use LibreNMS\RRD\RrdPath;

class LegacyGraph extends AbstractRrdGraph
{
    private readonly string $auth_file;
    private readonly string $graph_file;

    /** @var array<string, mixed>|null local variables set by the auth file, shared with the graph file */
    private ?array $authScope = null;
    private ?LegacyGraphParameters $legacyParams = null;
    /** @var array<int, string> */
    private array $rrdOptions = [];
    private ?Device $device = null;
    private ?Port $port = null;

    /**
     * @param  array<string, scalar>  $vars
     *
     * @throws InvalidGraph
     */
    public function __construct(GraphParameters $params, array $vars = [])
    {
        parent::__construct($params, $vars);

        $this->auth_file = base_path("includes/html/graphs/$params->type/auth.inc.php");
        if (! is_file($this->auth_file)) {
            throw new InvalidGraph;
        }

        $graph_file = base_path("includes/html/graphs/$params->type/$params->subtype.inc.php");
        if (! is_file($graph_file)) {
            $graph_file = base_path("includes/html/graphs/$params->type/generic.inc.php");
        }
        if (! is_file($graph_file)) {
            throw new InvalidGraph;
        }
        $this->graph_file = $graph_file;
    }

    public function authorize(): bool
    {
        return (bool) ($this->authScope()['auth'] ?? false);
    }

    /**
     * Auth files may resolve the device for the graph
     */
    public function getDevice(): ?Device
    {
        $this->authScope();

        return $this->device;
    }

    /**
     * Auth files may resolve the port for the graph
     */
    public function getPort(): ?Port
    {
        $this->authScope();

        return $this->port;
    }

    public function getSubtitle(): ?string
    {
        $title = $this->authScope()['title'] ?? null;

        return is_string($title) && $title !== '' ? $title : null;
    }

    public function getTitle(): string
    {
        return $this->graphParams()->title ?: parent::getTitle();
    }

    protected function options(): GraphOptions
    {
        return $this->graphParams()->toOptions();
    }

    protected function rrdDefinition(): array
    {
        $this->graphParams();

        return $this->rrdOptions;
    }

    /**
     * @return array<string, mixed>
     */
    private function authScope(): array
    {
        if ($this->authScope !== null) {
            return $this->authScope;
        }

        $this->device = $this->deviceFromVars();
        $this->port = $this->portFromVars();
        if ($this->device) {
            DeviceCache::setPrimary($this->device->device_id);
        }

        $scope = $this->includeLegacy($this->auth_file, [
            'device' => $this->device ?? new Device,
            'port' => $this->port,
            'vars' => $this->vars,
            'auth' => auth()->guest(),
        ]);

        if ($this->device === null) {
            $device = $scope['device'] ?? null;
            if (is_array($device) && isset($device['device_id'])) {
                $device = DeviceCache::get($device['device_id']);
            }
            $this->device = $device instanceof Device && $device->exists ? $device : null;

            if ($this->device) {
                DeviceCache::setPrimary($this->device->device_id);
            }
        }

        if (($scope['port'] ?? null) instanceof Port) {
            $this->port ??= $scope['port'];
        }

        return $this->authScope = $scope;
    }

    /**
     * Run the graph file once, filling the rrd options and the legacy graph params
     *
     * @throws GraphException
     */
    private function graphParams(): LegacyGraphParameters
    {
        if ($this->legacyParams !== null) {
            return $this->legacyParams;
        }

        $legacyParams = new LegacyGraphParameters($this->params);
        $params = $this->params;

        try {
            $scope = $this->includeLegacy($this->graph_file, [
                ...$this->authScope(),
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
        } catch (RrdNotFoundException $e) {
            throw new GraphException('No Data file', 'No Data', $e);
        }

        $this->rrdOptions = $scope['rrd_options'] ?? [];

        // record the rrd files used so missing files are reported as No Data
        if (isset($scope['rrd_list']) && is_array($scope['rrd_list'])) {
            $files = array_column($scope['rrd_list'], 'filename');
        } elseif (isset($scope['rrd_filenames']) && is_array($scope['rrd_filenames'])) {
            $files = $scope['rrd_filenames'];
        } else {
            $files = [$scope['rrd_filename'] ?? $scope['filename'] ?? null];
        }

        foreach ($files as $file) {
            if ($file instanceof RrdPath) {
                $this->getValidator()->validate($file);
            }
        }

        return $this->legacyParams = $legacyParams;
    }

    /**
     * Include a legacy file with the given local variables
     *
     * @param  array<string, mixed>  $scope
     * @return array<string, mixed> the local variables after the include
     */
    private function includeLegacy(string $file, array $scope): array
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
        } finally {
            if ($previousCwd !== false) {
                chdir($previousCwd);
            }
        }
    }
}
