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
and mutual respect is the basis of our behaviour towards others.  For more
about the culture we're trying to build, please read the [Freenode
philosophy][13], including [guidelines for running an IRC channel][6] and
[being a community catalyst][7].


Documentation
-------------

Documentation can be found in the [doc directory][5] or [docs.librenms.org][16], including instructions
for installing and contributing.


Participating
-------------

You can participate in the project by:
- Talking to us on IRC ([##librenms on Freenode][4]) or [Twitter][3].
- Joining the [librenms-project][1] mailing list.
- Improving the [documentation][5].
- Cloning the [repo][2] and filing [bug reports][18] and [pull requests][19] on github.
  See [CONTRIBUTING][15] for more details.


VM image
--------

You can try LibreNMS by downloading a VM image.  Currently, a Ubuntu-based
image is supplied and has been tested with [VirtualBox][8].

Download the [VirtualBox / VMWare image][11] and open it then log in with credentials provided. 
Enter the following commands:

    cd /opt/librenms
    git pull
    php discover.php -h all
    php poller.php -h all

You'll then need to find out the IP of your VM (`ifconfig | grep add`) and
create a DNS entry for `librenms.example.com` to point to that IP.  You can
also edit your `/etc/hosts` file with the following line:

    $ip librenms.example.com

where `$ip` is the IP of your VM.

Add a new user by entering:

    ./adduser.php <username> <password> 10 <email>

replace <username>, <password> and <email> with a username, password and your email address.

From there, just point your web browser
to `http://librenms.example.com/` and login with your new username and password.


License
-------

Copyright (C) 2006-2012 Adam Armstrong <adama@memetic.org>

Copyright (C) 2013-2016 by individual LibreNMS contributors

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

  Please see [Acknowledgements][17]

[1]: https://groups.google.com/forum/#!forum/librenms-project "LibreNMS"
[2]: https://github.com/librenms/librenms "Main LibreNMS GitHub repo"
[3]: https://twitter.com/librenms "@LibreNMS on Twitter"
[4]: irc://irc.freenode.net/##librenms "LibreNMS IRC channel"
[5]: https://github.com/librenms/librenms/tree/master/doc/
[6]: http://freenode.net/channel_guidelines.shtml "Freenode channel guidelines"
[7]: http://freenode.net/catalysts.shtml "Freenode community catalysts"
[8]: https://www.virtualbox.org/ "VirtualBox"
[9]: http://observium.org/ "Observium web site"
[10]: http://www.debian.org/social_contract "Debian project social contract"
[11]: http://www.librenms.org/#downloads
[12]: https://github.com/librenms/librenms/tree/master/doc/Observium_Welcome.md
[13]: http://freenode.net/philosophy.shtml "Freenode philosophy"
[14]: https://github.com/librenms/librenms/tree/master/LICENSE.txt
[15]: http://docs.librenms.org/General/Contributing/
[16]: http://docs.librenms.org/
[17]: http://docs.librenms.org/General/Acknowledgement/
[18]: https://github.com/librenms/librenms/issues
[19]: https://github.com/librenms/librenms/pulls
