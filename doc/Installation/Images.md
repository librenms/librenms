# CentOS

> NOTE: We highly advise that you change all passwords on this image
> when you deploy it!!
>
> NOTE: Read the above note again!

We have available for download a pre-built image based on CentOS 7 and
Ubuntu 18.04. These images are built using
[packer.io](https://packer.io).

Details of the image and it's setup are:

At present we provide the following builds:

- OVA Built with VirtualBox.

- Any issues and or help with these images should be reported via
  [Community Forum](https://community.librenms.org) or our [Discord
  server](https://t.libren.ms/discord)

## Setup

- US Keyboard
- Etc/UTC Timezone
- 4 Poller Wrapper threads

## Software

- PHP 7
- MariaDB
- Syslog-ng

## Features

- Oxidized installed but not configured
- Weathermap plugin enabled
- Billing enabled
- RRDCached enabled
- Service checks enabled
- Syslog enabled

## Download

All images can be downloaded from
[GitHub](https://github.com/librenms/packer-builds/releases/latest). The
tags follow the main LibreNMS repo. When a new LibreNMS release is
available we will push new images out running that version. Please do
note that if you download an older release with a view to running that
specific version, you will need to disable updates in config.php.

## Access/Credentials

To access your newly imported VM, those ports are forwarded from your machine to the VM: 8080 for WebUI and 2023 for SSH.
Remember to edit/remove them if you change (and you should) the VM network configuration.

- WebUI (http://localhost:8080)
  - username: librenms
  - password: `D32fwefwef`

- SSH (ssh://localhost:2023)
  - username: librenms
  - password: `CDne3fwdfds`

- MySQL/MariaDB
  - username: librenms
  - password: `D42nf23rewD`

## Contributing

If you would like to help with these images whether it's add
additional features or default software / settings then you can do so
on [GitHub](https://github.com/librenms/packer-builds/).
