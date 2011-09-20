<?php
	include("includes/defaults.inc.php");
	include("config.php");

	$basepath = $config['rrd_dir'].'/';

	$files = (sensor_getDirectoryTree($basepath));

	$count = count($files);

	echo($count . " Files \n");
	$start = date("U");
        $i = 0;
	foreach ($files as $file){
		sensor_fixRdd($file);
		$i++;
		if (date("U") - $start > 1)
			echo(round(($i / $count) * 100, 2) . "%  \r");
	}

	function sensor_getDirectoryTree( $outerDir, &$files = array()){

		$dirs = array_diff( scandir( $outerDir ), Array( ".", ".." ) );
    		foreach ( $dirs as $d ){
        		if (is_dir($outerDir."/".$d)  ){
				sensor_getDirectoryTree($outerDir.'/'. $d, $files);
        		} else {
				if ((preg_match('/^fan-.*.rrd$/', $d)) ||
				     (preg_match('/^current-.*.rrd$/', $d)) ||
				     (preg_match('/^freq-.*.rrd$/', $d)) ||
				     (preg_match('/^humidity-.*.rrd$/', $d)) ||
				     (preg_match('/^volt-.*.rrd$/', $d)) ||
				     (preg_match('/^temp-.*.rrd$/', $d)) )
					array_push($files, preg_replace('/\/+/', '/',  $outerDir.'/'. $d));

			}
    		}
  		return $files;
	}


	function sensor_fixRdd($file){
		global $config;
                global $rrdcached;
		$fileC = shell_exec( "{$config['rrdtool']} dump $file $rrdcached");
		if (preg_match('/<name> fan/', $fileC))
		{
		  shell_exec("{$config['rrdtool']} tune $file $rrdcached -r fan:sensor");
		  rename($file,str_replace('/fan-','/fanspeed-',$file));
		}
		elseif (preg_match('/<name> volt/', $fileC))
		{
		  shell_exec("{$config['rrdtool']} tune $file $rrdcached -r volt:sensor");
		  rename($file,str_replace('/volt-','/voltage-',$file));
		}
		elseif (preg_match('/<name> current/', $fileC))
		{
		  shell_exec("{$config['rrdtool']} tune $file $rrdcached -r current:sensor");
		}
		elseif (preg_match('/<name> freq/', $fileC))
		{
		  shell_exec("{$config['rrdtool']} tune $file $rrdcached -r freq:sensor");
		  rename($file,str_replace('/freq-','/frequency-',$file));
		}
		elseif (preg_match('/<name> humidity/', $fileC))
		{
		  shell_exec("{$config['rrdtool']} tune $file $rrdcached -r humidity:sensor");
		}
		elseif (preg_match('/<name> temp/', $fileC))
		{
		  shell_exec("{$config['rrdtool']} tune $file $rrdcached -r temp:sensor");
		  rename($file,str_replace('/temp-','/temperature-',$file));
		}
	}

	echo("\n");

?>

