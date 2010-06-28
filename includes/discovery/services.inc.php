<?php
  $id = $device['device_id'];
  $hostname = $device['hostname'];
  $community = $device['community'];
  $snmpver = $device['snmpver'];
  $port = $device['port'];

  echo("Services: ");

  $known_services = array(22 => "ssh", 25 => "smtp", 53 => "dns", 80 => "http", 110 => "pop", 143 => "imap");

  function add_service($service) {
    global $id;
    global $hostname;
    echo "$service ";
    $sql = "INSERT INTO `services` (`device_id`,`service_ip`,`service_type`,`service_desc`,`service_param`,`service_ignore`) 
                          VALUES ('" . mres($id). "','" . mres($hostname) . "','" . mres($service) . "',
                                  '" . mres("auto discovered: $service") . "','" . mres("") . "','0')";

    $query = mysql_query($sql);
  }

  ## Services
  if($device['type'] == "server") {
    $oids = shell_exec($config['snmpwalk'] . " -".$device['snmpver']." -CI -Osqn -c ".$community." ".$hostname.":".$port." .1.3.6.1.2.1.6.13.1.1.0.0.0.0");
    $oids = trim($oids);
    foreach(explode("\n", $oids) as $data) {
     $data = trim($data);
     if($data) {
      list($oid, $tcpstatus) = explode(" ", $data);
      if (trim($tcpstatus)=="listen") {
       $split_oid = explode('.',$oid);
       $tcp_port = $split_oid[count($split_oid)-6];
	if($known_services[$tcp_port]) { add_service($known_services[$tcp_port]); };
      }
     }
    } 
  } ## End Services

echo("\n");

?>
