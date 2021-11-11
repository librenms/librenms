<?php

namespace LibreNMS\OS;

use LibreNMS\OS;

class HimoinsaGensets extends OS
{
    public static function dectostate($oid, $value, $sensor)
    {
        if ($oid === 'status.0') {
            switch ($sensor) {
                case 'Motor':
                    $status = ($value & 1) | ($value & 2);
                    break;
                case 'Mode':
                    $status =
                        ($value & 4) |
                        ($value & 8) |
                        ($value & 16) |
                        ($value & 32);
                    break;
                case 'Alarm':
                    $status = ($value & 128);
                    break;
                case 'TransferPump':
                    $status = ($value & 64);
                    break;
                case 'Comm':
                    $status = ($value & 512) | ($value & 256);
                    break;
            }
            
            return $status;
        }
        if ($oid === 'statusConm.0') {
            switch ($sensor) {
                case 'Comm':
                    $status =
                        ($value & 32) |
                        ($value & 64);
                    break;
                case 'CommAlarm':
                    $status = ($value & 1);
                    break;
            }

            return $status;
        }
    }
}
