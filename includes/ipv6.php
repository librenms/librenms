<?php

define("NET_IPV6_NO_NETMASK_MSG", "Netmask length not found");
define("NET_IPV6_NO_NETMASK", 10);
define("NET_IPV6_UNASSIGNED", 1);
define("NET_IPV6_RESERVED",  11);
define("NET_IPV6_RESERVED_NSAP", 12);
define("NET_IPV6_RESERVED_IPX", 13);
define("NET_IPV6_RESERVED_UNICAST_GEOGRAPHIC", 14);
define("NET_IPV6_UNICAST_PROVIDER", 22);
define("NET_IPV6_MULTICAST", 31);
define("NET_IPV6_LOCAL_LINK", 42);
define("NET_IPV6_LOCAL_SITE", 43);
define("NET_IPV6_UNKNOWN_TYPE", 1001);

class Net_IPv6 {

    function removeNetmaskSpec($ip) {
        $addr = $ip;
        if(false !== strpos($ip, '/')) {
            $elements = explode('/', $ip);
            if(2 == count($elements)) {
                $addr = $elements[0];
            }
        }
        return $addr;
    }

    function getNetmaskSpec($ip) {
        $spec = '';
        if(false !== strpos($ip, '/')) {
            $elements = explode('/', $ip);
            if(2 == count($elements)) {
                $spec = $elements[1];
            }
        }
        return $spec;
    }

    // }}}
    // {{{ getNetmask()

    function getNetmask($ip, $bits = null) {
        if(null==$bits) {
            $elements = explode('/', $ip);
            if(2 == count($elements)) {
                $addr = $elements[0];
                $bits = $elements[1];
            } else {
                require_once 'PEAR.php';
                return PEAR::raiseError(NET_IPV6_NO_NETMASK_MSG, NET_IPV6_NO_NETMASK);
            }
        } else {
            $addr = $ip;
        }
        $addr = Net_IPv6::uncompress($addr);
        $binNetmask = str_repeat('1', $bits).str_repeat('0', 128 - $bits);
        return Net_IPv6::_bin2Ip(Net_IPv6::_ip2Bin($addr) & $binNetmask);
    }

    function isInNetmask($ip, $netmask, $bits=null) {
        // try to get the bit count
        if(null == $bits) {
            $elements = explode('/', $ip);
            if(2 == count($elements)) {
                $ip = $elements[0];
                $bits = $elements[1];
            } else if(null == $bits) {
                $elements = explode('/', $netmask);
                if(2 == count($elements)) {
                     $netmask = $elements[0];
                     $bits = $elements[1];
                }
                if(null == $bits) {
                    require_once 'PEAR.php';
                    return PEAR::raiseError(NET_IPV6_NO_NETMASK_MSG, NET_IPV6_NO_NETMASK);
                }
            }
        }

        $binIp = Net_IPv6::_ip2Bin(Net_IPv6::removeNetmaskSpec($ip));
        $binNetmask = Net_IPv6::_ip2Bin(Net_IPv6::removeNetmaskSpec($netmask));
        if(null != $bits && "" != $bits && 0 == strncmp( $binNetmask, $binIp, $bits)) {
            return true;
        }
        return false;
    }


    function getAddressType($ip) {
        $ip = Net_IPv6::removeNetmaskSpec($ip);
        $binip = Net_IPv6::_ip2Bin($ip);
        if(0 == strncmp('1111111010', $binip, 10)) {
            return NET_IPV6_LOCAL_LINK;
        } else if(0 == strncmp('1111111011', $binip, 10)) {
            return NET_IPV6_LOCAL_SITE;
        } else if (0 == strncmp('111111100', $binip, 9)) {
            return NET_IPV6_UNASSIGNED;
        } else if (0 == strncmp('11111111', $binip, 8)) {
            return NET_IPV6_MULTICAST;
        }  else if (0 == strncmp('00000000', $binip, 8)) {
            return NET_IPV6_RESERVED;
        } else if (0 == strncmp('00000001', $binip, 8) ||
                   0 == strncmp('1111110', $binip, 7)) {
            return NET_IPV6_UNASSIGNED;
        } else if (0 == strncmp('0000001', $binip, 7)) {
            return NET_IPV6_RESERVED_NSAP;
        } else if (0 == strncmp('0000010', $binip, 7)) {
            return NET_IPV6_RESERVED_IPX;;
        } else if (0 == strncmp('0000011', $binip, 7) ||
                    0 == strncmp('111110', $binip, 6) ||
                    0 == strncmp('11110', $binip, 5) ||
                    0 == strncmp('00001', $binip, 5) ||
                    0 == strncmp('1110', $binip, 4) ||
                    0 == strncmp('0001', $binip, 4) ||
                    0 == strncmp('001', $binip, 3) ||
                    0 == strncmp('011', $binip, 3) ||
                    0 == strncmp('101', $binip, 3) ||
                    0 == strncmp('110', $binip, 3)) {
            return NET_IPV6_UNASSIGNED;
        } else if (0 == strncmp('010', $binip, 3)) {
            return NET_IPV6_UNICAST_PROVIDER;
        }  else if (0 == strncmp('100', $binip, 3)) {
            return NET_IPV6_RESERVED_UNICAST_GEOGRAPHIC;
        }
        return NET_IPV6_UNKNOWN_TYPE;
    }


