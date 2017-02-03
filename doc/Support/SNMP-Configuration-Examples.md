source: Support/SNMP-Configuration-Examples.md

# SNMP configuration examples

Table of Content:
- [Devices](#devices)
    - [Cisco](#cisco)
        - [Adaptive Security Appliance (ASA)](#adaptive-security-appliance-asa)
        - [IOS / IOS XE / NX-OS](#ios--ios-xe--nx-os)
        - [Wireless LAN Controller (WLC)](#wireless-lan-controller-wlc)
    - [HPE 3PAR](#hpe3par)
        - [Inform OS 3.2.x](#inform-os-32x)
    - [Infoblox](#infoblox)
        - [NIOS 7.x](#nios-7x)
    - [Juniper](#juniper)
        - [Junos OS](#junos-os)
    - [Palo Alto](#palo-alto)
        - [PANOS 6.x/7.x](#panos-6x7x)
- [Operating systems](#operating-systems)
    - [Linux (snmpd)](#linux-snmpd)
    - [Windows Server 2008 R2](#windows-server-2008-r2)
    - [Windows Server 2012 R2](#windows-server-2012-r2)

## Devices

### Cisco
#### Adaptive Security Appliance (ASA)
1. Launch ASDM and connect to your device
2. Go to Configuration > Management Access > SNMP
3. Add your community string
4. Add in the "SNMP Host Access List" section your LibreNMS server IP address
5. Click Apply and Save

#### IOS / IOS XE / NX-OS

``` 
snmp-server community YOUR-COMMUNITY RO
snmp-server contact YOUR-CONTACT
snmp-server location YOUR-LOCATION
```

#### Wireless LAN Controller (WLC)
1. Access the web admin page and log in
2. If you are running version 8.1 and later, on the new dashboard click "Advanced"
3. Go to management Tab
4. On SNMP sub-menu, select "Communities"
5. Click "New..."
6. Add your community name and leave IP addresses empty
7. Click Apply and Save

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
#### NIOS 7.x
1. Access the web admin page and log in
2. Go to Grid tab > Grid Manager
3. In the right menu select "Grid properties"
4. Select "SNMP" menu
5. Click "Enable SNMPv1/SNMPv2 Queries"
6. Add your community
7. Click Save & Close

### Juniper
#### Junos OS
```
set snmp description description
set snmp location location
set snmp contact contact
set snmp community YOUR-COMMUNITY authorization read-only
```

### Mikrotik
#### RouterOS 6.x
```
#Terminal SNMP v2 Configuration
/snmp community
set [ find default=yes ] read-access=no
add addresses=<SRC IP/NETWORK> name=<COMMUNITY>
/snmp
set contact="<NAME>" enabled=yes engine-id=<ENGINE ID> location="<LOCALTION>"
```

### Palo Alto
#### PANOS 6.x/7.x
1. Access the web admin page and log in
2. Go to Device tab > Setup
3. Go to the sub-tab "Operations"
4. Click "SNMP Setup"	
5. Enter your SNMP community and then click "OK"
6. Click Apply

Note that you need to allow SNMP on the needed interfaces. To do that you need to create a network "Interface Mgmt" profile for standard interface and allow SNMP under "Device > Management > Management Interface Settings" for out of band management interface.


## Operating systems
### Linux (snmpd)

Replace your snmpd.conf file by the example below and edit it with appropriate community in "RANDOMSTRINGGOESHERE".

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
extend .1.3.6.1.4.1.2021.7890.1 distro /usr/bin/distro
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

#### Restart the snmpd service:

##### CentOS 6 / Red hat 6
```
service snmpd restart
```
##### CentOS 7 / Red hat 7
```
systemctl restart snmpd
```
##### Ubuntu
```
service snmpd restart
```

### Windows Server 2008 R2
1. Log in to your Windows Server 2008 R2
2. Start "Server Manager" under "Administrative Tools"
3. Click "Features" and then click "Add Feature"
5. Check (if not checked) "SNMP Service", click "Next" until "Install"
6. Start "Services" under "Administrative Tools"
7. Edit "SNMP Service" properties
8. Go to the security tab
9. In "Accepted community name" click "Add" to add your community string and permission
10. In "Accept SNMP packets from these hosts" click "Add" and add your LibreNMS server IP address
11. Validate change by clicking "Apply"

### Windows Server 2012 R2
1. Log in to your Windows Server 2012 R2
2. Start "Server Manager" under "Administrative Tools"
3. Click "Manage" and then "Add Roles and Features"
4. Continue by pressing "Next" to the "Features" menu
5. Install (if not installed) "SNMP Service"
6. Start "Services" under "Administrative Tools"
7. Edit "SNMP Service" properties
8. Go to the security tab
9. In "Accepted community name" click "Add" to add your community string and permission
10. In "Accept SNMP packets from these hosts" click "Add" and add your LibreNMS server IP address
11. Validate change by clicking "Apply"
