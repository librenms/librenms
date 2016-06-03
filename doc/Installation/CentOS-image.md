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

[OVA Image](http://www.lathwood.co.uk/librenms/librenms_centos_7.ova) - 968M

  - md5sum: 619ef0071ee25c95cf2939a388b9021b

  - sha256sum: 39c1e129badd407b7c8c51bfa2e240ae6424947b95964872cd871f00bccaf141

[VMDK Image](http://www.lathwood.co.uk/librenms/librenms_centos_7.vmdk) - 2.5G

  - md5sum: fccbd2fdc645f706ca9da2fdfe0f11f1

  - sha256sum: 1038b4c475cd67dfbcdce3f13b482949c15cf0862a73ab50e00e4d6b253f3897

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
