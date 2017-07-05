<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Yacine Ben <https://github.com/yac01/librenms.git >
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
 
 
 function opentsdb_update($device, $measurement, $tags, $fields)
{

        global $config, $opentsdb;      // Gloabal Variable look config.php for enable or disable OpenTSDB

        if ($config['opentsdb']['enable'] == true)    // if OpenTSDB enable
        {
          if ($opentsdb != true)  // if the connection is already made

          {$opentsdb = fsockopen($config['opentsdb']['host'], $config['opentsdb']['port']);}

          $timestamp = time();
          $tmp_tags = "hostname=".$device['hostname'];
        
   /*If you want to sort you metric by customer and gain speed when you send you query on grafana you need to put 
     $config['opentsdb']['co'] == true else false */
        if ($config['opentsdb']['customer'] == true)
          {
            $co = $device['customer'];
            $measurement = $measurement.".".$co;   // Add Object Code
          }

          foreach ($tags as $k => $v)
             {
              $v = str_replace(array(' ',',','='), '_', $v);
              if (!empty($v)) {
                 $tmp_tags = $tmp_tags ." ". $k ."=".$v;
                 }
             }

          foreach ($fields as $k => $v)
             {
              $tmp_tags_key = $tmp_tags ." ". "key" ."=".$k;
              $line = sprintf('put net.%s %d %f %s', strtolower($measurement), $timestamp, $v, $tmp_tags_key);
              d_echo("Sending to OPenTSDB: $line\n");
              fwrite($opentsdb, $line . "\n"); // send $line into OpenTSDB
             }
        }
}

