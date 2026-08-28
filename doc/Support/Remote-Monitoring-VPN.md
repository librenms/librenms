# Remote monitoring using tinc VPN

This article describes how to connect several remote sites and their
subnets to your central monitoring server with tinc. You can then reach
devices on remote private IP ranges through one gateway at each site.
The traffic goes securely back to your LibreNMS installation.

## Configuring the monitoring server

The package management of almost all Linux distributions supplies tinc.
For a different operating system, find the correct version on the tinc
homepage: <https://www.tinc-vpn.org/download/>

This section describes the setup for Debian-based systems. The setup
for CentOS and other distributions is almost the same.

- First, configure your firewall to accept connections on port 655 UDP
  and TCP.
- Then install tinc with `apt-get install tinc`.
- Create this directory structure for your configuration files:
  `mkdir -p /etc/tinc/myvpn/hosts`. Here, `myvpn` is the name of your
  VPN network. You can select any name.
- Create your main configuration file: `vim /etc/tinc/myvpn/tinc.conf`

```bash
Name = monitoring
AddressFamily = ipv4
Device = /dev/net/tun
```

- Then create the network up script and the network down script. These
  scripts set the network configuration inside the VPN:
  `vim /etc/tinc/myvpn/tinc-up`

```bash
#!/bin/sh
ifconfig $INTERFACE 10.6.1.1 netmask 255.255.255.0
ip route add 10.6.1.1/24 dev $INTERFACE
ip route add 10.0.0.0/22 dev $INTERFACE
ip route add 10.100.0.0/22 dev $INTERFACE
ip route add 10.200.0.0/22 dev $INTERFACE
```

- In this example, 10.6.1.1 is the VPN IP address of the monitoring
  server on a /24 subnet. tinc replaces `$INTERFACE` with the name of
  the VPN, `myvpn` in this example. The next line adds a route for the
  VPN subnet. The other sites are then available at their VPN address.
  The last 3 lines give the remote subnets. This example reaches
  devices on three different remote private /22 subnets. The `tinc-up`
  script therefore has one route for each remote site.

- The `tinc-down` script removes the custom interface. This action also
  removes the routes: `vim /etc/tinc/myvpn/tinc-down`

```bash
#!/bin/sh
ifconfig $INTERFACE down
```

- Make the scripts executable: `chmod +x /etc/tinc/myvpn/tinc-*`
- The last step creates a host configuration file. Give the file the
  same name as the `Name` value in `tinc.conf`:
  `vim /etc/tinc/myvpn/hosts/monitoring`

```bash
Subnet = 10.6.1.1/32
```

On the monitoring server, give only the subnet. Do not give the
external IP address. The server then listens on all available external
interfaces.

- Create the key pair with tinc: `tincd -n myvpn -K`
- The file `/etc/tinc/myvpn/hosts/monitoring` now holds an RSA public
  key at its end. Your private key is in
  `/etc/tinc/myvpn/rsa_key.priv`.
- To restore the connection after each reboot, add your VPN name to
  `/etc/tinc/nets.boot`.
- Start tinc with `tincd -n myvpn`. It then listens for the connections
  from your remote sites.

## Remote site configuration

The steps for a remote gateway device are almost the same as for the
central monitoring server. A gateway device is a router, a computer, or
a VM on the remote subnet. The device must reach the internet and must
forward IP packets to external networks.

- Install tinc
- Create directory structure: `mkdir -p /etc/tinc/myvpn/hosts`
- Create main configuration: `vim /etc/tinc/myvpn/tinc.conf`

```bash
Name = remote1
AddressFamily = ipv4
Device = /dev/net/tun
ConnectTo = monitoring
```

- Create up script: `vim /etc/tinc/myvpn/tinc-up`

```bash
#!/bin/sh
ifconfig $INTERFACE 10.6.1.2 netmask 255.255.255.0
ip route add 10.6.1.2/32 dev $INTERFACE
```

- Create down script: `vim /etc/tinc/myvpn/tinc-down`

```bash
#!/bin/sh
ifconfig $INTERFACE down
```

- Make the scripts executable: `chmod +x /etc/tinc/myvpn/tinc*`
- Create the device configuration: `vim /etc/tinc/myvpn/hosts/remote1`

```bash
Address = 198.51.100.2
Subnet = 10.0.0.0/22
```

These two lines set the IP address of the device outside the VPN and
the subnet that the device makes available.

- Copy the host configuration of the monitoring server to this device.
  The file must hold the public key. Then add the external IP address
  of the monitoring server: `vim /etc/tinc/myvpn/hosts/monitoring`

```bash
Address = 203.0.113.6
Subnet = 10.6.1.1/32

-----BEGIN RSA PUBLIC KEY-----
VeDyaqhKd4o2Fz...
```

- Generate the keys of this device: `tincd -n myvpn -K`
- Copy the host file of this device to your monitoring server. The file
  must hold the public key.
- To start the connection automatically after a reboot, add the name of
  the VPN to `/etc/tinc/nets.boot`.
- Start tinc: `tincd -n myvpn`

Do these steps again for each remote site. Give each site a different
name and different internal IP addresses. This example connects 3
remote sites behind Ubiquiti EdgeRouters. These devices accept software
from the Debian package management, so the setup is easy. Create the
configuration files and the network scripts on each device. Then send
the host configurations with the public keys to each device that
connects back.

You can now add each device to LibreNMS. Use the internal IP address of
the device on the remote subnet, or use name resolution. One method is
an entry for each important device in the `/etc/hosts` file of the
monitoring server.

tinc is also a mesh VPN. You can give several `ConnectTo` values on
each device. The devices then hold their connections when one network
path fails.
