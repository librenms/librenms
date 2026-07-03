<?php

namespace LibreNMS\Polling;

use App\Models\DevicePollingMethod;
use LibreNMS\Interfaces\PollingMethod;

class PollingMethodFactory
{
    public function make(DevicePollingMethod $method): PollingMethod
    {
        $class = $method->method_type->methodClass();

        return $class::fromModel($method);
    }
}
