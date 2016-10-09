source: Installation/CentOS-image.md
> NOTE: We highly advice that you change all passwords on this image when you deploy it!!

> NOTE: Read the above note again!

We have available for download a pre-built image based on CentOS 7. Details of the image are below:

The image is built with VirtualBox, a vmdk is provided along with an ova which was exported using OFV 1.0 version. 
These should be supported in VMWare Fusion, Workstation, Player and VirtualBox.

Any issues with these images should be reported via [Github](https://github.com/librenms/librenms/issues) or our IRC channel ##librenms on the Freenode network.

#### Setup

  - UK Keyboard
  - Etc/UTC Timezone
  - 4 Poller Wrapper threads

#### Software

  - PHP 7
  - MariaDB
  - Syslog-ng

### Features

  - Oxidized install but not configured
  - Weathermap plugin enabled
  - Billing enabled
  - RRDCached enabled
  - Service checks enabled
  - Syslog enabled

#### Download

[OVA Image](http://www.lathwood.co.uk/librenms/librenms_centos_7.ova) - 1.1G

  - md5sum: 53f0c06c26255e859144e471ae85eedc

  - sha256sum: 9d902e8452ec8f88ab96180b46113ab5c8ef9213b16cebbbf3f88df37670ebd4

[VMDK Image](http://www.lathwood.co.uk/librenms/librenms_centos_7.vmdk) - 2.8G

  - md5sum: 8c54cb929ba9e16bc9a985a292b8f9d9

  - sha256sum: 944d1164f0664334fdb50e425049819a18da5568c56b6b49681a9ebe13ae9489

#### Credentials

> Please note the second character of the SSH password is a CAPITAL EYE

  - SSH
    - username: librenms
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
