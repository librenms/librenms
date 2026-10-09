<?php

namespace LibreNMS\Exceptions;

use LibreNMS\Enum\PollingMethodType;

class MissingSecretException extends \Exception
{
    public function __construct(PollingMethodType $type)
    {
        parent::__construct(trans('exceptions.missing_secret', ['method' => $type->label()]));
    }
}