    function Uncompress($ip) {
        $netmask = Net_IPv6::getNetmaskSpec($ip);
        $uip = Net_IPv6::removeNetmaskSpec($ip);

        $c1 = -1;
        $c2 = -1;
        if (false !== strpos($uip, '::') ) {
            list($ip1, $ip2) = explode('::', $uip);

            if(""==$ip1) {
                $c1 = -1;
            } else {
               	$pos = 0;
                if(0 < ($pos = substr_count($ip1, ':'))) {
                    $c1 = $pos;
                } else {
                    $c1 = 0;
                }
            }
            if(""==$ip2) {
                $c2 = -1;
            } else {
                $pos = 0;
                if(0 < ($pos = substr_count($ip2, ':'))) {
                    $c2 = $pos;
                } else {
                    $c2 = 0;
                }
            }
            if(strstr($ip2, '.')) {
                $c2++;
            }
            if(-1 == $c1 && -1 == $c2) { // ::
                $uip = "0:0:0:0:0:0:0:0";
            } else if(-1==$c1) {              // ::xxx
                $fill = str_repeat('0:', 7-$c2);
                $uip =  str_replace('::', $fill, $uip);
            } else if(-1==$c2) {              // xxx::
                $fill = str_repeat(':0', 7-$c1);
                $uip =  str_replace('::', $fill, $uip);
            } else {                          // xxx::xxx
                $fill = str_repeat(':0:', 6-$c2-$c1);
                $uip =  str_replace('::', $fill, $uip);
                $uip =  str_replace('::', ':', $uip);
            }
        }
        if('' != $netmask) {
                $uip = $uip.'/'.$netmask;
        }
        return $uip;
    }

    function Compress($ip)	{

        $netmask = Net_IPv6::getNetmaskSpec($ip);
        $ip = Net_IPv6::removeNetmaskSpec($ip);
        if (!strstr($ip, '::')) {
             $ipp = explode(':',$ip);
             for($i=0; $i<count($ipp); $i++) {
                 $ipp[$i] = dechex(hexdec($ipp[$i]));
             }
            $cip = ':' . join(':',$ipp) . ':';
			preg_match_all("/(:0)+/", $cip, $zeros);
    		if (count($zeros[0])>0) {
				$match = '';
				foreach($zeros[0] as $zero) {
    				if (strlen($zero) > strlen($match))
						$match = $zero;
				}
				$cip = preg_replace('/' . $match . '/', ':', $cip, 1);
			}
			$cip = preg_replace('/((^:)|(:$))/', '' ,$cip);
            $cip = preg_replace('/((^:)|(:$))/', '::' ,$cip);
         }
         if('' != $netmask) {
                $cip = $cip.'/'.$netmask;
         }
         return $cip;
    }

    function SplitV64($ip) {
        $ip = Net_IPv6::removeNetmaskSpec($ip);
        $ip = Net_IPv6::Uncompress($ip);
        if (strstr($ip, '.')) {
            $pos = strrpos($ip, ':');
            $ip{$pos} = '_';
            $ipPart = explode('_', $ip);
            return $ipPart;
        } else {
            return array($ip, "");
        }
    }

    function checkIPv6($ip) {
        $ip = Net_IPv6::removeNetmaskSpec($ip);
        $ipPart = Net_IPv6::SplitV64($ip);
        $count = 0;
        if (!empty($ipPart[0])) {
            $ipv6 =explode(':', $ipPart[0]);
            for ($i = 0; $i < count($ipv6); $i++) {
                $dec = hexdec($ipv6[$i]);
                $hex = strtoupper(preg_replace("/^[0]{1,3}(.*[0-9a-fA-F])$/", "\\1", $ipv6[$i]));
                if ($ipv6[$i] >= 0 && $dec <= 65535 && $hex == strtoupper(dechex($dec))) {
                    $count++;
                }
            }
            if (8 == $count) {
                return true;
            } elseif (6 == $count and !empty($ipPart[1])) {
                $ipv4 = explode('.',$ipPart[1]);
                $count = 0;
                for ($i = 0; $i < count($ipv4); $i++) {
                    if ($ipv4[$i] >= 0 && (integer)$ipv4[$i] <= 255 && preg_match("/^\d{1,3}$/", $ipv4[$i])) {
                        $count++;
                    }
                }
                if (4 == $count) {
                    return true;
                }
            } else {
                return false;
            }

        } else {
            return false;
        }
    }

    function _ip2Bin($ip) {
        $binstr = '';
        $ip = Net_IPv6::removeNetmaskSpec($ip);
        $ip = Net_IPv6::Uncompress($ip);
        $parts = explode(':', $ip);
        foreach($parts as $v) {
            $str = base_convert($v, 16, 2);
            $binstr .= str_pad($str, 16, '0', STR_PAD_LEFT);
        }
        return $binstr;
    }

    function _bin2Ip($bin) {
        $ip = "";
        if(strlen($bin)<128) {
            $bin = str_pad($str, 128, '0', STR_PAD_LEFT);
        }
        $parts = str_split($bin, "16");
        foreach($parts as $v) {
            $str = base_convert($v, 2, 16);
            $ip .= $str.":";
        }
        $ip = substr($ip, 0,-1);
        return $ip;
    }

}

?>
