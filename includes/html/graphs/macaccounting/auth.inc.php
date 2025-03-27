<?php

use LibreNMS\Exceptions\RrdGraphException;
use LibreNMS\Util\Mac;

if (is_numeric($vars['id'])) {
    $acc = dbFetchRow('SELECT * FROM `mac_accounting` AS M, `ports` AS I, `devices` AS D WHERE M.ma_id = ? AND I.port_id = M.port_id AND I.device_id = D.device_id', [$vars['id']]);

    if (\LibreNMS\Util\Debug::isEnabled()) {
        echo '<pre>';
        print_r($acc);
        echo '</pre>';
    }

    if (is_array($acc)) {
        if ($auth || port_permitted($acc['port_id'])) {
            $filename = Rrd::name($acc['hostname'], ['cip', $acc['ifIndex'], $acc['mac']]);
            d_echo($filename);

            if (is_file($filename)) {
                d_echo('exists');

                $rrd_filename = $filename;
                $port = cleanPort(get_port_by_id($acc['port_id']));
                $device = device_by_id_cache($port['device_id']);
                $title = generate_device_link($device);
                $title .= ' :: Port  ' . generate_port_link($port);
                $title .= ' :: ' . Mac::parse($acc['mac'])->readable();
                $auth = true;
            } else {
                throw new RrdGraphException('file not found');
            }
        } else {
            throw new RrdGraphException('unauthenticated');
        }
    } else {
        throw new RrdGraphException('entry not found');
    }
} else {
    throw new RrdGraphException('invalid id');
}
