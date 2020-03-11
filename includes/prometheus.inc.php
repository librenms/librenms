<?php

use LibreNMS\Config;

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

function prometheus_push($device, $measurement, $tags, $fields)
{
    global $prometheus;
    if (Config::get('prometheus.enable') === true) {
        if ($prometheus !== false) {
            try {
                $ch = curl_init();

                set_curl_proxy($ch);
                $vals = "";
                $promtags = "/measurement/".$measurement;

                foreach ($fields as $k => $v) {
                    if ($v !== null) {
                        $vals = $vals . "$k $v\n";
                    }
                }
                
                foreach ($tags as $t => $v) {
                    if ($v !== null) {
                        $promtags = $promtags . "/$t/$v";
                    }
                }

                $promurl = Config::get('prometheus.url') . '/metrics/job/' . Config::get('prometheus.job') . '/instance/' . $device['hostname'] . $promtags;
                $promurl = str_replace(" ", "-", $promurl); // Prometheus doesn't handle tags with spaces in url
        
                d_echo("\nPrometheus data:\n");
                d_echo($measurement);
                d_echo($tags);
                d_echo($fields);
                d_echo($vals);
                d_echo($promurl);
                d_echo("\nEND\n");

                curl_setopt($ch, CURLOPT_URL, $promurl);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $vals);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 
                $headers = array();
                $headers[] = "Content-Type: test/plain";
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        
                curl_exec($ch);
 
                if (curl_errno($ch)) {
                    d_echo('Error:' . curl_error($ch));
                }
            } catch (Exception $e) {
                d_echo("Caught exception: " . $e->getMessage() . PHP_EOL);
                d_echo($e->getTrace());
            }
        } else {
            c_echo("[%gPrometheus Push Disabled%n]\n");
        }//end if
    }//end if
}// end prometheus_push
