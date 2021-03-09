source: Installation/index.md
path: blob/master/doc/

# Installing LibreNMS - 3 main routes


## Docker

Choose docker if you are somewhat familiar with docker and you just want to give the 
official docker image a quick spin for a smoketest of LibreNMS. Or, if you are very familiar 
with both docker and LibreNMS and want to build a custom installation.

There may be simpler avenues to learn about Docker than trying to learn LibreNMS and 
Docker at the same time.

An official LibreNMS docker image based on Alpine Linux and Nginx is available 
on [DockerHub](https://hub.docker.com/r/librenms/librenms/). Documentation can 
be found on the [Github repository](https://github.com/librenms/docker).

Do note that the official image (at time of writing) is respun once a month, and that 
the native daily update mechanism in LibreNMS is disabled.


## Virtual Machine Images

These are also good for a smoketest or installs not intended to scale to 1000s of devices. 
Daily updates should be possible with these.

VirtualBox images you can use to get started:

- [Virtual Machines](Images)


## Manual install

Choose manual install if you have tested either the docker image or the VM and decided that :

a) You like what you see so far.

and

b) You need something which scales to more devices, or you want daily updates, or 
you don't feel like learning docker right now.

Or for any other reason you see fit. You are not obligated to use Docker or a VM first.

If you want to install manually then we have some documentation which should make it easy.

This document includes instructions for:

 - Ubuntu 20.04
 - RHEL / CentOS 8
 - Debian 10
 
** [Install LibreNMS](Install-LibreNMS.md) **





### Old Install Docs

These install docs are no longer updated and may result in an unsuccessful install.

- [Ubuntu 18.04 Apache](Installation-Ubuntu-1804-Apache/)
- [Ubuntu 18.04 Nginx](Installation-Ubuntu-1804-Nginx/)
- [Debian 10 Nginx](Installation-Debian-10-Nginx/)
- [RHEL / CentOS 7 Apache](Installation-CentOS-7-Apache/)
- [RHEL / CentOS 7 Nginx](Installation-CentOS-7-Nginx/)
- [Ubuntu 16.04 Apache](Installation-Ubuntu-1604-Apache/)
- [Ubuntu 16.04 Nginx](Installation-Ubuntu-1604-Nginx/)
- [RHEL / CentOS 6](Installation-CentOS-6-Apache-Nginx/)
