<?php

namespace LibreNMS\Enum;

enum GraphOutput: string
{
    case Raw = 'raw';
    case Base64 = 'base64';
    case Inline = 'inline';
}
