#!/usr/bin/php
<?php

include("config.php");
include("includes/functions.php");

$dataHandle = fopen("http://www.observernms.org/latest.php", r);

if($dataHandle)
{
	while (!feof($dataHandle))
        {
  		$data.= fread($dataHandle, 4096);
	}
	if($data)
	{
 		list($major, $minor, $release) = explode(".", $data);
                list($cur, $tag) = explode("-", $config['version']);
	        list($cur_major, $cur_minor, $cur_release) = explode(".", $cur);

     	     if($argv[1] == "--cron") {
             
		shell_exec("echo $major.$minor.$release > rrd/version.txt ");
    
             } else {

                echo("Current Version $cur_major.$cur_minor.$cur_release \n");

		if($major > $cur_major) {
	          echo("New major release : $major.$minor.$release");
                } elseif ($minor > $cur_minor) {
	          echo("New minor release : $major.$minor.$release");
                } elseif ($release > $cur_release) {
                  echo("New trivial release : $major.$minor.$release");
                } else {
		  echo("Your release is up to date\n");
                }
             }
	}
	fclose($dataHandle);
}

?>
