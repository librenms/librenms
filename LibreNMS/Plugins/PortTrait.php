<?php

namespace LibreNMS\Plugins;

use App\Models\Port;

trait PortTrait
{
    protected static $port_view = 'port';

    public static function port_container($device, $port)
    {
        echo view(self::prefix() . self::$port_view, self::portData(Port::find($port['port_id'])));
    }

    protected static function portData(Port $port): array
    {
        return [
            'title' => self::className(),
            'port'  => $port,
        ];
    }
}
