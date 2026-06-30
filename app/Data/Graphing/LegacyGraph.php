<?php

/**
 * LegacyGraph.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Data\Graphing;

use App\Facades\DeviceCache;
use LibreNMS\Exceptions\InvalidGraph;
use LibreNMS\Exceptions\RrdNotFoundException;

class LegacyGraph extends AbstractGraph
{
    private readonly string $auth_file;
    private readonly string $graph_file;

    private ?string $pageTitle = null;
    private ?string $graphTitle = null;
    private ?bool $authorized = null;
    private bool $loaded = false;
    private array $rrdOptions = [];

    /**
     * @param  array<string, scalar>  $vars
     *
     * @throws InvalidGraph
     */
    public function __construct(
        GraphParameters $params,
        array $vars = [],
    ) {
        parent::__construct($params, $vars);

        $this->auth_file = base_path("includes/html/graphs/$params->type/auth.inc.php");
        if (! file_exists($this->auth_file)) {
            throw new InvalidGraph;
        }

        $graph_file = base_path("includes/html/graphs/$params->type/$params->subtype.inc.php");
        if (! file_exists($graph_file)) {
            $graph_file = base_path("includes/html/graphs/$params->type/generic.inc.php");
        }
        $this->graph_file = $graph_file;
        if (! file_exists($this->graph_file)) {
            throw new InvalidGraph;
        }
    }

    private function load(): void
    {
        if ($this->loaded) {
            return;
        }

        $previousCwd = getcwd();
        chdir(base_path());

        try {
            include_once base_path('includes/common.php');
            include_once base_path('includes/html/functions.inc.php');
            include_once base_path('includes/dbFacile.php');
            include_once base_path('includes/rewrites.php');

            if ($this->device->exists) {
                DeviceCache::setPrimary($this->device->device_id);
            }

            // Local scope variables for the included files
            $device = $this->device;
            $port = $this->port;
            $vars = $this->vars;

            $auth = auth()->guest();
            @include $this->auth_file;

            $this->authorized = $auth;
            $this->graphTitle = $graph_title ?? $this->graphTitle;
            $this->pageTitle = $title ?? $this->graphTitle;

            if (! $auth) {
                $this->loaded = true;

                return;
            }

            $graph_params = $this->params;
            $type = $graph_params->type;
            $subtype = $graph_params->subtype;
            $height = $graph_params->height;
            $width = $graph_params->width;
            $from = $graph_params->from;
            $to = $graph_params->to;
            $period = $graph_params->period;
            $prev_from = $graph_params->prev_from;
            $inverse = $graph_params->inverse;
            $in = $graph_params->in;
            $out = $graph_params->out;
            $float_precision = $graph_params->float_precision;
            $title = $graph_params->visible('title');
            $nototal = ! $graph_params->visible('total');
            $nodetails = ! $graph_params->visible('details');
            $noagg = ! $graph_params->visible('aggregate');

            $rrd_options = [];

            @include $this->graph_file;

            $this->rrdOptions = $rrd_options;

            $validator = $this->getValidator();
            if (isset($rrd_list) && is_array($rrd_list)) {
                foreach ($rrd_list as $item) {
                    $validator->validate(null, ['filename' => $item['filename']]);
                }
            } elseif (isset($rrd_filenames) && is_array($rrd_filenames)) {
                foreach ($rrd_filenames as $filename) {
                    $validator->validate(null, ['filename' => $filename]);
                }
            } elseif (isset($rrd_filename)) {
                $validator->validate(null, ['filename' => $rrd_filename]);
            }

            $this->loaded = true;
        } catch (RrdNotFoundException) {
            //
        } finally {
            if ($previousCwd !== false) {
                chdir($previousCwd);
            }
        }
    }

    public function authorize(): bool
    {
        $this->load();

        return $this->authorized;
    }

    public function rrdDefinition(): array
    {
        $this->load();

        return $this->rrdOptions;
    }

    public function getPageTitle(): string
    {
        $this->load();

        return $this->pageTitle ?? $this->getGraphTitle();
    }

    public function getGraphTitle(): string
    {
        $this->load();

        if ($this->graphTitle !== null) {
            return $this->graphTitle;
        }

        if ($this->port) {
            return $this->port->device?->display . ' :: ' . $this->port->getDescription();
        }

        return $this->device->display ?? '';
    }
}
