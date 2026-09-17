<?php

namespace LibreNMS\Polling;

use App\Models\DevicePollingMethod;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;

class PollingMethodFactory
{
    public function make(DevicePollingMethod $method): PollingMethodConfig
    {
        $class = $method->method_type->definition()->class();

        return $class::fromPollingMethod($method);
    }
}
