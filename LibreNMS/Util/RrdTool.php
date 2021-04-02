<?php

namespace LibreNMS\Util;

use App\Models\Device;
use LibreNMS\Config;
use LibreNMS\Util\Rewrite;
use LibreNMS\Data\Store\Rrd;


class RrdTool
{
    /**
     * Get array of all rrd files for device
     *
     * @param array $device device for which we get the rrd's
     * @return array $rrdfilearray array of rrd files for this host
     */
    public static function getRrdFiles($device)
    {
        if (Config::get('rrdcached')) {
            $rrdcachedcmd = sprintf('%s list /%s --daemon %s', Config::get('rrdtool', 'rrdtool'), $device['hostname'], Config::get('rrdcached'));
            $rrd_files = shell_exec($rrdcachedcmd);
            // Split returned files into array
            $rrdfilearray = preg_split('/\s+/', trim($rrd_files));
        } else {
            $rrdclass = new Rrd();
            $rrddir = $rrdclass->dirFromHost($device['hostname']);

            $pattern = sprintf('%s/*.rrd', $rrddir);
            $rrdfilearray = glob($pattern);
        }

        return $rrdfilearray;
    }

    /**
     * Get array of rrd files for specific application.
     *
     * @param array $device device for which we get the rrd's
     * @param int   $app_id application id on the device
     * @param string  $app_name name of app to be searched
     * @param string  $category which category of graphs are searched
     * @return array $rrdfilearray array of rrd files for this host
     */
    public static function getRrdApplicationArrays($device, $app_id, $app_name, $category = null)
    {
        $entries = [];
        $separator = '-';

        $rrdfilearray = self::getRrdFiles($device);
        if ($category) {
            $pattern = sprintf('%s-%s-%s-%s', 'app', $app_name, $app_id, $category);
        } else {
            $pattern = sprintf('%s-%s-%s', 'app', $app_name, $app_id);
        }

        foreach ($rrdfilearray as $rrd) {
            if (str_contains($rrd, $pattern)) {
                $filename = basename($rrd, '.rrd');
                $entry = explode($separator, $filename, 4 + $offset)[3 + $offset];
                if ($entry) {
                    array_push($entries, $entry);
                }
            }
        }
        
        return $entries;
    }
}
