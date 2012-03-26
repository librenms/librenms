<?php

if($device['os_group'] == "unix") {

echo("Observium *nix Agent: ");

$port='6556';

$agent = fsockopen($device['hostname'], $port, $errno, $errstr, 10);

if(!$agent)
{
  echo "failed";
  exit();
} else {
  while (!feof($agent)) 
  { 
    $agent_raw .= fgets($agent, 128); 
  } 
}

if(!empty($agent_raw))
{
  foreach(explode("<<<", $agent_raw) as $section)
  {

    list($section, $data) = explode(">>>", $section);
    $agent_data[$section] = trim($data);
  }

  if(!empty($agent_data['rpm']))
  {
    echo("RPM Packages ");
    ## Build array of existing packages
    $manager = "rpm";

    $pkgs_db_db = dbFetchRows("SELECT * FROM `packages` WHERE `device_id` = ?", array($device['device_id']));
    foreach ($pkgs_db_db as $pkg_db) 
    { 
      $pkgs_db[$pkg_db['manager']][$pkg_db['name']][$pkg_db['arch']][$pkg_db['version']][$pkg_db['build']]['id'] = $pkg_db['pkg_id'];
      $pkgs_db[$pkg_db['manager']][$pkg_db['name']][$pkg_db['arch']][$pkg_db['version']][$pkg_db['build']]['status'] = $pkg_db['status'];
      $pkgs_db[$pkg_db['manager']][$pkg_db['name']][$pkg_db['arch']][$pkg_db['version']][$pkg_db['build']]['size'] = $pkg_db['size'];
      $pkgs_db_id[$pkg_db['pkg_id']] = $pkg_db['manager'] ."-".$pkg_db['name']."-".$pkg_db['arch']."-".$pkg_db['version']."-".$pkg_db['build'];
    }

    foreach(explode("\n", $agent_data['rpm']) as $package) 
    {
	list($name, $version, $build, $arch, $size) = explode(" ", $package);
        $pkgs[$manager][$name][$arch][$version][$build]['manager'] = $manager;
        $pkgs[$manager][$name][$arch][$version][$build]['name']    = $name;
        $pkgs[$manager][$name][$arch][$version][$build]['arch']    = $arch;
        $pkgs[$manager][$name][$arch][$version][$build]['version'] = $version;
        $pkgs[$manager][$name][$arch][$version][$build]['build']   = $build;
        $pkgs[$manager][$name][$arch][$version][$build]['size']    = $size;
        $pkgs[$manager][$name][$arch][$version][$build]['status']  = '1';
        $text = $manager."-".$name."-".$arch."-".$version."-".$build;
        $pkgs_id[] = $pkgs[$manager][$name][$arch][$version][$build];
    }

    foreach($pkgs_id as $pkg) {
      $name    = $pkg['name'];
      $version = $pkg['version'];
      $build   = $pkg['build'];
      $arch    = $pkg['arch'];
      $size    = $pkg['size'];
      
      #echo(str_pad($name, 20)." ".str_pad($version, 10)." ".str_pad($build, 10)." ".$arch."\n");
      #echo($name." ");

      if(is_array($pkgs_db[$pkg['manager']][$pkg['name']][$pkg['arch']][$pkg['version']][$pkg['build']]))
      {
        ### FIXME - packages_history table
        $id = $pkgs_db[$pkg['manager']][$pkg['name']][$pkg['arch']][$pkg['version']][$pkg['build']]['id'];
        if($pkgs_db[$pkg['manager']][$pkg['name']][$pkg['arch']][$pkg['version']][$pkg['build']]['status'] != '1')
        {
          $pkg_update['status']  = '1';
        }
        if($pkgs_db[$pkg['manager']][$pkg['name']][$pkg['arch']][$pkg['version']][$pkg['build']]['size'] != $size)
        {
          $pkg_update['size']  = $size; 
        }
        if(!empty($pkg_update))
        {
          dbUpdate($pkg_update, 'packages', '`pkg_id` = ?', array($id));
          echo("u");
        } else {
          echo(".");
        }
        unset($pkgs_db_id[$id]);
      } else {    
        if(count($pkgs[$manager][$name][$arch], 1) > "10" || count($pkgs_db[$manager][$name][$arch], 1) == '0') {
          dbInsert(array('device_id' => $device['device_id'], 'name' => $name, 'manager' => $manager,
                       'status' => 1, 'version' => $version, 'build' => $build, 'arch' => $arch, 'size' => $size), 'packages');
          echo("+".$name."-".$version."-".$build."-".$arch);
        } elseif(count($pkgs_db[$manager][$name][$arch], 1)) {
          $pkg_c = dbFetchRow("SELECT * FROM `packages` WHERE `device_id` = ? AND `manager` = ? AND `name` = ? and `arch` = ? ORDER BY version DESC, build DESC", 
                                    array($device['device_id'], $manager, $name, $arch));
	  echo("U(".$pkg_c['name']."-".$pkg_c['version']."-".$pkg_c['build']."|".$name."-".$version."-".$build.")");
          $pkg_update = array('version' => $version, 'build' => $build, 'status' => '1', 'size' => $size);
	  dbUpdate($pkg_update, 'packages', '`pkg_id` = ?', array($pkg_c['pkg_id']));
          unset($pkgs_db_id[$pkg_c['pkg_id']]);
        }
      }
       unset($pkg_update);
    }
  }

  foreach($pkgs_db_id as $id => $text) 
  {
    dbDelete('packages', "`pkg_id` =  ?", array($id));
    echo("-".$text);
  }

}

echo("\n");

}

?>
