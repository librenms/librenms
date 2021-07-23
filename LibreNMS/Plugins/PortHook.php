<?php

namespace LibreNMS\Plugins;

use App\Models\Port;

trait PortHook
{
    public static function port_container($device, $port)
    {
        echo view(self::prefix() . self::authenticatePortHook($device, $port), self::portData(Port::find($port['port_id'])));
    }

    protected static function authenticatePortHook($device, $port)
    {
        return 'port';
    }

    protected static function portData(Port $port): array
    {
        return [
            'title' => self::className(),
            'port'  => $port,
        ];
    }
}
