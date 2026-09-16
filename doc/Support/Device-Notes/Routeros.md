This agent script lets LibreNMS run a script on a Mikrotik RouterOS device. The script collects the VLAN information from `/interface/vlan/` and from `/interface/bridge/vlan/`.

## Installation

- Go to https://github.com/librenms/librenms-agent/tree/master/snmp/Routeros
- Copy the content of the `LNMS_vlans.scr` file into a script on the RouterOS device. Name the script `LNMS_vlans`. This is NOT the same as a txt file in the Files section of the device.
- If the creation of the script is not clear, download the `LNMS_vlans.scr` file. Remove the `.scr` extension from the name. Then copy this file to each Mikrotik device to monitor.
- Open a terminal on each device and run this command: ```{ :global txtContent [/file get LNMS_vlans contents]; /system/script/add name=LNMS_vlans owner=admin policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon source=$txtContent ;}```  The command imports the content of the txt file into a script with the name `LNMS_vlans`.
- Enable an SNMP community with READ and WRITE capability. Without WRITE capability, LibreNMS cannot run the script. We recommend SNMP v3 for this purpose.
- Discover or force a rediscovery of your Mikrotik devices. After the discovery, the vlans menu appears for the device in LibreNMS.

### *** IMPORTANT NOTE ***

Permit SNMP traffic only from a small set of IP addresses. These are the addresses of LibreNMS and the related systems, usually one /32 address for each. The write permission makes an attack on the device possible. An attacker can remove all firewall filters or change the admin credentials.

### Theory of operation:

The Mikrotik VLAN discovery plugin uses the capability of RouterOS to start a script through SNMP.

LibreNMS first tests whether the script exists. If the script is present, LibreNMS starts the `LNMS_vlans` script.

The script collects information from:
- `/interface/bridge/vlan` for the tagged ports in the bridge
- `/interface/bridge/vlan` for the current untagged ports in the bridge
- `/interface/bridge/port` for the port PVID, that is the untagged VLAN, in the bridge
- `/interface/vlan` for the VLAN interfaces

The script then sends the information to LibreNMS over SNMP.

The protocol is:
type,vlanId,ifName <cr>

For example:
`T,254,ether1` means tagged VLAN 254 on port ether1.

`U,100,wlan2` means untagged VLAN 100 on port wlan2.
