<?php

namespace LibreNMS\Enum;

enum FpingExitCode: int
{
    case Success = 0;
    case Unreachable = 1;
    case InvalidHost = 2;
    case InvalidArgs = 3;
    case SysCallFail = 4;
}
