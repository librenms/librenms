> NOTE: We highly advice that you change all passwords on this image when you deploy it!!

> NOTE: Read the above note again!

We have available for download a pre-built image based on Ubuntu 16.04 LTS. Details of the image are below:

The image is built with VirtualBox, a vmdk is provided along with an ova which was exported using OFV 1.0 version. 
These should be supported in VMWare Fusion, Workstation, Player and VirtualBox.

Any issues with these images should be reported via [Github](https://github.com/librenms/librenms/issues) or our IRC channel ##librenms on the Freenode network.

#### Setup

  - UK Keyboard
  - Etc/UTC Timezone
  - LVM for disk setup
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

[OVA Image](http://www.lathwood.co.uk/librenms/librenms_ubuntu_1604.ova) - 1.6G

  - md5sum: 18c13c521aa5a6f5e96be2641324a626

  - sha256sum: 78c09dcd441633ea633118fbc51090e032257752b1f0698fcd084b2b025b6343

[VMDK Image](http://www.lathwood.co.uk/librenms/librenms_ubuntu_1604.vmdk) - 4.0G

  - md5sum: fc072de8ee6c95ccee1a7a4cd8d08f4c

  - sha256sum: 36a2252a6f6f7a3a8d7b5e2fda00eb7949a8d9d1fb637a440773aac5ebf838f3

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
