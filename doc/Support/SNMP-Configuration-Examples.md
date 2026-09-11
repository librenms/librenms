# SNMP configuration examples

## Devices

### Cisco

#### Adaptive Security Appliance (ASA)

ASDM

1. Start ASDM and connect to your device.
1. Go to Configuration > Management Access > SNMP.
1. Add your community string.
1. In the "SNMP Host Access List" section, add the IP address of your LibreNMS server.
1. Click Apply, then Save.

CLI

```bash
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

!!! note
    If the device cannot find the SNMP user, reboot the ASA. After the reboot, continue with the normal steps.

#### IOS / IOS XE

```bash
# SNMPv2c

snmp-server community <YOUR-COMMUNITY> RO
snmp-server contact <YOUR-CONTACT>
snmp-server location <YOUR-LOCATION>

# SNMPv3

snmp-server group <GROUP-NAME> v3 priv
snmp-server user <USER-NAME> <GROUP-NAME> v3 auth sha <AUTH-PASSWORD> priv aes 128 <PRIV-PASSWORD>
snmp-server contact <YOUR-CONTACT>
snmp-server location <YOUR-LOCATION>

# Note: The following is also required if using SNMPv3 and you want to populate the FDB table, STP info and others.

snmp-server group <GROUP-NAME> v3 priv context vlan- match prefix
```

!!! note
    If the device cannot find the SNMP user, reboot the ASA. After the reboot, continue with the normal steps.

#### NX-OS

```bash
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

1. Open the web admin page and log in.
1. On version 8.1 and later, click "Advanced" on the new dashboard.
1. Go to the Management tab.
1. In the SNMP submenu, select "Communities".
1. Click "New...".
1. Add your community name. Leave the IP addresses empty.
1. Click Apply, then Save.

### Eaton

#### Network Card-MS

1. Connect to the web interface of the device.
1. Upgrade to the latest manufacturer firmware for your hardware revision. Read the release notes. On a device that accepts the Lx releases, install LD.
1. Reboot the card. This action is safe for the connected load. Then configure Network, System, and Access Control. Save the configuration after each step.
1. Configure SNMP. By default, the device enables SNMP v1 and SNMP v3 with default credentials. Disable the versions that you do not need. SNMP v3 works, but it uses MD5 and DES. You can therefore need another section in your SNMP credentials table in LibreNMS. Then save.

### Extreme

#### EXOS

```bash

# General configuration (all SNMP versions)
configure snmp sysName "<YOUR-HOSTNAME>"
configure snmp sysLocation "<YOUR-LOCATION>"
configure snmp sysContact "<YOUR-CONTACT>"

# SNMPv3 (Read-Only)
configure snmpv3 add user <READ-ONLY-USERNAME> authentication sha <AUTH-PASSWORD> privacy aes <PRIV-PASSWORD>
configure snmpv3 add group <READ-ONLY-GROUP> user <READ-ONLY-USERNAME> sec-model usm
configure snmpv3 add access <READ-ONLY-GROUP> sec-model usm sec-level priv read-view defaultAdminView write-view noAuth notify-view defaultAdminView
disable snmp access snmp-v1v2c
enable snmp access snmpv3

# SNMPv3 (Read-Write)
configure snmpv3 add user <READ-WRITE-USERNAME> authentication sha <AUTH-PASSWORD> privacy aes <PRIV-PASSWORD>
configure snmpv3 add group <READ-WRITE-GROUP> user <READ-WRITE-USERNAME> sec-model usm
configure snmpv3 add access <READ-WRITE-GROUP> sec-model usm sec-level priv read-view defaultAdminView write-view defaultAdminView notify-view defaultAdminView
disable snmp access snmp-v1v2c
enable snmp access snmpv3
```


### HPE / 3PAR

#### Comware

SNMPv2c

```bash
snmp-agent community read <YOUR-COMMUNITY>
snmp-agent sys-info contact <YOUR-CONTACT>
snmp-agent sys-info location <YOUR-LOCATION>
snmp-agent sys-info version all
snmp-agent packet max-size 6000
```

