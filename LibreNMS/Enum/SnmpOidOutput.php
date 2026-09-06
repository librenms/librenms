<?php

namespace LibreNMS\Enum;

enum SnmpOidOutput
{
    case Full;
    case Numeric;
    case Module;
    case Suffix;
    case Ucd;
    case None;
}
