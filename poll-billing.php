#!/usr/bin/php
<?php

$debug = "1";


include("config.php");
include("includes/functions.php");

$iter = "0";

echo("Starting Polling Session ... \n\n");

$bill_query = mysql_query("select * from bills");
while ($bill_data = mysql_fetch_array($bill_query)) {
        echo("Bill : ".$bill_data['bill_name']."\n");

        CollectData($bill_data['bill_id']);

        $iter++;
}

function CollectData($bill_id) {
        $port_query = mysql_query("select * from bill_ports as P, interfaces as I, devices as D where P.bill_id='$bill_id' AND I.interface_id = P.port_id AND D.device_id = I.device_id");

        while ($port_data = mysql_fetch_array($port_query)) {
                unset($port_in_measurement);
                unset($port_in_delta);
                unset($last_port_in_measurement);
                unset($last_port_in_delta);
                unset($port_out_measurement);
                unset($port_out_delta);
                unset($last_port_out_measurement);
                unset($last_port_out_delta);

                $port_id = $port_data['port_id'];
                $host    = $port_data['hostname'];
		$port	 = $port_data['port'];

                echo("\nPolling ".$port_data['ifDescr']." on ".$port_data['hostname']."\n");

                $port_in_measurement = trim(getValue($host, $port_data['community'], $port, $port_data['ifIndex'], "In"));
                $port_out_measurement = trim(getValue($host, $port_data['community'], $port,  $port_data['ifIndex'], "Out"));

                echo("$port_in_measurement and $port_out_measurement \n");

                $now = mysql_result(mysql_query("SELECT NOW()"), 0);

                $last_data = getLastPortCounter($port_id,in);
                if ($last_data[state] == "ok") {
                        $last_port_in_measurement = $last_data[counter];
                        $last_port_in_delta = $last_data[delta];
                        if ($port_in_measurement > $last_port_in_measurement) {
                                $port_in_delta = $port_in_measurement - $last_port_in_measurement;
                        } else {
                                $port_in_delta = $last_port_in_delta;
                        }
                } else {
                        $port_in_delta = '0';
                }
                $pim = "INSERT INTO port_in_measurements (port_id,timestamp,counter,delta) VALUES ($port_id, '$now', $port_in_measurement, $port_in_delta) ";
                #echo("$pim \n");
                $pim_query = mysql_query($pim);
                unset($last_data, $last_port_in_measurement, $last_port_in_delta);

                $last_data = getLastPortCounter($port_id,out);
                if ($last_data[state] == "ok") {
                        $last_port_out_measurement = $last_data[counter];
                        $last_port_out_delta = $last_data[delta];
                        if ($port_out_measurement > $last_port_out_measurement) {
                                $port_out_delta = $port_out_measurement - $last_port_out_measurement;
                        } else {
                                $port_out_delta = $last_port_out_delta;
                        }
                } else {
                  $port_out_delta = '0';
                }
                $pom = "INSERT INTO port_out_measurements (port_id,timestamp,counter,delta) VALUES ($port_id, '$now', $port_out_measurement, $port_out_delta) ";
                #echo("$pom \n");
                $pom_query = mysql_query($pom);
                unset($last_data, $last_port_in_measurement, $last_port_in_delta);

                $delta = $delta + $port_in_delta + $port_out_delta;
                $in_delta = $in_delta + $port_in_delta;
                $out_delta = $out_delta + $port_out_delta;
                unset($port_in_delta,$port_out_delta,$prev_delta,$prev_timestamp,$period);

        }
        $last_data = getLastMeasurement($bill_id);

        if ($last_data[state] == "ok") {
                $prev_delta     = $last_data[delta];
                $prev_in_delta  = $last_data[in_delta];
                $prev_out_delta = $last_data[out_delta];
                $prev_timestamp = $last_data[timestamp];
                $period = mysql_result(mysql_query("SELECT UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) - UNIX_TIMESTAMP('$prev_timestamp')"),0);
        } else {
                $prev_delta = '0';
                $period   = '0';
                $prev_in_delta =  '0';
                $prev_out_delta =  '0';
        }
        if( $delta < '0' ) {
                $delta = $prev_delta;
                $in_delta = $prev_in_delta;
                $out_delta = $prev_out_delta;
        }
        $insert_string = "INSERT INTO bill_data (bill_id,timestamp,period,delta,in_delta,out_delta) VALUES ('$bill_id','$now','$period','$delta','$in_delta','$out_delta')";
        #echo("$insert_string\n");
        $insert_measurement = mysql_query($insert_string);
}

if ($argv[1]) { CollectData($argv[1]); }


?>

