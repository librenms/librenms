<?php

/**
 * AbstractGraph.php
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

use App\Data\TimeSeries\Contracts\MetricValidator;
use App\Facades\DeviceCache;
use App\Facades\PortCache;
use App\Facades\Rrd;
use App\Models\Device;
use App\Models\Port;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use LibreNMS\Exceptions\RrdGraphException;
use LibreNMS\Interfaces\Data\Graphing\GraphInterface;
use LibreNMS\Util\Debug;

abstract class AbstractGraph implements GraphInterface
{
    protected Device $device;
    protected ?Port $port;
    private ?MetricValidator $validator = null;

    public function __construct(
        protected readonly GraphParameters $params,
        protected readonly array $vars = [],
    )
    {
        $device_id = $this->vars['device'] ?? ($this->params->type == 'device' ? ($this->vars['id'] ?? null) : null);
        $this->device = DeviceCache::get($device_id);

        $port_id = $this->vars['port'] ?? ($this->params->type == 'port' ? ($this->vars['id'] ?? null) : null);
        $this->port = PortCache::get($port_id);

        $this->init();
    }

    public function getParams(): GraphParameters
    {
        return $this->params;
    }

    /**
     * Graph specific validation rules for the graph vars.
     * Common graph request input is validated by GraphRequest.
     */
    public function validation(): array
    {
        return [];
    }

    public function getDevice(): ?Device
    {
        return $this->device->exists ? $this->device : null;
    }

    public function getPort(): ?Port
    {
        return $this->port;
    }

    public function getSubtitle(): ?string
    {
        return null;
    }

    public function getPageTitle(): string
    {
        return $this->getGraphTitle();
    }

    public function getRrdCommandOptions(): array
    {
        $params = $this->getParams();

        if ($rules = $this->validation()) {
            Validator::validate($this->vars, $rules);
        }

        if (! $this->authorize()) {
            throw new RrdGraphException('No Authorization', 'No Auth', $params->width, $params->height);
        }

        $rrd_options = $this->rrdDefinition();

        if (empty($rrd_options)) {
            throw new RrdGraphException('Graph Definition Error', 'Def Error', $params->width, $params->height);
        }

        return [...$params->toRrdOptions(), ...$rrd_options];
    }

    public function render(): GraphImage
    {
        $params = $this->getParams();
        $rrd_options = $this->getRrdCommandOptions();

        try {
            return new GraphImage($params->imageFormat, $this->getGraphTitle(), Rrd::graph($rrd_options));
        } catch (RrdGraphException $e) {
            if (Debug::isEnabled()) {
                throw $e;
            }

            $validator = $this->getValidator();

            if ($validator->hasAttempted() && ! $validator->hasValidFiles()) {
                throw new RrdGraphException('No Data files', 'No Data', $params->width, $params->height, $e->getCode(), $e->getImage());
            }

            throw new RrdGraphException('Error: ' . $e->getMessage(), 'Draw Error', $params->width, $params->height, $e->getCode(), $e->getImage());
        }
    }

    public function getValidator(): MetricValidator
    {
        $this->validator ??= app(MetricValidator::class);

        return $this->validator;
    }

    /**
     * Check the ability for the current user.
     * Guests are allowed, they were already authenticated by AuthenticateGraph
     * (signed url, allow_unauth_graphs, or allow_unauth_graphs_cidr)
     */
    protected function allows(string $ability, mixed $arguments = []): bool
    {
        return auth()->guest() || Gate::allows($ability, $arguments);
    }

    protected function init(): void
    {
        // for child init
    }
}
