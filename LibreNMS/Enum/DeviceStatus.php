<?php

namespace LibreNMS\Enum;

enum DeviceStatus
{
    case DISABLED;
    case DOWN;
    case IGNORED_DOWN;
    case IGNORED_UP;
    case NEVER_POLLED;
    case UP;
}
