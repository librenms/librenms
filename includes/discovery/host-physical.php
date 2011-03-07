<?php
/* FIXME: dead file */

 echo("Physical Inventory : ");

# if($config['enable_inventory']) {

  $ents_cmd  = $config['snmpwalk'] . " -M " . $config['mibdir'] . " -m HOST-RESOURCES-MIB -O qn -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['$port'] . " ";
  $ents_cmd .= "hrDeviceIndex | sed s/.1.3.6.1.2.1.25.3.2.1.1.//g | cut -f 1 -d\" \"";

  $ents  = trim(`$ents_cmd | grep -v o`);

 foreach(explode("\n", $ents) as $hrDeviceIndex) {

    $ent_data  = $config['snmpget'] . " -M " . $config['mibdir'] ." -m HOST-RESOURCES-MIB:ENTITY-MIB -Ovqs -";
    $ent_data  .= $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] .":".$device['port'];
    $ent_data .= " hrDeviceType." . $entPhysicalIndex;
    $ent_data .= " hrDeviceDescr." . $entPhysicalIndex;
    $ent_data .= " entPhysicalParentRelPos." . $entPhysicalIndex;
    $ent_data .= " entAliasMappingIdentifier." . $entPhysicalIndex. ".0";

 }

#}

?>
