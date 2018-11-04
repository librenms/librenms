source: Installation/CentOS-image.md
path: blob/master/doc/
> NOTE: We highly advice that you change all passwords on this image when you deploy it!!

> NOTE: Read the above note again!

We have available for download a pre-built image based on CentOS 7.5. Details of the image are below:

The image is built with VirtualBox, The ova was exported using OFV 1.0 version. 
These should be supported in VMWare Fusion, Workstation, Player and VirtualBox.

* NOTE: It's highly recommended that you update and validate by doing the following. 
```bash
su - librenms
cd /opt/librenms
./daily.sh && ./validate.php
```

* Any issues and or help with these images should be reported via [Community Fourm](https://community.librenms.org) or our [Discord server](https://t.libren.ms/discord)

### LibreNMS version
```
Component | Version
--------- | -------
LibreNMS  | 1.45-6-g07162ae
DB Schema | 270
PHP       | 7.2.10
MySQL     | 5.5.60-MariaDB
RRDTool   | 1.4.8
SNMP      | NET-SNMP 5.7.2
```

### Setup

  - UK Keyboard
  - Etc/UTC Timezone
  - 4 Poller Wrapper threads

### Software

  - PHP 7.2
  - MariaDB
  - Syslog-ng
  - Certbot (Secure your install with Let's Encrypt)
  - Snmptrapd configured

### Features

  - Weathermap plugin enabled
  - Billing enabled
  - RRDCached enabled
  - Service checks enabled
  - Syslog enabled

### Configuration

By default, the configured nginx vhost is `librenms.example.com`

Direct IP access is **disabled**. Before use, you must configure a valid FQDN and change it in the nginx vhost.

    vi /etc/nginx/conf.d/librenms.conf

Edit `server_name` as required:
```nginx
server {
 ...
 server_name librenms.example.com;
 ...
```
And restart nginx:

    systemctl restart nginx

### Download

[OVA Image](FIXME) - XG

  - md5sum: FIXME

  - sha256sum: FIXME

### Credentials

> Please note the second character of the SSH password is a CAPITAL EYE

  - SSH
    - username: root
    - password: `CIne3fwdfds`

> Please note the second character of the SSH password is a CAPITAL EYE

  - MySQL/MariaDB
    - username: root
    - password: `NIfceu3fqfd`

    - username: librenms
    - password: `D42nf23rewD`

  - WebUI
    - username: librenms
    - password: `D32fwefwef`

> NOTE: Again, we highly advise that you change all passwords on this image when you deploy it!!