source: doc/Support/Device-Notes/Routeros.md
path: blob/master/doc/

This is attempt to get vlans information from Mikrotik RouterOS.

# Installation

Installation is very simple. On mikrotik we need:
1. one script, named "LNMS_vlans"
2. snmp community with write permission

Copy the scripts from librenms-agent/snmp/Routeros and place in /system/scripts
Set snmp community to have WRITE permission in /snmp/community

It is strongly recomended that snmp allowed address is narrowed down to /32 because write permission could allow attack on device

Theory of operation:

Mikrotik vlan discovery plugin using ability of ROS to "fire up" a script trough SNMP
At first, LibreNMS check for existence of script, and if it present, it will be started
Sript try to gather information from:
a. /interface/bridge/vlan for tagged ports inside bridge
b. /interface/bridge/vlan for currently untagged ports inside bridge
c. /interface/bridge/port for ports PVID (untagged) inside bridge
d. /interface/vlan for plain (old style) vlans

after information is gathered, it is transmitted to LibreNMS over SNMP
protocol is:
type,vlanId,ifName <cr>

i.e: 
T,254,ether1 is translated to Tagged vlan 254 on port ether1
U,100,wlan2 is translated to Untagged vlan 100 on port wlan2
