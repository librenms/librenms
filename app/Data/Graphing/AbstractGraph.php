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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Exceptions\InvalidGraphInput;
use LibreNMS\Exceptions\UnauthorizedGraph;
use LibreNMS\Interfaces\Data\Graphing\GraphInterface;

abstract class AbstractGraph implements GraphInterface
{
    public function __construct(protected readonly GraphParameters $params)
    {
    }

    /**
     * Draw the image, input has been validated and the user authorized
     *
     * @throws GraphException
     */
    abstract protected function draw(): GraphImage;

    public function getPageContext(): GraphPageContext
    {
        $port = $this->requestedPort();

        return new GraphPageContext($this->requestedDevice() ?? $port?->device, $port);
    }

    /**
     * The device requested by the device or id (of device graphs) input, null if it does not exist
     */
    protected function requestedDevice(): ?Device
    {
        if ($this->params->deviceId === null) {
            return null;
        }

        $device = DeviceCache::get($this->params->deviceId);

        return $device->exists ? $device : null;
    }

    /**
     * The port requested by the port or id (of port graphs) input
     */
    protected function requestedPort(): ?Port
    {
        return $this->params->portId === null ? null : PortCache::get($this->params->portId);
    }

    public function getTitle(): string
    {
        return ($this->getPageContext()->device->display ?? ucfirst($this->params->type))
            . '::' . Str::title(str_replace('_', ' ', $this->params->subtype));
    }

    public function getDebugCommand(): ?string
    {
        return null;
    }

    public function render(): GraphImage
    {
        $this->checkRenderable();

        return $this->draw();
    }

    /**
     * Graph specific validation rules for the graph input.
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
            $validator = Validator::make($this->params->input->all(), $rules);

            if ($validator->fails()) {
                throw new InvalidGraphInput($validator->errors()->first());
            }
        }

        if (! $this->authorize()) {
            throw new UnauthorizedGraph;
        }
    }

    /**
     * Can the current user view the model, guests only when anonymous graphs are allowed
     */
    protected function canView(Model $model): bool
    {
        return app(GraphAccess::class)->canView($model);
    }
}
