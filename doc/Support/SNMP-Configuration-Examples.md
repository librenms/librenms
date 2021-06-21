source: Support/SNMP-Configuration-Examples.md
path: blob/master/doc/

# SNMP configuration examples

Table of Content:

- [Devices](#devices)
  - [Cisco](#cisco)
    - [Adaptive Security Appliance (ASA)](#adaptive-security-appliance-asa)
    - [IOS / IOS XE](#ios--ios-xe)
    - [NX-OS](#nx-os)
    - [Wireless LAN Controller (WLC)](#wireless-lan-controller-wlc)
  - [HPE 3PAR](#hpe3par)
  - [Inform OS 3.2.x](#inform-os-32x)
  - [Infoblox](#infoblox)
    - [NIOS 7.x+](#nios-7x)
  - [Juniper](#juniper)
    - [Junos OS](#junos-os)
  - [Mikrotik](#mikrotik)
    - [RouterOS 6.x](#routeros-6x)
  - [Palo Alto](#palo-alto)
    - [PANOS 6.x/7.x](#panos-6x7x)
  - [Ubiquiti](#ubiquiti)
    - [EdgeOs](#edgeos)
  - [VMware](#vmware)
    - [ESX/ESXi 5.x/6.x](#esxesxi-5x6x)
    - [VCenter 6.x](#vcenter-6x)
- [Operating systems](#operating-systems)
  - [Linux (snmpd v2)](#linux-snmpd)
  - [Linux (snmpd v3)](#linux-snmpd-v3)
  - [Windows Server 2008 R2](#windows-server-2008-r2)
  - [Windows Server 2012 R2 and newer](#windows-server-2012-r2-and-newer)
  - [Mac OSX](#Mac-OSX)

## Devices

### Cisco

#### Adaptive Security Appliance (ASA)

ASDM

1. Launch ASDM and connect to your device
1. Go to Configuration > Management Access > SNMP
1. Add your community string
1. Add in the "SNMP Host Access List" section your LibreNMS server IP address
1. Click Apply and Save

CLI

```
# SNMPv2c

snmp-server community <YOUR-COMMUNITY>
snmp-server contact <YOUR-CONTACT>
snmp-server location <YOUR-LOCATION>
snmp-server host <INTERFACE> <LIBRENMS-IP> poll community <YOUR-COMMUNITY> version 2c

# SNMPv3

snmp-server group <GROUP-NAME> v3 priv
snmp-server user <USER-NAME> <GROUP-NAME> v3 auth sha <AUTH-PASSWORD> priv aes 128 <PRIV-PASSWORD>
snmp-server contact <YOUR-CONTACT>
snmp-server location <YOUR-LOCATION>
snmp-server host <INTERFACE> <LIBRENMS-IP> poll version 3 <USER-NAME>
```
>Note: If the device is unable to find the SNMP user, reboot the ASA. Once rebooted, continue the steps as normal.

#### IOS / IOS XE

```
# SNMPv2c

snmp-server community <YOUR-COMMUNITY> RO
snmp-server contact <YOUR-CONTACT>
snmp-server location <YOUR-LOCATION>

# SNMPv3

snmp-server group <GROUP-NAME> v3 priv
snmp-server user <USER-NAME> <GROUP-NAME> v3 auth sha <AUTH-PASSWORD> priv aes 128 <PRIV-PASSWORD>
snmp-server contact <YOUR-CONTACT>
snmp-server location <YOUR-LOCATION>

# Note: The following is also required if using SNMPv3 and you want to populate the FDB table.

snmp-server group <GROUP-NAME> v3 priv context vlan- match prefix
```
>Note: If the device is unable to find the SNMP user, reboot the ASA. Once rebooted, continue the steps as normal.

#### NX-OS

```
# SNMPv2c

snmp-server community <YOUR-COMMUNITY> RO
snmp-server contact <YOUR-CONTACT>
snmp-server location <YOUR-LOCATION>

# SNMPv3

snmp-server user <USER-NAME> <GROUP-NAME> v3 auth sha <AUTH-PASSWORD> priv aes 128 <PRIV-PASSWORD>
snmp-server contact <YOUR-CONTACT>
snmp-server location <YOUR-LOCATION>
```

#### Wireless LAN Controller (WLC)

1. Access the web admin page and log in
1. If you are running version 8.1 and later, on the new dashboard click "Advanced"
1. Go to management Tab
1. On SNMP sub-menu, select "Communities"
1. Click "New..."
1. Add your community name and leave IP addresses empty
1. Click Apply and Save

### Eaton

#### Network Card-MS

1. Connect to the Web UI of the device
1. Upgrade to the latest available manufacturer firmware which applies to your hardware revision. Refer to the release notes.   For devices which can use the Lx releases, *do* install LD.
1. After rebooting the card (safe for connected load), configure Network, System and Access Control. Save config for each step.
1. Configure SNMP. The device defaults to both SNMP v1 and v3 enabled, with default credentials. Disable what you do not need. SNMP v3 works, but uses MD5/DES. You may have to add another section to your SNMP credentials table in LibreNMS. Save.

### HPE 3PAR

#### Inform OS 3.2.x

- Access the CLI
- Add an SNMP Manager with your LibreNMS IP address:

```
addsnmpmgr <librenms ip>
```

- Add your SNMP community:

```
setsnmppw <community>
```

### Infoblox

#### NIOS 7.x+

1. Access the web admin page and log in
1. Go to Grid tab > Grid Manager
1. In the right menu select "Grid properties"
1. Select "SNMP" menu
1. Click "Enable SNMPv1/SNMPv2 Queries"
1. Add your community
1. Click Save & Close

### Juniper

#### Junos OS

for SNMPv1/v2c

```
set snmp description description
set snmp location location
set snmp contact contact
set snmp community YOUR-COMMUNITY authorization read-only
```

for SNMPv3 (authPriv):

```
set snmp v3 usm local-engine user authpriv authentication-sha authentication-password YOUR_AUTH_SECRET
set snmp v3 usm local-engine user authpriv privacy-aes128 privacy-password YOUR_PRIV_SECRET
set snmp v3 vacm security-to-group security-model usm security-name authpriv group mysnmpv3
set snmp v3 vacm access group mysnmpv3 default-context-prefix security-model any security-level authentication read-view mysnmpv3view
set snmp v3 vacm access group mysnmpv3 default-context-prefix security-model any security-level privacy read-view mysnmpv3view
set snmp view mysnmpv3view oid iso include
```

### Mikrotik

#### RouterOS 6.x

CLI SNMP v2 Configuration

```
/snmp community
set [ find default=yes ] read-access=no
add addresses=<ALLOWED-SRC-IPs/NETMASK> name=<COMMUNITY>
/snmp
set contact="<NAME>" enabled=yes engine-id=<ENGINE ID> location="<LOCALTION>"
```
Notes:

* About the snmp community commands:
    * The commands change the default snmp community.  It is probably possible to create a new one instead.
    * <ALLOWED-SRC-IPs/NETMASK> specify the address and host (not network) netmask of the LibreNMS server.  Example: 192.168.8.71/32
    * trap-version=2 must also be specified if some other trap-version has been set
    * trap-interfaces may also be used to limit the interfaces the router listens on
* About the snmp command:
    * contact, engine-id and location are optional
    * trap-community is probably required if a new snmp community has been created.

CLI SNMP v3 Configuration for *authPriv*
```
/snmp community
add name="<COMMUNITY>" addresses="<ALLOWED-SRC-IPs/NETMASK>"
set "<COMMUNITY>" authentication-password="<AUTH_PASS>" authentication-protocol=MD5
set "<COMMUNITY>" encryption-password="<ENCRYP_PASS>" encryption-protocol=AES
set "<COMMUNITY>" read-access=yes write-access=no security=private
#Disable public SNMP
set public read-access=no write-access=no security=private
/snmp
set contact="<NAME>" enabled=yes engine-id="<ENGINE ID>" location="<LOCALTION>"
```
Notes:

* Use password with length of min 8 chars

Notes for both SNMP v2 and v3

* In some cases of advanced routing one may need to set explicitly the source IP address from which the SNMP daemon will reply - `/snmp set src-address=<SELF_IP_ADDRESS>`

### Palo Alto

#### PANOS 6.x/7.x

1. Access the web admin page and log in
1. Go to Device tab > Setup
1. Go to the sub-tab "Operations"
1. Click "SNMP Setup"
1. Enter your SNMP community and then click "OK"
1. Click Apply

Note that you need to allow SNMP on the needed interfaces. To do that
you need to create a network "Interface Mgmt" profile for standard
interface and allow SNMP under "Device > Management > Management
Interface Settings" for out of band management interface.

One may also configure SNMP from the command line, which is useful
when you need to configure more than one firewall for SNMP
monitoring. Log into the firewall(s) via ssh, and perform these
commands for basic SNMPv3 configuration:

```
username@devicename> configure
username@devicename# set deviceconfig system service disable-snmp no
username@devicename# set deviceconfig system snmp-setting access-setting version v3 views pa view iso oid 1.3.6.1
username@devicename# set deviceconfig system snmp-setting access-setting version v3 views pa view iso option include
username@devicename# set deviceconfig system snmp-setting access-setting version v3 views pa view iso mask 0xf0
username@devicename# set deviceconfig system snmp-setting access-setting version v3 users authpriv authpwd YOUR_AUTH_SECRET
username@devicename# set deviceconfig system snmp-setting access-setting version v3 users authpriv privpwd YOUR_PRIV_SECRET
username@devicename# set deviceconfig system snmp-setting access-setting version v3 users authpriv view pa
username@devicename# set deviceconfig system snmp-setting snmp-system location "Yourcity, Yourcountry [60.4,5.31]"
username@devicename# set deviceconfig system snmp-setting snmp-system contact noc@your.org
username@devicename# commit
username@devicename# exit
```

### Ubiquiti

#### EdgeOs

If you use the HTTP interface:
1. Access the legacy web admin page and log in
1. Go to System > Advanced Configuration
1. Go to the sub-tab "SNMP" > "Community"
1. Click "Add Community Group"
1. Enter your SNMP community, ip address and click submit
1. Go to System > Summary
1. Go to the sub-tab "Description"
1. Enter your System Name, System Location and System Contact.
1. Click submit
1. Click "Save Configuration"

If you use CLI:
```
username@devicename> enable
username@devicename# configure
username@devicename (Config)# snmp-server community "public" ro
username@devicename (Config)# snmp-server sysname "devicename"
username@devicename (Config)# snmp-server contact "noc@example.com"
username@devicename (Config)# exit
username@devicename# write memory
```

### VMware

#### ESX/ESXi 5.x/6.x

Log on to your ESX server by means of ssh. You may have to enable the
ssh service in the GUI first.
From the CLI, execute the following commands:

```
esxcli system snmp set --authentication SHA1
esxcli system snmp set --privacy AES128
esxcli system snmp hash --auth-hash YOUR_AUTH_SECRET --priv-hash YOUR_PRIV_SECRET --raw-secret
```

This command produces output like this

```
   Authhash: f3d8982fc28e8d1346c26eee49eb2c4a5950c934
   Privhash: 0596ab30b315576a4e9f7d7bde65bf49b749e335
```

Now define a SNMPv3 user:

```
esxcli system snmp set --users <username>/f3d8982fc28e8d1346c26eee49eb2c4a5950c934/0596ab30b315576a4e9f7d7bde65bf49b749e335/priv
esxcli system snmp set -L "Yourcity, Yourcountry [60.4,5.3]"
esxcli system snmp set -C noc@your.org
esxcli system snmp set --enable true
```

>Note: In case of snmp timeouts, disable the firewall with `esxcli
>network firewall set --enabled false` If snmp timeouts still occur
>with firewall disabled, migrate VMs if needed and reboot ESXi host.

#### VCenter 6.x

Log on to your ESX server by means of ssh. You may have to enable the
ssh service in the GUI first. From the CLI, execute the following
commands:

```
snmp.set --authentication SHA1
snmp.set --privacy AES128
snmp.hash --auth_hash YOUR_AUTH_SECRET --priv_hash YOUR_PRIV_SECRET --raw_secret true
```

This command produces output like this

```
   Privhash: 0596ab30b315576a4e9f7d7bde65bf49b749e335
   Authhash: f3d8982fc28e8d1346c26eee49eb2c4a5950c934
```

Now define a SNMPv3 user:

```
snmp.set --users authpriv/f3d8982fc28e8d1346c26eee49eb2c4a5950c934/0596ab30b315576a4e9f7d7bde65bf49b749e335/priv
snmp.enable
```

## Operating systems

### Linux (snmpd v2)

Replace your snmpd.conf file by the example below and edit it with
appropriate community in "RANDOMSTRINGGOESHERE".

```
vi /etc/snmp/snmpd.conf
```

```
# Change RANDOMSTRINGGOESHERE to your preferred SNMP community string
com2sec readonly  default         RANDOMSTRINGGOESHERE

group MyROGroup v2c        readonly
view all    included  .1                               80
access MyROGroup ""      any       noauth    exact  all    none   none

syslocation Rack, Room, Building, City, Country [GPSX,Y]
syscontact Your Name <your@email.address>

#Distro Detection
extend distro /usr/bin/distro
#Hardware Detection (uncomment to enable)
#extend hardware '/bin/cat /sys/devices/virtual/dmi/id/product_name'
#extend manufacturer '/bin/cat /sys/devices/virtual/dmi/id/sys_vendor'
#extend serial '/bin/cat /sys/devices/virtual/dmi/id/product_serial'
```

**NOTE**: On some systems the snmpd is running as its own user, which
means it can't read `/sys/devices/virtual/dmi/id/product_serial` which
is mode 0400. One solution is to include `@reboot chmod 444
/sys/devices/virtual/dmi/id/product_serial` in the crontab for root or
equivalent.

Non-x86 or SMBIOS-based systems, such as ARM-based Raspberry Pi units should
query device tree locations for this metadata, for example:
```
extend hardware '/bin/cat /sys/firmware/devicetree/base/model'
extend serial '/bin/cat /sys/firmware/devicetree/base/serial-number'
```

The LibreNMS server include a copy of this example here:

```
/opt/librenms/snmpd.conf.example
```

The binary /usr/bin/distro must be copied from the original source repository:

```
curl -o /usr/bin/distro https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/distro
chmod +x /usr/bin/distro
```

### Linux (snmpd v3)

Go to /etc/snmp/snmpd.conf

Open the file in vi or nano /etc/snmp/snmpd.conf and add the following
line to create SNMPV3 User (replace username and passwords with your
own):

```
createUser authPrivUser SHA "authPassword" AES "privPassword"
```

Make sure the agent listens to all interfaces by adding the following
line inside snmpd.conf:

```
agentAddress udp:161,udp6:[::1]:161
```

This line simply means listen to connections across all interfaces
IPv4 and IPv6 respectively

Uncomment and change the following line to give read access to the
username created above (rouser is what LibreNMS uses) :

```
#rouser authPrivUser priv
```

Change the following details inside snmpd.conf

```
syslocation Rack, Room, Building, City, Country [GPSX,Y]
syscontact Your Name <your@email.address>
```

Save and exit the file

#### Restart the snmpd service

##### CentOS 6 / Red hat 6

```
service snmpd restart
```

##### CentOS 7 / Red hat 7

```
systemctl restart snmpd
```

Add SNMP to Firewalld

```
firewall-cmd --zone=public --permanent --add-service=snmp
firewall-cmd --reload
```

##### Ubuntu

```
service snmpd restart
```

### Windows Server 2008 R2

1. Log in to your Windows Server 2008 R2
1. Start "Server Manager" under "Administrative Tools"
1. Click "Features" and then click "Add Feature"
1. Check (if not checked) "SNMP Service", click "Next" until "Install"
1. Start "Services" under "Administrative Tools"
1. Edit "SNMP Service" properties
1. Go to the security tab
1. In "Accepted community name" click "Add" to add your community string and permission
1. In "Accept SNMP packets from these hosts" click "Add" and add your
   LibreNMS server IP address
1. Validate change by clicking "Apply"

### Windows Server 2012 R2 and newer

#### GUI
1. Log in to your Windows Server 2012 R2 or newer
1. Start "Server Manager" under "Administrative Tools"
1. Click "Manage" and then "Add Roles and Features"
1. Continue by pressing "Next" to the "Features" menu
1. Install (if not installed) "SNMP Service"
1. Start "Services" under "Administrative Tools"
1. Edit "SNMP Service" properties
1. Go to the security tab
1. In "Accepted community name" click "Add" to add your community string and permission
1. In "Accept SNMP packets from these hosts" click "Add" and add your
   LibreNMS server IP address
1. Validate change by clicking "Apply"

#### PowerShell
The following example will install SNMP, set the Librenms IP and set a read only community string.  
Replace `$IP` and `$communitystring` with your values.

```Powershell
Install-WindowsFeature -Name 'SNMP-Service','RSAT-SNMP'
New-ItemProperty -Path "HKLM:\SYSTEM\CurrentControlSet\services\SNMP\Parameters\PermittedManagers"  -Name 2 -Value $IP
New-ItemProperty -Path "HKLM:\SYSTEM\CurrentControlSet\services\SNMP\Parameters\ValidCommunities"  -Name $communitystring -Value 4

```

>Note: SNMPv3 can be supported on Windows platforms with the use of Net-SNMP.

### Mac OSX

Step 1: ```sudo nano /etc/snmp/snmpd.conf```

```bash
#Allow read-access with the following SNMP Community String:
rocommunity public

# all other settings are optional but recommended.

# Location of the device
syslocation data centre A

# Human Contact for the device
syscontact SysAdmin

# System Name of the device
sysName SystemName

# the system OID for this device. This is optional but recommended,
# to identify this as a MAC OS system.
sysobjectid 1.3.6.1.4.1.8072.3.2.16
```

Step 2:

``` bash
sudo launchctl load -w /System/Library/LaunchDaemons/org.net-snmp.snmpd.plist
```
