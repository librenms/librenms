<?php
	include("includes/defaults.inc.php");
	include("config.php");

	$basepath = $config['rrd_dir'].'/';

	$files = (getDirectoryTree($basepath));

	$count = count($files);

        if ($config['rrdcached'])
        {
          $rrdcached = " --daemon " . $config['rrdcached'];
        }

	echo($count . " Files \n");
	$start = date("U");
        $i = 0;
	foreach ($files as $file){
		fixRdd($file);
		$i++;
		if (date("U") - $start > 1)
			echo(round(($i / $count) * 100, 2) . "%  \r");
	}

	function getDirectoryTree( $outerDir, &$files = array()){

		$dirs = array_diff( scandir( $outerDir ), Array( ".", ".." ) );
    		foreach ( $dirs as $d ){
        		if (is_dir($outerDir."/".$d)  ){
				getDirectoryTree($outerDir.'/'. $d, $files);
        		}else{
				if (preg_match('/^[\d]+.rrd$/', $d))
					array_push($files, preg_replace('/\/+/', '/',  $outerDir.'/'. $d));

			}
    		}
  		return $files;
	}


	function fixRdd($file){
		global $config;
                global $rrdcached;
		$fileC = shell_exec( "{$config['rrdtool']} dump $file $rrdcached" );

#---------------------------------------------------------------------------------------------------------

$first = <<<FIRST
        <ds>
                <name> INDISCARDS </name>
                <type> DERIVE </type>
                <minimal_heartbeat> 600 </minimal_heartbeat>
                <min> 0.0000000000e+00 </min>
                <max> 1.2500000000e+10 </max>
                <!-- PDP Status -->
		<last_ds> UNKN </last_ds>
		<value> 0.0000000000e+00 </value>
		<unknown_sec> 0 </unknown_sec>
        </ds>
        <ds>
                <name> OUTDISCARDS </name>
                <type> DERIVE </type>
                <minimal_heartbeat> 600 </minimal_heartbeat>
                <min> 0.0000000000e+00 </min>
                <max> 1.2500000000e+10 </max>
                <!-- PDP Status -->
		<last_ds> UNKN </last_ds>
		<value> 0.0000000000e+00 </value>
		<unknown_sec> 0 </unknown_sec>
        </ds>
        <ds>
                <name> INUNKNOWNPROTOS </name>
                <type> DERIVE </type>
                <minimal_heartbeat> 600 </minimal_heartbeat>
                <min> 0.0000000000e+00 </min>
                <max> 1.2500000000e+10 </max>
                <!-- PDP Status -->
                <last_ds> UNKN </last_ds>
                <value> 0.0000000000e+00 </value>
                <unknown_sec> 0 </unknown_sec>
        </ds>
        <ds>
                <name> INBROADCASTPKTS </name>
                <type> DERIVE </type>
                <minimal_heartbeat> 600 </minimal_heartbeat>
                <min> 0.0000000000e+00 </min>
                <max> 1.2500000000e+10 </max>
                <!-- PDP Status -->
                <last_ds> UNKN </last_ds>
                <value> 0.0000000000e+00 </value>
                <unknown_sec> 0 </unknown_sec>
        </ds>
        <ds>
                <name> OUTBROADCASTPKTS </name>
                <type> DERIVE </type>
                <minimal_heartbeat> 600 </minimal_heartbeat>
                <min> 0.0000000000e+00 </min>
                <max> 1.2500000000e+10 </max>
                <!-- PDP Status -->
                <last_ds> UNKN </last_ds>
                <value> 0.0000000000e+00 </value>
                <unknown_sec> 0 </unknown_sec>
        </ds>
        <ds>
                <name> INMULTICASTPKTS </name>
                <type> DERIVE </type>
                <minimal_heartbeat> 600 </minimal_heartbeat>
                <min> 0.0000000000e+00 </min>
                <max> 1.2500000000e+10 </max>
                <!-- PDP Status -->
                <last_ds> UNKN </last_ds>
                <value> 0.0000000000e+00 </value>
                <unknown_sec> 0 </unknown_sec>
        </ds>
        <ds>
                <name> OUTMULTICASTPKTS </name>
                <type> DERIVE </type>
                <minimal_heartbeat> 600 </minimal_heartbeat>
                <min> 0.0000000000e+00 </min>
                <max> 1.2500000000e+10 </max>
                <!-- PDP Status -->
                <last_ds> UNKN </last_ds>
                <value> 0.0000000000e+00 </value>
                <unknown_sec> 0 </unknown_sec>
        </ds>

<!-- Round Robin Archives -->
FIRST;



$second = <<<SECOND
			<ds>
				<primary_value> 0.0000000000e+00 </primary_value>
		                <secondary_value> NaN </secondary_value>
		                <value> NaN </value>
		                <unknown_datapoints> 0 </unknown_datapoints>
                        </ds>
			<ds>
				<primary_value> 0.0000000000e+00 </primary_value>
		                <secondary_value> NaN </secondary_value>
		                <value> NaN </value>
		                <unknown_datapoints> 0 </unknown_datapoints>
                        </ds>
                        <ds>
                                <primary_value> 0.0000000000e+00 </primary_value>
                                <secondary_value> NaN </secondary_value>
                                <value> NaN </value>
                                <unknown_datapoints> 0 </unknown_datapoints>
                        </ds>
                        <ds>
                                <primary_value> 0.0000000000e+00 </primary_value>
                                <secondary_value> NaN </secondary_value>
                                <value> NaN </value>
                                <unknown_datapoints> 0 </unknown_datapoints>
                        </ds>
                        <ds>
                                <primary_value> 0.0000000000e+00 </primary_value>
                                <secondary_value> NaN </secondary_value>
                                <value> NaN </value>
                                <unknown_datapoints> 0 </unknown_datapoints>
                        </ds>
                        <ds>
                                <primary_value> 0.0000000000e+00 </primary_value>
                                <secondary_value> NaN </secondary_value>
                                <value> NaN </value>
                                <unknown_datapoints> 0 </unknown_datapoints>
                        </ds>
                        <ds>
                                <primary_value> 0.0000000000e+00 </primary_value>
                                <secondary_value> NaN </secondary_value>
                                <value> NaN </value>
                                <unknown_datapoints> 0 </unknown_datapoints>
                        </ds>
	</cdp_prep>
SECOND;

$third = <<<THIRD
<v> NaN </v><v> NaN </v><v> NaN </v><v> NaN </v><v> NaN </v><v> NaN </v><v> NaN </v></row>
THIRD;




#---------------------------------------------------------------------------------------------------------
		if (!preg_match('/DISCARDS/', $fileC)){
			$fileC = str_replace('<!-- Round Robin Archives -->', $first, $fileC);
			$fileC = str_replace('</cdp_prep>', $second, $fileC);
			$fileC = str_replace('</row>', $third, $fileC);
			$tmpfname = tempnam("/tmp", "OBS");
			file_put_contents($tmpfname, $fileC);
			@unlink($file);
			$newfile = preg_replace("/(\d+)\.rrd/", "port-\\1.rrd", $file);
			@unlink($newfile);
			shell_exec($config['rrdtool'] . " restore $tmpfname  $newfile");
			unlink($tmpfname);

		}


	}

	echo("\n");

?>

