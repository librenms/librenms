<?php


  $translate_ifOperStatus = array(
   	"1" => "up",
	"2" => "down",
	"3" => "testing",
	"4" => "unknown",
	"5" => "dormant",
	"6" => "notPresent",
	"7" => "lowerLayerDown",
  );

  function translate_ifOperStatus ($ifOperStatus) {
    global $translate_ifOperStatus;
    if($translate_ifOperStatus['$ifOperStatus']) {
      $ifOperStatus = $translate_ifOperStatus['$ifOperStatus'];
    }
    return $ifOperStatus;
  }

  $translate_ifAdminStatus = array(
        "1" => "up",
        "2" => "down",
        "3" => "testing",
  );

  function translate_ifAdminStatus ($ifAdminStatus) {
    global $translate_ifAdminStatus;
    if($translate_ifAdminStatus['$ifAdminStatus']) {
      $ifAdminStatus = $translate_ifAdminStatus['$ifAdminStatus'];
    }
    return $ifAdminStatus;
  }



  $rewrite_ios_features = array(
        "PK9S" => "IP w/SSH LAN Only",
        "LANBASEK9" => "Lan Base Crypto",
        "LANBASE" => "Lan Base",
        "ADVENTERPRISEK9" => "Advanced Enterprise Crypto",
        "ADVSECURITYK9" => "Advanced Security Crypto",
        "K91P" => "Provider Crypto",
        "K4P" => "Provider Crypto",
        "ADVIPSERVICESK9" => "Adv IP Services Crypto",
        "ADVIPSERVICES" => "Adv IP Services",
        "IK9P" => "IP Plus Crypto",
        "K9O3SY7" => "IP ADSL FW IDS Plus IPSEC 3DES",
        "SPSERVICESK9" => "SP Services Crypto",
        "PK9SV" => "IP MPLS/IPV6 W/SSH + BGP",
        "IS" => "IP Plus",
        "IPSERVICESK9" => "IP Services Crypto",
        "BROADBAND" => "Broadband",
        "IPBASE" => "IP Base",
        "IPSERVICE" => "IP Services",
        "P" => "Service Provider",
        "P11" => "Broadband Router",
        "G4P5" => "NRP",
        "JK9S" => "Enterprise Plus Crypto",
        "IK9S" => "IP Plus Crypto",
        "JK" => "Enterprise Plus",
        "I6Q4L2" => "Layer 2",
        "I6K2L2Q4" => "Layer 2 Crypto",
        "C3H2S" => "Layer 2 SI/EI",
        "_WAN" => " + WAN",
  );



  $rewrite_shortif = array (
    'tengigabitethernet' => 'Te',
    'gigabitethernet' => 'Gi',
    'fastethernet' => 'Fa',
    'ethernet' => 'Et',
    'serial' => 'Se',
    'pos' => 'Pos',
    'port-channel' => 'Po',
    'atm' => 'Atm',
    'null' => 'Null',
    'loopback' => 'Lo',
    'dialer' => 'Di',
    'vlan' => 'Vlan',
    'tunnel' => 'Tunnel',
  );

  $rewrite_iftype = array (
    '/^frameRelay$/' => 'Frame Relay',
    '/^ethernetCsmacd$/' => 'Ethernet',
    '/^softwareLoopback$/' => 'Loopback',
    '/^tunnel$/' => 'Tunnel',
    '/^propVirtual$/' => 'Virtual Int',
    '/^ppp$/' => 'PPP',
    '/^ds1$/' => 'DS1',
    '/^pos$/' => 'POS',
    '/^sonet$/' => 'SONET',
    '/^slip$/' => 'SLIP',
    '/^mpls$/' => 'MPLS Layer',
    '/^l2vlan$/' => 'VLAN Subif',
    '/^atm$/' => 'ATM',
    '/^aal5$/' => 'ATM AAL5',
    '/^atmSubInterface$/' => 'ATM Subif',
    '/^propPointToPointSerial$/' => 'PtP Serial',
  );

  $rewrite_ifname = array (
    'ether' => 'Ether',
    'gig' => 'Gig',
    'fast' => 'Fast',
    'ten' => 'Ten',
    '-802.1q vlan subif' => '',
    '-802.1q' => '',
    'bvi' => 'BVI',
    'vlan' => 'Vlan',
    'ether' => 'Ether',
    'tunnel' => 'Tunnel',
    'serial' => 'Serial',
    '-aal5 layer' => ' aal5',
    'null' => 'Null',
    'atm' => 'ATM',
    'port-channel' => 'Port-Channel',
    'dial' => 'Dial',
    'hp procurve switch software loopback interface' => 'Loopback Interface',
    'control plane interface' => 'Control Plane',
    'loop' => 'Loop',
  );



// Specific rewrite functions

function makeshortif($if)
{
        global $rewrite_shortif;
        $if = fixifName ($if);
        $if = strtolower($if);
        $if = array_str_replace($rewrite_shortif, $if);
        return $if;
}

function rewrite_ios_features ($features)
{
        global $rewrite_ios_features;
        $type = array_preg_replace($rewrite_ios_features, $features);
        return ($features);
}


function fixiftype ($type)
{
        global $rewrite_iftype;
        $type = array_preg_replace($rewrite_iftype, $type);
        return ($type);
}

function fixifName ($inf)
{
        global $rewrite_ifname;
        $inf = strtolower($inf);
        $inf = array_str_replace($rewrite_ifname, $inf);
        return $inf;
}


// Underlying rewrite functions


  function array_str_replace($array, $string) 
  {
    foreach ($array as $search => $replace) {
      $string = str_replace($search, $replace, $string);
    }
    return $string;
  }

  function array_preg_replace($array, $string) 
  {
    foreach ($array as $search => $replace) {
      $string = preg_replace($search, $replace, $string);
    }
    return $string;
  }



?>
