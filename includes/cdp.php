<?php

class snmpCDP {
 
    var $community = "public";
    var $host = "";
 
    /**
     * Initialises the class.
     * $snmp = new snmpCDP('device','public');
     */

    function snmpCDP($host,$community) {
        $this->host=$host;
        $this->community=$community;
    }
 
    /**
     * Identify
     * Determines if the queried device is made by Cisco or not.
     * $type = $snmp->identify();
     * @return string 
     */
    function identify() {
        $ret=snmpget($this->host,$this->community,"SNMPv2-MIB::sysDescr.0");
        if (substr_count($ret,"Cisco") > 0) {
            return "cisco";
        } else {
            return "unknown";
        }
    }
 
    /**
     * Location
     * Returns the location string configured on the device.
     * $location = $snmp->location();
     * @return string 
     */
    function location() {
        return snmpget($this->host,$this->community,"SNMPv2-MIB::sysLocation.0");
    }
 
    /**
     * Function to determine if port is up or down from returned SNMP string.
     * @access private
     */
    function _isitup($text) {
        $x = substr($text,9);
        switch($x) {
            case "up(1)": return true; break;
            case "down(2)": return false; break;
        }
    }
 
    /**
     * Returns the type of port depending on the returned SNMP string.
     * @access private
     */
    function _porttype($text) {
        $x = substr($text,9);
        switch($x) {
            case "ethernetCsmacd(6)": return "ethernet"; break;
            case "propVirtual(53)": return "virtual"; break;
            case "propPointToPointSerial(22)": return "serial"; break;
            default: return $text; break;
        }
    }
 
    /**
     * Get Port List
     * Returns an array forming a list of the ports on the device, including name, alias and type.
     * The returned array is indexed by the port index in the SNMP tree.
     *
     * $snmp->getports();
     *
     * An example of the output:
     * Array
     * (
     *     [2] => Array
     *         (
     *             [desc] => GigabitEthernet0/1
     *             [alias] =>
     *             [type] => ethernet
     *         )
     * )
     * @return array 
     */
    function getports() {
        $nosint = @snmpget($this->host,$this->community,"IF-MIB::ifNumber.0");
        $ports  = @snmpwalk($this->host,$this->community,"IF-MIB::ifIndex");
        $results=array();
        foreach($ports as $port) {
            $x = substr($port,9);
            $admin = snmpget($this->host,$this->community,"IF-MIB::ifAdminStatus.$x");
            if ($this->_isitup($admin)==true) {
                $desc = substr(snmpget($this->host,$this->community,"IF-MIB::ifDescr.$x"),8);
                $alias = substr(snmpget($this->host,$this->community,"IF-MIB::ifAlias.$x"),8);
                $type = $this->_porttype(snmpget($this->host,$this->community,"IF-MIB::ifType.$x"));
                $results["$x"]=array("desc"=>$desc,"alias"=>$alias,"type"=>$type);
            }
        }
        return $results;
    }
 
    /**
     * Port Status
     * Returns the status of an individual port. Takes the SNMP index as the parameter.
     * if ($snmp->portstatus(2)==true) {
     *     echo "Port is up!";
     * }
     * @var integer $id 
     * @return bool 
     */
    function portstatus($id) {
        $adminStatus = @snmpget($this->host,$this->community,"IF-MIB::ifAdminStatus.$id");
        if ($this->_isitup($adminStatus)==true) {
            $operStatus = @snmpget($this->host,$this->community,"IF-MIB::ifOperStatus.$id");
            if ($this->_isitup($operStatus)==true) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
 
    /**
     * @access private
     */
    function _walkget($oid) {
        $ret = snmpwalk($this->host,$this->community,$oid);
        if (sizeof($ret) > 0) {
            return $ret[0];
        } else {
            return false;
        }
    }
 
    /**
     * Explore CDP
     * When supplied with the current port list from the device, it will determine each ports CDP status.
     * Returns an array containing the device name and port of the remote SNMP device detected via CDP,
     * assuming that it has the same community string as the initial device. The returned array is indexed
     * by the SNMP ports of the initial device.
     *
     * $ports = $snmp->getports();
     * $cdp = $snmp->explore_cdp($ports);
     * 
     * An example of the output will look like:
     * Array
     * (
     *     [2] => Array
     *         (
     *             [host] => second.device.hostname
     *             [port] => FastEthernet0/1
     *         )
     * )
     * @var array ports
     * @return array 
     */
    function explore_cdp($ports) {
        $cdpports=array();
        foreach($ports as $id => $port) {
            if ($ret = $this->_walkget("SNMPv2-SMI::enterprises.9.9.23.1.2.1.1.6.$id")) {
                // this port is connected to another cisco!
                $remote_id = substr($ret,9,strlen($ret)-10);
                if ($ret = $this->_walkget("SNMPv2-SMI::enterprises.9.9.23.1.2.1.1.7.$id")) {
                    $remote_port = substr($ret,9,strlen($ret)-10);
                }
                #echo "$this->host($port[desc]) is connected to $remote_id($remote_port)\n";
                $cdpports[$id]=array('host'=>$remote_id,'port'=>$remote_port);
            }
        }
        return $cdpports;
    }
 
}
 
?>
