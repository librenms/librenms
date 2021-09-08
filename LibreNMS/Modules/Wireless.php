<?php

namespace LibreNMS\Modules;

use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\WirelessPolling;
use LibreNMS\OS;
use LibreNMS\Util\ModuleModelObserver;

class Wireless implements Module
{
    use SyncsModels;

    /**
     * Discover this module. Heavier processes can be run here
     * Run infrequently (default 4 times a day)
     *
     * @param OS $os
     */
    public function discover(OS $os)
    {

    }

    /**
     * Poll data for this module and update the DB / RRD.
     * Try to keep this efficient and only run if discovery has indicated there is a reason to run.
     * Run frequently (default every 5 minutes)
     *
     * @param OS $os
     */
    public function poll(OS $os)
    {
        if ($os instanceof WirelessPolling) {
            echo "\nWireless Access Points: ";

            // Get APs from controller
            $access_points = $os->pollWirelessAccessPoints();

            // Get existing APs from the DB
            $db_access_points = AccessPoint::where(['device_id' => $this->getDeviceId()]);

            // Mark existing APs offline if not found in polled data

            // Syncmodels

            // RRD

            echo PHP_EOL;
        }
    }

    /**
     * Remove all DB data for this module.
     * This will be run when the module is disabled.
     *
     * @param OS $os
     */
    public function cleanup(OS $os)
    {

    }
}
