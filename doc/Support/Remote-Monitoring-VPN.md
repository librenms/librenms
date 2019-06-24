source: Support/Remote-Monitoring-VPN.md
path: blob/master/doc/

# Remote monitoring using tinc VPN

This article describes how to use tinc to connect several remote sites
and their subnets to your central monitoring server. This will let you
connect to devices on remote private IP ranges through one gateway on
each site, routing them securely back to your LibreNMS installation.

## Configuring the monitoring server

tinc should be available on nearly all Linux distributions via package
management. If you are running something different, just take a look
at tinc's homepage to find an appropriate version for your operating
system: <https://www.tinc-vpn.org/download/>

I am going to describe the setup for Debian-based systems, but there
are virtually no differences for e.g. CentOS or similar.

- First make sure your firewall accepts connections on port 655 UDP
  and TCP.
- Then install tinc via `apt-get install tinc`.
- Create the following directory structure to hold all your
  configuration files: `mkdir -p /etc/tinc/myvpn/hosts` "myvpn" is
  your VPN network's name and can be chosen freely.
- Create your main configuration file: `vim /etc/tinc/myvpn/tinc.conf`

```bash
Name = monitoring
AddressFamily = ipv4
Device = /dev/net/tun
```

- Next we need network up- and down scripts to define a few network
  settings for inside our VPN: `vim /etc/tinc/myvpn/tinc-up`

```bash
#!/bin/sh
ifconfig $INTERFACE 10.6.1.1 netmask 255.255.255.0
ip route add 10.6.1.1/24 dev $INTERFACE
ip route add 10.0.0.0/22 dev $INTERFACE
ip route add 10.100.0.0/22 dev $INTERFACE
ip route add 10.200.0.0/22 dev $INTERFACE
```

- In this example we have 10.6.1.1 as the VPN IP address for the
  monitoring server on a /24 subnet. $INTERFACE will be automatically
  substituted with the name of the VPN, "myvpn" in this case. Then we
  have a route for the VPN subnet, so we can reach other sites via
  their VPN address. The last 3 lines designate the remote subnets. In
  the example I want to reach devices on three different remote
  private /22 subnets and be able to monitor devices on them from this
  server, so I set up routes for each of those remote sites in my
  tinc-up script.

- The tinc-down script is relatively simple as it just removes the
  custom interface, which should get rid of the routes as well: `vim
  /etc/tinc/myvpn/tinc-down`

```bash
#!/bin/sh
ifconfig $INTERFACE down
```

- Make sure your scripts scan be executed: `chmod +x
  /etc/tinc/myvpn/tinc-*`
- As a last step we need a host configuration file. This should be
  named the same as the "Name" you defined in tinc.conf: `vim
  /etc/tinc/myvpn/hosts/monitoring`

```bash
Subnet = 10.6.1.1/32
```

On the monitoring server we will just fill in the subnet and not
define its external IP address to make sure it listens on all
available external interfaces.

- It's time to use tinc to create our key-pair: `tincd -n myvpn -K`
- Now the file `/etc/tinc/myvpn/hosts/monitoring` should have an RSA
  public key appended to it and your private key should reside in
  `/etc/tinc/myvpn/rsa_key.priv`.
- To make sure that the connection will be restored after each reboot,
  you can add your VPN name to `/etc/tinc/nets.boot`.
- Now you can start tinc with `tincd -n myvpn` and it will listen for
  your remote sites to connect to it.

## Remote site configuration

Essentially the same steps as for your central monitoring server apply
for all remote gateway devices. These can be routers, or just any
computer or VM running on the remote subnet, able to reach the
internet with the ability to forward IP packets externally.

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

- Make executable: `chmod +x /etc/tinc/myvpn/tinc*`
- Create device configuration: `vim /etc/tinc/myvpn/hosts/remote1`

```bash
Address = 198.51.100.2
Subnet = 10.0.0.0/22
```

This defines the device IP address outside of the VPN and the subnet it will expose.

- Copy over the monitoring server's host configuration (including the
  embedded public key) and add it's external IP address: `vim
  /etc/tinc/myvpn/hosts/monitoring`

```bash
Address = 203.0.113.6
Subnet = 10.6.1.1/32

-----BEGIN RSA PUBLIC KEY-----
VeDyaqhKd4o2Fz...
```

- Generate this device's keys: `tincd -n myvpn -K`
- Copy over this devices host file including the embedded public key
  to your monitoring server.
- Add the name for the VPN to`/etc/tinc/nets.boot` if you want to
  autostart the connection upon reboot.
- Start tinc: `tincd -n myvpn`

These steps can basically be repeated for every remote site just
 choosing different names and other internal IP addresses. In my case
 I connected 3 remote sites running behind Ubiquiti EdgeRouters. Since
 those devices let me install software through Debian's package
 management it was very easy to set up. Just create the necessary
 configuration files and network scripts on each device and distribute
 the host configurations including the public keys to each device that
 will actively connect back.

Now you can add all devices you want to monitor in LibreNMS using
their internal IP address on the remote subnets or using some form of
name resolution. I opted to declare the most important devices in my
`/etc/hosts` file on the monitoring server.

As an added bonus tinc is a mesh VPN, so in theory you could specify
several "ConnectTo" on each device and they should hold connections
even if one network path goes down.