!!! note
    Some walks need `packet max-size` to complete. The network path must support fragmentation.

SNMPv3

```bash
snmp-agent mib-view excluded ExcludeAll snmp
snmp-agent group v3 V3ROGroup privacy read-view ViewDefault write-view ExcludeAll
snmp-agent usm-user v3 <USER> V3ROGroup simple authentication-mode sha <AuthKey> privacy-mode aes128 <PrivacyKey>
snmp-agent sys-info contact <YOUR-CONTACT>
snmp-agent sys-info location <YOUR-LOCATION>
snmp-agent sys-info version v3
undo snmp-agent sys-info version v1 v2c
snmp-agent packet max-size 6000
```

!!! note
    Some walks need `packet max-size` to complete. The network path must support fragmentation.

#### Inform OS 3.2.x

- Open the command line.
- Add an SNMP manager with the IP address of your LibreNMS server:

```bash
addsnmpmgr <librenms ip>
```

- Add your SNMP community:

```bash
setsnmppw <community>
```

### Infoblox

#### NIOS 7.x+

1. Open the web admin page and log in.
1. Go to the Grid tab > Grid Manager.
1. In the right menu, select "Grid properties".
1. Select the "SNMP" menu.
1. Click "Enable SNMPv1/SNMPv2 Queries".
1. Add your community.
1. Click Save & Close.

### Juniper

#### Junos OS

For SNMPv1 and SNMPv2c:

```bash
set snmp description description
set snmp location location
set snmp contact contact
set snmp community YOUR-COMMUNITY authorization read-only
```

For SNMPv3 with authPriv:

```bash
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

```bash
/snmp community
set [ find default=yes ] read-access=no
add addresses=<ALLOWED-SRC-IPs/NETMASK> name=<COMMUNITY>
/snmp
set contact="<NAME>" enabled=yes engine-id=<ENGINE ID> location="<LOCATION>"
```

!!! note
    * The snmp community commands:
         * These commands change the default SNMP community. You can also create a new community.
         * `<ALLOWED-SRC-IPs/NETMASK>` is the address and the host netmask of the LibreNMS server. Use the host netmask, not the network netmask. An example is 192.168.8.71/32.
         * If another trap-version is already set, you must also give `trap-version=2`.
         * `trap-interfaces` limits the interfaces that the router listens on.
    * The snmp command:
         * `contact`, `engine-id`, and `location` are optional.
         * A new SNMP community usually needs `trap-community`.

CLI SNMP v3 Configuration for *authPriv*

```bash
/snmp community
add name="<COMMUNITY>" addresses="<ALLOWED-SRC-IPs/NETMASK>"
set "<COMMUNITY>" authentication-password="<AUTH_PASS>" authentication-protocol=MD5
set "<COMMUNITY>" encryption-password="<ENCRYPT_PASS>" encryption-protocol=AES
set "<COMMUNITY>" read-access=yes write-access=no security=private
#Disable public SNMP
set public read-access=no write-access=no security=private
/snmp
set contact="<NAME>" enabled=yes engine-id="<ENGINE ID>" location="<LOCATION>"
```

!!! note
    * The password must have a minimum length of 8 characters.

    Notes for SNMP v2 and SNMP v3:

    * With advanced routing, you can need to set the source IP address of the SNMP replies. Use `/snmp set src-address=<SELF_IP_ADDRESS>`.

### Palo Alto

#### PANOS 6.x/7.x

1. Open the web admin page and log in.
1. Go to the Device tab > Setup.
1. Go to the "Operations" subtab.
1. Click "SNMP Setup".
1. Enter your SNMP community, then click "OK".
1. Click Apply.

You must also permit SNMP on the necessary interfaces. For a standard
interface, create a network "Interface Mgmt" profile. For an out of
band management interface, permit SNMP under "Device > Management >
Management Interface Settings".

You can also configure SNMP from the command line. This method is
useful for several firewalls. Log in to each firewall with ssh. Then
run these commands for a basic SNMPv3 configuration:

```bash
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

