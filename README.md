Introduction
------------

LibreNMS is an autodiscovering PHP/MySQL/SNMP based network monitoring
which includes support for a wide range of network hardware and operating
systems including Cisco, Linux, FreeBSD, Juniper, Brocade, Foundry, HP and
many more.  LibreNMS is a community-based fork of [Observium][9].

We intend LibreNMS to be a viable project and community that:
- encourages contribution,
- focuses on the needs of its users, and
- offers a welcoming, friendly environment for everyone.

The [Debian Social Contract][10] will be the basis of our priority system,
and mutual respect the basis of our behaviour towards others.  For more
about the culture we're trying to build, please read the [Freenode
philosophy][13], including [guidelines for running an IRC channel][6] and
[being a community catalyst][7].


Documentation
-------------

Documentation can be found in the [doc directory][5], including instructions
for installing and contributing.


Participating
-------------

You can participate in the project by:
- Joining the [librenms-project][1] mailing list to post questions and
  suggestions.
- Talking to us on [Twitter][3] or [IRC][4].
- Improving the [documentation][5].
- Cloning the [repo][2] and filing bug reports and pull requests on github.
  See [CONTRIBUTING][15] for more details.


Try It
------

You can try LibreNMS by downloading a VM image.  Currently, a Debian-based
image is supplied and has been tested with [VMware Fusion 5][8].

Download the [VMware Fusion 5 image][11] at open it, and log in as `root`
with the password `root`.  Enter the following commands:

    cd /opt/librenms
    git pull
    php discover.php -h all
    php poller.php -h all

You'll then need to find out the IP of your VM (`ifconfig | grep add`) and
create a DNS entry for `librenms.example.com` to point to that IP.  You can
also edit your `/etc/hosts` file with the following line:

    $ip librenms.example.com

where `$ip` is the IP of your VM.  From there, just point your web browser
to `http://librenms.example.com/` and login with username `librenms` and
password `librenms`.


License
-------

Copyright (C) 2006-2012 Adam Armstrong <adama@memetic.org>

Copyright (C) 2013-2014 by individual LibreNMS contributors

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.

[LICENSE.txt][14] contains a copy of the full GPLv3 licensing conditions.

The following additional license conditions apply to LibreNMS (a GPL
exception):

  As a special exception, you have permission to link or otherwise combine
  LibreNMS with the included copies of the following third-party software,
  and distribute modified versions, as long as you follow the requirements
  of the GNU GPL v3 in regard to all of the remaining software (comprising
  LibreNMS).

  The authorised third-party software packages under this exception are as
  follows (their title, location, and license are noted where known):
  - JpGraph (html/includes/jpgraph): QPL 1.0 license
  - MIBS (mibs): unknown/various
  - html/graph-realtime.php: BSD (original?)
  - html/includes/collectd/: GPLv2 only
  - overLIB (html/js/overlib_mini.js): modified Artistic 1.0?
  - scripts/*/mysql: GPLv2 only
  - check_mk (scripts/observium_agent*): GPLv2

[1]: https://groups.google.com/forum/#!forum/librenms-project "LibreNMS"
[2]: https://github.com/librenms/librenms "Main LibreNMS GitHub repo"
[3]: https://twitter.com/librenms "@LibreNMS on Twitter"
[4]: irc://irc.freenode.net/##librenms "LibreNMS IRC channel"
[5]: https://github.com/librenms/librenms/tree/master/doc/
[6]: http://freenode.net/channel_guidelines.shtml "Freenode channel guidelines"
[7]: http://freenode.net/catalysts.shtml "Freenode community catalysts"
[8]: http://www.vmware.com/products/fusion/ "VMware Fusion"
[9]: http://observium.org/ "Observium web site"
[10]: http://www.debian.org/social_contract "Debian project social contract"
[11]: ftp://librenms.label-switched.net/pub/librenms_vm.zip
[12]: https://github.com/librenms/librenms/tree/master/doc/Observium_Welcome.md
[13]: http://freenode.net/philosophy.shtml "Freenode philosophy"
[14]: https://github.com/librenms/librenms/tree/master/LICENSE.txt
[15]: https://github.com/librenms/librenms/tree/master/doc/CONTRIBUTING.md

