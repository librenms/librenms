<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Mike Rostermund <mike@kollegienet.dk>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$scale_min = '0';
$colour_scheme = 'mixed';
$attribs = get_dev_attribs($device['device_id']);
ksort($config['poller_modules']);

require 'includes/graphs/common.inc.php';

$colour_iter = 0;
$rrd_options .= " 'COMMENT:Seconds               Current  Minimum  Maximum  Average\\n'";
foreach ($config['poller_modules'] as $module => $module_status) {
    $rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/poller-'.$module.'-perf.rrd';
    if ($attribs['poll_'.$module] || ( $module_status && !isset($attribs['poll_'.$module]))) {
        if (is_file($rrd_filename)) {
            if (!$config['graph_colours'][$colour_scheme][$colour_iter]) {
                $colour_iter = 0;
            }
            $colour = $config['graph_colours'][$colour_scheme][$colour_iter];
            $colour_iter++;

            $rrd_options .= ' DEF:'.$module.'='.$rrd_filename.':'.$module.':AVERAGE';
            $rrd_options .= ' LINE1.25:'.$module.'#'.$colour.':"'.str_pad($module, 18," ").'"';
            $rrd_options .= ' GPRINT:'.$module.':LAST:%6.2lf  GPRINT:'.$module.':AVERAGE:%7.2lf';
            $rrd_options .= " GPRINT:".$module.":MAX:%7.2lf  'GPRINT:".$module.":AVERAGE:%7.2lf\\n'";
        }
    }
}
