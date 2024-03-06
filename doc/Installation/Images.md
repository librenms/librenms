# LibreNMS VMs

> NOTE: We highly advise that you change all passwords on this image
> when you deploy it!!
>
> NOTE: These images ship with a vagrant user, please remove this user
> account when you deploy it!!
>
> NOTE: Read the above note again!

We have available for download a pre-built image based on Ubuntu 22.04.
These images are built using [packer.io](https://packer.io).

Details of the image and it's setup are:

At present we provide the following builds:

- OVA Built with VirtualBox.
- OVA Built for VMWare ESXi.
- Vagrant Box file.

- Any issues and or help with these images should be reported via
  [Community Forum](https://community.librenms.org) or our [Discord
  server](https://t.libren.ms/discord)

## Setup

- US Keyboard
- Etc/UTC Timezone
- 4 Poller Wrapper threads

## Software

- PHP 8.1
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
specific version, you will need to disable updates `lnms config:set update false`.

## Access/Credentials

If you are using the VirtualBox image then to access your newly imported VM, 
these ports are forwarded from your machine to the VM: 8080 for WebUI and 2023 for SSH.
Remember to edit/remove them if you change (and you should) the VM network configuration.

- WebUI (http://localhost)
  - username: librenms
  - password: `D32fwefwef`

- SSH (change the password ssh://localhost:2023)
  - username: librenms
  - password: `CDne3fwdfds`

- SSH (remove this account)
  - username: vagrant
  - password; vagrant

- MySQL/MariaDB
  - username: librenms
  - password: `D42nf23rewD`

## Contributing

If you would like to help with these images whether it's add
additional features or default software / settings then you can do so
on [GitHub](https://github.com/librenms/packer-builds/).
