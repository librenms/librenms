<?php

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
