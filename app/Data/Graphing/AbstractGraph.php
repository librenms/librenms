<?php

/**
 * AbstractGraph.php
 *
 * Base for graphs: entity lookup from the graph vars, input validation and authorization before drawing
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
use App\Facades\PortCache;
use App\Models\Device;
use App\Models\Port;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Exceptions\InvalidGraphInput;
use LibreNMS\Interfaces\Data\Graphing\GraphInterface;

abstract class AbstractGraph implements GraphInterface
{
    protected readonly GraphVars $vars;

    /**
     * @param  array<string, mixed>  $vars  graph input, such as the entity ids
     */
    public function __construct(
        protected readonly GraphParameters $params,
        array $vars = [],
    ) {
        $this->vars = new GraphVars($vars);
    }

    /**
     * Draw the image, input has been validated and the user authorized
     *
     * @throws GraphException
     */
    abstract protected function draw(): GraphImage;

    public function getParams(): GraphParameters
    {
        return $this->params;
    }

    public function getDevice(): ?Device
    {
        return $this->deviceFromVars() ?? $this->portFromVars()?->device;
    }

    public function getPort(): ?Port
    {
        return $this->portFromVars();
    }

    /**
     * The device from the device var, or the id var of device graphs
     */
    protected function deviceFromVars(): ?Device
    {
        $deviceId = $this->vars->int('device') ?? ($this->params->type === 'device' ? $this->vars->int('id') : null);

        if ($deviceId === null) {
            return null;
        }

        $device = DeviceCache::get($deviceId);

        return $device->exists ? $device : null;
    }

    /**
     * The port from the port var, or the id var of port graphs
     */
    protected function portFromVars(): ?Port
    {
        $portId = $this->vars->int('port') ?? ($this->params->type === 'port' ? $this->vars->int('id') : null);

        return $portId === null ? null : PortCache::get($portId);
    }

    public function getTitle(): string
    {
        return ($this->getDevice()->display ?? ucfirst($this->params->type))
            . '::' . Str::title(str_replace('_', ' ', $this->params->subtype));
    }

    public function getSubtitle(): ?string
    {
        return null;
    }

    public function render(): GraphImage
    {
        $this->checkRenderable();

        return $this->draw();
    }

    /**
     * Graph specific validation rules for the graph vars.
     * Common graph request input is validated by GraphRequest.
     *
     * @return array<string, mixed>
     */
    protected function validation(): array
    {
        return [];
    }

    /**
     * @throws GraphException
     */
    protected function checkRenderable(): void
    {
        if ($rules = $this->validation()) {
            $validator = Validator::make($this->vars->all(), $rules);

            if ($validator->fails()) {
                throw new InvalidGraphInput($validator->errors()->first());
            }
        }

        if (! $this->authorize()) {
            throw new GraphException('No Authorization', 'No Auth');
        }
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
}
