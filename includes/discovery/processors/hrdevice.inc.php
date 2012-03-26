<?php

echo("hrDevice ");
$hrDevice_oids = array('hrDevice','hrProcessorLoad');
unset($hrDevice_array);
foreach ($hrDevice_oids as $oid) { $hrDevice_array = snmpwalk_cache_oid($device, $oid, $hrDevice_array, "HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES"); }
if (is_array($hrDevice_array))
{
  foreach ($hrDevice_array as $index => $entry)
  {
    # Workaround bsnmpd reporting CPUs as hrDeviceOther (fuck you, FreeBSD.)
    if ($entry['hrDeviceType'] == "hrDeviceOther" && preg_match('/^cpu[0-9]+:/', $entry['hrDeviceDescr']))
    {
      $entry['hrDeviceType'] = "hrDeviceProcessor";
    }
    if ($entry['hrDeviceType'] == "hrDeviceProcessor")
    {
      $hrDeviceIndex = $entry['hrDeviceIndex'];

      $usage_oid = ".1.3.6.1.2.1.25.3.3.1.2." . $index;
      $usage = $entry['hrProcessorLoad'];

      #### What is this for? I have forgotten. What has : in its hrDeviceDescr?
      #### Set description to that found in hrDeviceDescr, first part only if containing a :
      $descr_array = explode(":",$entry['hrDeviceDescr']);
      if ($descr_array['1']) { $descr = $descr_array['1']; } else { $descr = $descr_array['0']; }

      ### Workaround to set fake description for Mikrotik who don't populate hrDeviceDescr
      if ($device['os'] == "routeros" && !isset($entry['hrDeviceDescr'])) { $descr = "Processor"; }

      $descr = str_replace("CPU ", "", $descr);
      $descr = str_replace("(TM)", "", $descr);
      $descr = str_replace("(R)", "", $descr);

      $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("hrProcessor-" . $index . ".rrd");
      $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("processor-hr-" . $index . ".rrd");

      if ($debug) { echo("$old_rrd $new_rrd"); }
      if (is_file($old_rrd))
      {
        rename($old_rrd,$new_rrd);
        echo("Moved RRD ");
      }

      if ($device['os'] == "arista-eos" && $index == "1") { unset($descr); }

      if (isset($descr) && $descr != "An electronic chip that makes the computer work.")
      {
        discover_processor($valid['processor'], $device, $usage_oid, $index, "hr", $descr, "1", $usage, NULL, $hrDeviceIndex);
      }
      unset($old_rrd,$new_rrd,$descr,$entry,$usage_oid,$index,$usage,$hrDeviceIndex,$descr_array);
    }
    unset($entry);
  }
  unset($hrDevice_oids, $hrDevice_array, $oid);
}

## End hrDevice Processors

?>