With the HTTP interface:
1. Open the legacy web admin page and log in.
1. Go to System > Advanced Configuration.
1. Go to the "SNMP" > "Community" subtab.
1. Click "Add Community Group".
1. Enter your SNMP community and IP address, then click submit.
1. Go to System > Summary.
1. Go to the "Description" subtab.
1. Enter your System Name, System Location, and System Contact.
1. Click submit.
1. Click "Save Configuration".

With the command line:

```bash
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

Log on to your ESX server with ssh. First enable the ssh service in the
web interface, if it is not enabled. Then run these commands:

```bash
esxcli system snmp set --authentication SHA1
esxcli system snmp set --privacy AES128
esxcli system snmp hash --auth-hash YOUR_AUTH_SECRET --priv-hash YOUR_PRIV_SECRET --raw-secret
```

The command gives output like this:

```bash
Authhash: f3d8982fc28e8d1346c26eee49eb2c4a5950c934
Privhash: 0596ab30b315576a4e9f7d7bde65bf49b749e335
```

Then define an SNMPv3 user:

```bash
esxcli system snmp set --users <username>/f3d8982fc28e8d1346c26eee49eb2c4a5950c934/0596ab30b315576a4e9f7d7bde65bf49b749e335/priv
esxcli system snmp set -L "Yourcity, Yourcountry [60.4,5.3]"
esxcli system snmp set -C noc@your.org
esxcli system snmp set --enable true
```

>Note: if SNMP timeouts occur, disable the firewall with `esxcli
>network firewall set --enabled false`. If the timeouts continue with
>the firewall disabled, migrate the VMs and reboot the ESXi host.

#### VCenter 6.x

Log on to your ESX server with ssh. First enable the ssh service in the
web interface, if it is not enabled. Then run these commands:

```bash
snmp.set --authentication SHA1
snmp.set --privacy AES128
snmp.hash --auth_hash YOUR_AUTH_SECRET --priv_hash YOUR_PRIV_SECRET --raw_secret true
```

The command gives output like this:

```bash
Privhash: 0596ab30b315576a4e9f7d7bde65bf49b749e335
Authhash: f3d8982fc28e8d1346c26eee49eb2c4a5950c934
```

Then define an SNMPv3 user:

```bash
snmp.set --users authpriv/f3d8982fc28e8d1346c26eee49eb2c4a5950c934/0596ab30b315576a4e9f7d7bde65bf49b749e335/priv
snmp.enable
```

## Operating systems

### Linux (snmpd v2)

Replace your `snmpd.conf` file with the example below. Then replace
`RANDOMSTRINGGOESHERE` with your own community.

```bash
vi /etc/snmp/snmpd.conf
```

```bash
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

!!! note
    On some systems, snmpd runs as its own user. It therefore cannot
    read `/sys/devices/virtual/dmi/id/product_serial`, because this file
    has mode 0400. One solution is the line `@reboot chmod 444
    /sys/devices/virtual/dmi/id/product_serial` in the crontab of root.

Systems that are not x86 and do not use SMBIOS need a different source
for this metadata. An example is an ARM-based Raspberry Pi. Query the
device tree locations, for example:

```bash
extend hardware '/bin/cat /sys/firmware/devicetree/base/model'
extend serial '/bin/cat /sys/firmware/devicetree/base/serial-number'
```

The LibreNMS server holds a copy of this example here:

```bash
/opt/librenms/snmpd.conf.example
```

Copy the `/usr/bin/distro` binary from the original source repository:

```bash
curl -o /usr/bin/distro https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/distro
chmod +x /usr/bin/distro
```

### Linux (snmpd v3)

#### Stop the snmpd service

##### CentOS 6 / Red hat 6

```bash
service snmpd stop
```

##### CentOS 7 / Red hat 7

```bash
systemctl stop snmpd
```

##### Ubuntu

```bash
service snmpd stop
```

