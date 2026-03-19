<?php

namespace LibreNMS\Enum;

enum SensorState: int
{
    case Ok = 0;
    case Warning = 1;
    case Error = 2;
    case Unknown = 3;
}
