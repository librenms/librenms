<?php

namespace App\Graphs;

use App\Data\Graphing\GraphImage;
use App\Data\Graphing\GraphPageContext;
use App\Data\Graphing\GraphParameters;
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

/**
 * Base for graphs: entity lookup from the graph vars, input validation and authorization before drawing
 */
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
