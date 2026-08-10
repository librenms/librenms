<?php

namespace LibreNMS\Enum;

enum DeviceStatus
{
    case Disabled;
    case Down;
    case IgnoredDown;
    case IgnoredUp;
    case NeverPolled;
    case Up;
}
