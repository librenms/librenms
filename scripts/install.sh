#!/bin/bash

# Detect OS
case $(head -n1 /etc/issue | cut -f 1 -d ' ') in
    Debian)     distro="debian" ;;
    Ubuntu)     distro="debian" ;;
    *)          distro="rhel6" ;;
esac

if [ -e '/usr/bin/wget' ]; then
	wget https://raw.githubusercontent.com/librenms/librenms/master/scripts/install-$distro.sh -O install-$distro.sh
	bash install-$distro.sh
else
	echo "Error: You need wget package to run this installer"
fi