Open the file `/var/lib/snmp/snmpd.conf` in vi or nano. Add the line
below to create the SNMPv3 user. Replace the username and the passwords
with your own values:

```bash
createUser authPrivUser SHA "authPassword" AES "privPassword"
```

At the next start of the service, snmpd removes this line. It writes an
equivalent line that starts with `usmUser`.

Open the file `/etc/snmp/snmpd.conf`.

Add this line to make the agent listen on all interfaces:

```bash
agentAddress udp:161,udp6:161
```

This line makes the agent accept connections on all IPv4 interfaces and
all IPv6 interfaces.

Remove the comment from the line below and change it. This line gives
read access to the new username. LibreNMS uses `rouser`:

```bash
#rouser authPrivUser priv
```

Change these details in `snmpd.conf`:

```bash
syslocation Rack, Room, Building, City, Country [GPSX,Y]
syscontact Your Name <your@email.address>
```

Save the file and close it.

#### Restart the snmpd service

##### CentOS 6 / Red hat 6

```bash
service snmpd restart
```

##### CentOS 7 / Red hat 7

```bash
systemctl restart snmpd
```

Add SNMP to Firewalld

```bash
firewall-cmd --zone=public --permanent --add-service=snmp
firewall-cmd --reload
```

##### Ubuntu

```bash
service snmpd restart
```

### Arch Linux (snmpd v2)

1. Install the SNMP package: `pacman -S net-snmp`
2. Create the SNMP folder: `mkdir /etc/snmp/`
3. Set the community: `echo rocommunity read_only_community_string >> /etc/snmp/snmpd.conf`
4. Set the contact: `echo syscontact Firstname Lastname >> /etc/snmp/snmpd.conf`
5. Set the location: `echo syslocation L69 4RX >> /etc/snmp/snmpd.conf`
6. Enable the service at startup: `systemctl enable snmpd.service`
7. Start SNMP: `systemctl restart snmpd.service`

### Windows Server 2008 R2

1. Log in to your Windows Server 2008 R2.
1. Start "Server Manager" under "Administrative Tools".
1. Click "Features", then click "Add Feature".
1. Select "SNMP Service". Then click "Next" until "Install".
1. Start "Services" under "Administrative Tools".
1. Edit the "SNMP Service" properties.
1. Go to the security tab.
1. In "Accepted community name", click "Add". Then add your community string and its permission.
1. In "Accept SNMP packets from these hosts", click "Add". Then add the IP address of your LibreNMS server.
1. Click "Apply" to save the change.

### Windows Server 2012 R2 and newer

#### GUI
1. Log in to your Windows Server 2012 R2 or later.
1. Start "Server Manager" under "Administrative Tools".
1. Click "Manage", then "Add Roles and Features".
1. Press "Next" until the "Features" menu.
1. Install "SNMP Service", if it is not installed.
1. Start "Services" under "Administrative Tools".
1. Edit the "SNMP Service" properties.
1. Go to the security tab.
1. In "Accepted community name", click "Add". Then add your community string and its permission.
1. In "Accept SNMP packets from these hosts", click "Add". Then add the IP address of your LibreNMS server.
1. Click "Apply" to save the change.

#### PowerShell
This example installs SNMP, sets the LibreNMS IP address, and sets a
read only community string.
Replace `$IP` and `$communitystring` with your own values.

```Powershell
Install-WindowsFeature -Name 'SNMP-Service','RSAT-SNMP'
New-ItemProperty -Path "HKLM:\SYSTEM\CurrentControlSet\services\SNMP\Parameters\PermittedManagers"  -Name 2 -Value $IP
New-ItemProperty -Path "HKLM:\SYSTEM\CurrentControlSet\services\SNMP\Parameters\ValidCommunities"  -Name $communitystring -Value 4

```

!!! note
    Net-SNMP gives SNMPv3 support on a Windows platform.

### Mac OSX

Step 1: `sudo nano /etc/snmp/snmpd.conf`

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

```bash
sudo launchctl load -w /System/Library/LaunchDaemons/org.net-snmp.snmpd.plist
```
