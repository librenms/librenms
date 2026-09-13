<?php

namespace LibreNMS\Polling;

use App\Models\DevicePollingMethod;
use LibreNMS\Interfaces\PollingMethodConfigInterface;

class PollingMethodFactory
{
    public function make(DevicePollingMethod $method): PollingMethodConfigInterface
    {
        /** @var class-string<PollingMethodConfigInterface> $class */
        $class = $method->method_type->definition()->class();

        return $class::fromModel($method);
    }
}
