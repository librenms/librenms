<?php

namespace LibreNMS\Polling;

use App\Models\DevicePollingMethod;
use LibreNMS\Interfaces\PollingMethodInterface;
use LibreNMS\Polling\Method\PollingMethodDefinition;

class PollingMethodFactory
{
    public function make(DevicePollingMethod $method): PollingMethodInterface
    {
        $class = PollingMethodDefinition::for($method->method_type)->class();

        /** @var PollingMethodInterface $instance */
        $instance = $class::fromModel($method);

        return $instance;
    }
}
