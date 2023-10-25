This agent script will allow LibreNMS to run a script on a Mikrotik / RouterOS device to gather the vlan information from both /interface/vlan/ and /interface/bridge/vlan/

## Installation

- Go to https://github.com/librenms/librenms-agent/tree/master/snmp/Routeros
- Copy and paste the contents of LNMS_vlans.scr file into a script within a RouterOS device.  Name this script LNMS_vlans. (This is NOT the same thing as creating a txt file and importing it into the Files section of the device)
- If you're unsure how to create the script.  Download the LNMS_vlans.scr file.  Rename to remove the .scr extension.  Copy this file onto all the Mikrotik devices you want to monitor.
- Open a Terminal / CLI on each tik and run this.  ```{ :global txtContent [/file get LNMS_vlans contents]; /system/script/add name=LNMS_vlans owner=admin policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon source=$txtContent ;}```  This will import the contents of that txt file into a script named LNMS_vlans
- Enable an SNMP community that has both READ and WRITE capabilities. This is important, otherwise, LibreNMS will not be able to run the above script. It is recommended to use SNMP v3 for this. 
- Discover / Force rediscover your Mikrotik devices. After discovery has been completed the vlans menu should appear within LibreNMS for the device.

### *** IMPORTANT NOTE ***

It is strongly recommended that SNMP service only be allowed to be communicated on a very limited set of IP addresses that LibreNMS and related systems will be coming from. (usually /32 address for each) because the write permission could allow an attack on a device. (such as dropping all firewall filters or changing the admin credentials) 

### Theory of operation:

Mikrotik vlan discovery plugin using the ability of ROS to "fire up" a script through SNMP.

At first, LibreNMS check for the existence of the script, and if it is present, it will start the LNMS_vlans script. 

The script will gather information from:
- /interface/bridge/vlan for tagged ports inside bridge
- /interface/bridge/vlan for currently untagged ports inside bridge
- /interface/bridge/port for ports PVID (untagged) inside bridge
- /interface/vlan for vlan interfaces

after the information is gathered, it is transmitted to LibreNMS over SNMP

protocol is:
type,vlanId,ifName <cr>

i.e: 
T,254,ether1 is translated to Tagged vlan 254 on port ether1

U,100,wlan2 is translated to Untagged vlan 100 on port wlan2
