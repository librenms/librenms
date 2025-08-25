[![Test Status](https://github.com/librenms/librenms/actions/workflows/test.yml/badge.svg?branch=master&event=push)](https://github.com/librenms/librenms/actions/workflows/test.yml?query=event%3Apush+branch%3Amaster)

## ⚡ Quick Start (Automated Installation)

Install LibreNMS on Ubuntu 22.04/24.04 with a single command:

```bash
curl -fsSL https://raw.githubusercontent.com/yourusername/librenms/main/scripts/install.sh | sudo bash
```

Or for more control:

```bash
# Download the installer
wget https://raw.githubusercontent.com/yourusername/librenms/main/scripts/install.sh
chmod +x install.sh

# Interactive installation
sudo ./install.sh

# Non-interactive production install
sudo ./install.sh --prod --non-interactive

# Development install with verbose output
sudo ./install.sh --dev --verbose
```

### 🎯 Installation Options

| Option | Description |
|--------|-------------|
| `--dev` | Development mode (debug enabled, less security) |
| `--prod` | Production mode (SSL support, enhanced security) |
| `--non-interactive` | Skip prompts (requires `--dev` or `--prod`) |
| `--verbose, -v` | Enable detailed logging |
| `--quiet, -q` | Suppress output except errors |
| `--help, -h` | Show help message |

### 📋 Prerequisites

- Ubuntu 22.04 LTS or 24.04 LTS
- Minimum 2GB RAM, 20GB disk space
- Root access or sudo privileges
- Internet connectivity

Introduction
------------

LibreNMS is an auto-discovering PHP/MySQL/SNMP based network monitoring
which includes support for a wide range of network hardware and operating
systems including Cisco, Linux, FreeBSD, Juniper, Brocade, Foundry, HP and
many more.

This repository includes an **automated installer** that sets up a complete 
LibreNMS environment with zero manual configuration required.

### ✨ What the Automated Installer Provides

- **🚀 Zero-touch Installation**: Complete setup from a fresh Ubuntu install
- **🔧 Two Deployment Modes**: Development and production configurations
- **🔐 Security Hardening**: Automated SSL, firewall, and security headers
- **📊 Health Monitoring**: Built-in health checks and system validation
- **🔄 Service Management**: Automatic service configuration and monitoring
- **📝 Comprehensive Logging**: Detailed installation and operation logs

We intend LibreNMS to be a viable project and community that:
- encourages contribution,
- focuses on the needs of its users, and
- offers a welcoming, friendly environment for everyone.

The [Debian Social Contract][10] will be the basis of our priority system,
and mutual respect is the basis of our behavior towards others.


Documentation
-------------

Documentation can be found in the [doc directory][5] or [docs.librenms.org][16], including instructions
for installing and contributing.


Participating
-------------

You can participate in the project by:
- Talking to us on [Discord][4] or [Twitter][3].
- Joining the [LibreNMS Community](https://community.librenms.org)
- Improving the [documentation][5].
- Cloning the [repository][2] and filing [pull requests][19] on GitHub.
- [Bug Reports](https://community.librenms.org) on our Community Forums
- See [CONTRIBUTING][15] for more details.


VM image
--------

You can try LibreNMS by downloading a VM image.  Currently, a Ubuntu-based
image is supplied and has been tested with [VirtualBox][8].

Download one of the [VirtualBox images][11] we have available, documentation is provided which details
login credentials and setup details.

License
-------

Copyright (C) 2006-2012 Adam Armstrong <adama@memetic.org>

Copyright (C) 2013-2024 by individual LibreNMS contributors

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <https://www.gnu.org/licenses/>.

[LICENSE.txt][14] contains a copy of the full GPLv3 licensing conditions.

The following additional license conditions apply to LibreNMS (a GPL
exception):

  As a special exception, you have permission to link or otherwise combine
  LibreNMS with the included copies of the following third-party software,
  and distribute modified versions, as long as you follow the requirements
  of the GNU GPL v3 in regard to all of the remaining software (comprising
  LibreNMS).

  Please see [Acknowledgements][17]

[2]: https://github.com/librenms/librenms "Main LibreNMS GitHub repo"
[3]: https://twitter.com/librenms "@LibreNMS on Twitter"
[4]: https://discord.gg/librenms "Discord LibreNMS Server"
[5]: https://github.com/librenms/librenms/tree/master/doc/
[8]: https://www.virtualbox.org/ "VirtualBox"
[10]: http://www.debian.org/social_contract "Debian project social contract"
[11]: https://www.librenms.org/#downloads
[14]: https://github.com/librenms/librenms/tree/master/LICENSE.txt
[15]: https://docs.librenms.org/General/Contributing/
[16]: https://docs.librenms.org/
[17]: https://docs.librenms.org/General/Acknowledgement/
[19]: https://github.com/librenms/librenms/pulls


## Backers

Support us with a monthly donation and help us continue our activities. [[Become a backer](https://opencollective.com/librenms#backer)]

<a href="https://opencollective.com/librenms/backer/0/website" target="_blank"><img src="https://opencollective.com/librenms/backer/0/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/1/website" target="_blank"><img src="https://opencollective.com/librenms/backer/1/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/2/website" target="_blank"><img src="https://opencollective.com/librenms/backer/2/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/3/website" target="_blank"><img src="https://opencollective.com/librenms/backer/3/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/4/website" target="_blank"><img src="https://opencollective.com/librenms/backer/4/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/5/website" target="_blank"><img src="https://opencollective.com/librenms/backer/5/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/6/website" target="_blank"><img src="https://opencollective.com/librenms/backer/6/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/7/website" target="_blank"><img src="https://opencollective.com/librenms/backer/7/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/8/website" target="_blank"><img src="https://opencollective.com/librenms/backer/8/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/9/website" target="_blank"><img src="https://opencollective.com/librenms/backer/9/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/10/website" target="_blank"><img src="https://opencollective.com/librenms/backer/10/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/11/website" target="_blank"><img src="https://opencollective.com/librenms/backer/11/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/12/website" target="_blank"><img src="https://opencollective.com/librenms/backer/12/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/13/website" target="_blank"><img src="https://opencollective.com/librenms/backer/13/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/14/website" target="_blank"><img src="https://opencollective.com/librenms/backer/14/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/15/website" target="_blank"><img src="https://opencollective.com/librenms/backer/15/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/16/website" target="_blank"><img src="https://opencollective.com/librenms/backer/16/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/17/website" target="_blank"><img src="https://opencollective.com/librenms/backer/17/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/18/website" target="_blank"><img src="https://opencollective.com/librenms/backer/18/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/19/website" target="_blank"><img src="https://opencollective.com/librenms/backer/19/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/20/website" target="_blank"><img src="https://opencollective.com/librenms/backer/20/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/21/website" target="_blank"><img src="https://opencollective.com/librenms/backer/21/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/22/website" target="_blank"><img src="https://opencollective.com/librenms/backer/22/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/23/website" target="_blank"><img src="https://opencollective.com/librenms/backer/23/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/24/website" target="_blank"><img src="https://opencollective.com/librenms/backer/24/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/25/website" target="_blank"><img src="https://opencollective.com/librenms/backer/25/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/26/website" target="_blank"><img src="https://opencollective.com/librenms/backer/26/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/27/website" target="_blank"><img src="https://opencollective.com/librenms/backer/27/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/28/website" target="_blank"><img src="https://opencollective.com/librenms/backer/28/avatar.svg"></a>
<a href="https://opencollective.com/librenms/backer/29/website" target="_blank"><img src="https://opencollective.com/librenms/backer/29/avatar.svg"></a>


## Sponsors

Become a sponsor and get your logo on our README on GitHub with a link to your site. [[Become a sponsor](https://opencollective.com/librenms#sponsor)]

<a href="https://opencollective.com/librenms/sponsor/0/website" target="_blank"><img src="https://opencollective.com/librenms/sponsor/0/avatar.svg"></a>
<a href="https://opencollective.com/librenms/sponsor/1/website" target="_blank"><img src="https://opencollective.com/librenms/sponsor/1/avatar.svg"></a>
<a href="https://opencollective.com/librenms/sponsor/2/website" target="_blank"><img src="https://opencollective.com/librenms/sponsor/2/avatar.svg"></a>
<a href="https://opencollective.com/librenms/sponsor/3/website" target="_blank"><img src="https://opencollective.com/librenms/sponsor/3/avatar.svg"></a>
<a href="https://opencollective.com/librenms/sponsor/4/website" target="_blank"><img src="https://opencollective.com/librenms/sponsor/4/avatar.svg"></a>
<a href="https://opencollective.com/librenms/sponsor/5/website" target="_blank"><img src="https://opencollective.com/librenms/sponsor/5/avatar.svg"></a>
<a href="https://opencollective.com/librenms/sponsor/6/website" target="_blank"><img src="https://opencollective.com/librenms/sponsor/6/avatar.svg"></a>
<a href="https://opencollective.com/librenms/sponsor/7/website" target="_blank"><img src="https://opencollective.com/librenms/sponsor/7/avatar.svg"></a>
<a href="https://opencollective.com/librenms/sponsor/8/website" target="_blank"><img src="https://opencollective.com/librenms/sponsor/8/avatar.svg"></a>
<a href="https://opencollective.com/librenms/sponsor/9/website" target="_blank"><img src="https://opencollective.com/librenms/sponsor/9/avatar.svg"></a>


