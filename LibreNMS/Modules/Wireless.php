<?php

namespace LibreNMS\Modules;
use App\Models\AccessPoint;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\WirelessAccessPointPolling;
use LibreNMS\OS;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;

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
        if ($os instanceof WirelessAccessPointPolling) {
            echo "\nWireless Access Points: ";

            // Initialize empty collections
            $newCollection = new Collection;
            $offline_access_points = new Collection;

            // Get APs from controller
            $access_points = $os->pollWirelessAccessPoints()->keyBy(function ($item) {
                return $item->getCompositeKey();
            });

            echo "\nCollection from controller: ";
            //d_echo($access_points);

            // Get existing APs from the DB
            $db_access_points = AccessPoint::where(['device_id' => $os->getDeviceId()])->get();

            if($db_access_points->isNotEmpty()) {
                $db_access_points = $db_access_points->keyBy(function ($item) {
                    return $item->getCompositeKey();
                });

                echo "\nCollection from DB: ";
                d_echo($db_access_points);
    
                // Get a collection of possibly offline APs
                $offline_access_points = $db_access_points->intersect($access_points);
                echo "\nIntersect: ";
                d_echo($offline_access_points);
                
                // Mark possibly offline APs as deleted
                foreach ($offline_access_points as $offline_ap) {
                    $offline_ap->setOffline();
                }
            }

            // Create a new collection with updated data, syncmodels
            $newCollection = $access_points->concat($offline_access_points);
            ModuleModelObserver::observe('\App\Models\AccessPoint');
            $this->syncModels($os->getDevice(), 'AccessPoint', $newCollection);

            // Cleanup duplicates? 
            // Can there be any even after failover since the syncmodels hashes by mac+radioid?

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
