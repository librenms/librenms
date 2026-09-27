## Getting started

### <a name="faq1"> How do I install LibreNMS?</a>

The doc folder of the installation files holds the full instructions.

Read the [installation document](../Installation/Install-LibreNMS.md).

### <a name="faq2"> How do I add a device?</a>

There are two ways to add a new device to LibreNMS.

1: On the command line, connect with ssh. Then change to the directory
   of your LibreNMS install and run:

```bash
lnms device:add [hostname or ip]
```

For all the options, run `lnms device:add -h`.

> A community with a special character such as `$` needs single
> quotation marks around it. An example is `'Pa$$w0rd'`.

2: In the web interface, go to Devices, then Add Device. Enter the
   details of the device. Then click `Add Host`.

### <a name="faq3"> How do I get help?</a>

[Getting Help](index.md)

### <a name="faq4"> What are the supported OSes for installing LibreNMS on?</a>

These distributions have official support:

- Ubuntu and Debian
- Red Hat and CentOS
- Gentoo

We help with other distributions where we can. If your distribution is
not in the list, try the installation. For help, join our [discord
server](https://t.libren.ms/discord).

### <a name="faq5"> Do you have a demo available?</a>

Yes. The demo is at <https://demo.librenms.org>.


## Support

### <a name='how-does-librenms-use-mibs'>How does LibreNMS use MIBs?</a>

LibreNMS does not parse MIBs to discover the sensors of a device.
LibreNMS uses static discovery definitions in YAML or PHP. An update of
a MIB alone therefore does not improve the OS support. The definitions
also need an update. LibreNMS uses MIBs only to make OIDs easier to
read.

### <a name="faq6"> Why do I get blank pages sometimes in the WebUI?</a>

To enable debug information, set `APP_DEBUG=true` in your `.env` file.
Do not leave this setting enabled, because it can leak private data.

If the page holds a large amount of data, increase the PHP memory limit
in [config.php](Configuration.md#core).

### <a name="faq10"> Why do I not see any graphs?</a>

Become the `librenms` user and run `./validate.php` in your install
directory. The output gives the cause of the problem.

A restricted `snmpd.conf` file or a restricted SNMP view is another
cause. Such a restriction limits the data that the device returns. For
net-snmp, we recommend the [supplied
snmpd.conf](https://raw.githubusercontent.com/librenms/librenms/master/snmpd.conf.example)
file.

### <a name="faq7"> How do I debug pages not loading correctly?</a>

LibreNMS has a debug system. This system shows the PHP errors, the PHP
warnings, and the PHP notices. It also shows the MySQL queries of the
page.

To enable debug information, set `APP_DEBUG=true` in your `.env` file.
Do not leave this setting enabled, because it can leak private data.
For more information, run `./scripts/composer_wrapper.php install`.
This command installs more debug tools. It adds a debug bar at the
bottom of each page with detailed debug information.

### <a name="faq11"> How do I debug the discovery process?</a>

For more information, read the [Discovery
Support](Discovery%20Support.md) document.

### <a name="faq12"> How do I debug the poller process?</a>

For more information, read the [Poller Support](Poller%20Support.md)
document.

### <a name="faq14"> Why do I get a lot apache or rrdtool zombies in my process list?</a>

The cause is a problem in PHP. The PHP developers do not plan a
correction. We added a workaround, so this problem must not occur. If
it does occur, report it in [issue
443](https://github.com/librenms/librenms/issues/443).

### <a name="faq15"> Why do I see traffic spikes in my graphs?</a>

A spike occurs at a counter reset. It also occurs when the device
returns bad data that looks like a counter reset. LibreNMS can set a
maximum value for the RRD files of the ports.

Before this feature, all RRD files had a maximum value of 100G. You can
now limit the maximum value to the real port speed.

`rrdtool tune` changes the maximum value when the interface speed
changes. It sets a minimum value for 10M and higher. The supplied
script `lnms port:tune` does the same. For more information, read the
[RRDTune doc](../Extensions/RRDTune.md).

The SNMP objects ifInOctets and ifOutOctets are counters. Each counter
starts at 0 at the boot of the device and counts up. LibreNMS records
the value every 5 minutes. It calculates the rate from the difference
between the previous value and the current value. The counter also
returns to 0 at its maximum value.

RRD is our time series storage. When no value arrives for some time,
RRD does not record a 0. It records the last value, because a 0 causes
worse problems. LibreNMS then gets the current ifInOctets value or
ifOutOctets value and records it. All the traffic since the last good
value therefore appears in the last 5-minute interval.

A spike therefore means that no data arrived from the device for
several polling intervals. The causes are different: a bad SNMP
implementation, intermittent network connectivity, a broken poller, and
more.

### <a name="faq17"> Why do I see gaps in my graphs?</a>

The most common cause is a poller run of more than 300 seconds. To find
the devices with this problem, open `/poll-log/` in the web interface.

Find the devices with the longest run. Then open the polling module
graph under Graphs -> Poller -> Poller Modules Performance. Find the
modules with the longest time and disable the unused modules.

If you poll many devices or many ports, run a local recursive DNS
server such as pdns-recursor.

We also recommend RRDCached for a large install. It gives a benefit at
each install size.

### <a name="faq16"> How do I change the IP / hostname of a device?</a>

The `renamehost.php` tool is in your librenms root directory. A rename
also changes the IP address or the hostname for the monitoring.

Usage:

```bash
./renamehost.php <old hostname> <new hostname>
```

You can also rename a device in the web interface. Open the device,
then click the settings icon -> Edit.

### <a name="faq19"> My device does not finish polling within 300 seconds</a>

Try these corrections:

- Disable the unnecessary polling modules under edit device.
- Set a max repeater value in the SNMP settings of the device. The best
  value is difficult to find. Run an `snmpbulkwalk` with `-Cr10` to
  `-Cr50` and compare the results. 50 is usually a good value for a
  device with enough capacity.

### <a name="faq18"> Things are not working correctly?</a>

Become the `librenms` user and run `./validate.php` in your install
directory.

Correct each problem in the output. Then run `./validate.php` again.

For an unusual problem, join our [discord
server](https://t.libren.ms/discord).

### <a name="faq21"> What do the values mean in my graphs?</a>

The graphs give metric values. A post on
[Reddit](https://www.reddit.com/r/networking/comments/4xzpfj/rrd_graph_interface_error_label_what_is_the_m/)
lists these values:

```
10^-18  a - atto
10^-15  f - femto
10^-12  p - pico
10^-9   n - nano
10^-6   u - micro
10^-3   m - milli
0    (no unit)
10^3    k - kilo
10^6    M - mega
10^9    G - giga
10^12   T - tera
10^15   P - peta
```

### <a name="faq22"> Why does a device show as a warning?</a>

The warning means that the device rebooted in the last 24 hours. This
period is the default. To change the threshold, set
`$config['uptime_warning'] = '86400';` in `config.php`. The value is in
seconds.

### <a name="faq23"> Why do I not see all interfaces in the Overall traffic graph for a device?</a>

By default, this graph excludes many interface types and interface
descriptions. These are the excluded defaults:

```php
$config['device_traffic_iftype'][] = '/loopback/';
$config['device_traffic_iftype'][] = '/tunnel/';
$config['device_traffic_iftype'][] = '/virtual/';
$config['device_traffic_iftype'][] = '/mpls/';
$config['device_traffic_iftype'][] = '/ieee8023adLag/';
$config['device_traffic_iftype'][] = '/l2vlan/';
$config['device_traffic_iftype'][] = '/ppp/';

$config['device_traffic_descr'][] = '/loopback/';
$config['device_traffic_descr'][] = '/vlan/';
$config['device_traffic_descr'][] = '/tunnel/';
$config['device_traffic_descr'][] = '/bond/';
$config['device_traffic_descr'][] = '/null/';
$config['device_traffic_descr'][] = '/dummy/';
```

To include the l2vlan interfaces again, first `unset` the configuration
array. Then set your own options:

```php
unset($config['device_traffic_iftype']);
$config['device_traffic_iftype'][] = '/loopback/';
$config['device_traffic_iftype'][] = '/tunnel/';
$config['device_traffic_iftype'][] = '/virtual/';
$config['device_traffic_iftype'][] = '/mpls/';
$config['device_traffic_iftype'][] = '/ieee8023adLag/';
$config['device_traffic_iftype'][] = '/ppp/';
```

### <a name="faq24"> How do I migrate my LibreNMS install to another server?</a>

For a move to a different CPU architecture, dump the RRD files and
create them again. Use [Dan Brown's migration
scripts](https://web.archive.org/web/20180815212723/https://vlan50.com/2015/04/17/migrating-from-observium-to-librenms/).

For a move to another server with the same CPU architecture, do these
steps:

- Install LibreNMS as in our normal documentation. Do not use the web
  installer and do not build the SQL schema.
- Stop cron. Comment out all lines in `/etc/cron.d/librenms`.
- Dump the MySQL database `librenms` on your old server
  (`mysqldump librenms -u root -p > librenms.sql`).
- Import the dump into your new server
  (`mysql -u root -p librenms < librenms.sql`).
- Copy the `rrd/` folder to the new server.
- Copy the `.env` file and the `config.php` file to the new server.
- Find the modified files, such as a specific OS, with `git status`.
  Then migrate these files.
- Set the ownership of the copied files and folders with
  `chown -R librenms:librenms /opt/librenms`. Use your own user if it
  is different.
- Remove the old pollers in the web interface (gear icon --> Pollers
  --> Pollers).
- Validate your installation with `/opt/librenms/validate.php`.
- Enable cron again. Remove the comment from all lines in
  `/etc/cron.d/librenms`.

### <a name="faq25"> Why is my EdgeRouter device not detected?</a>

The cause is usually `service snmp description` in your configuration.
Remove this setting. Ubiquiti made this value override the sysDescr
value, and the override breaks our detection.

If the setting is not in your configuration, the cause is an EdgeOS
update or a new device type. Please [create an
issue](https://github.com/librenms/librenms/issues/new).

### <a name="faq26"> Why are some of my disks not showing?</a>

On a Linux server, net-snmp does not always expose all disks through
hrStorage (HOST-RESOURCES-MIB). LibreNMS also reads the disks from
dskTable (UCD-SNMP-MIB). To expose these disks, add more configuration
to your `snmpd.conf` file. For example, `/dev/sda1` can have the mount
point `/storage`. To expose this disk, use one of these lines:

`disk /dev/sda1`

Or

`disk /storage`

Restart snmpd. LibreNMS then adds the disk at the next discovery.

#### <a name="faq27"> Why are my disks reporting an incorrect size?</a>

net-snmp has a known problem. It reports an incorrect disk size and an
incorrect disk use for a disk or a raid of more than 16TB. A workaround
exists, but it is not active on CentOS 6.8 by default. The workaround
breaks the official SNMP specification and can therefore cause an
unexpected result in other SNMP tools. To enable the workaround, add
this line to `/etc/snmp/snmpd.conf`:

`realStorageUnits 0`

### <a name="faq28"> What does mean \"ignore alert tag\" on device, component, service and port?</a>

The ignore alert tag marks a device, a component, a service, or a port.
The alert checks still run. An alert rule can read the tag. For
example, a device can have the condition `devices.ignore = 0` or
`macros.device = 1`. With the ignore alert tag on, the alert rule does
not match. LibreNMS ignores the alert rule.

### <a name="network-config-permanent-change"> How do I clean up alerts from my switches and routers about ports being down or changing speed</a>

Some properties for alerting end in `_prev`. LibreNMS updates these
properties only at a change, not at each poller run. A permanent change
to your network can therefore leave an alert that does not clear.
Examples of such a change are the removal of a device, a major firmware
upgrade, and a downgrade of a WAN connection.

If a port stays down permanently, set it to administratively down on
the device. This setting also stops malicious access. You can then run
alerts only on the ports with `ifAdminStatus = up`. If you do not do
this, you must reset the port state history of the device.

On the device with the alerts, click the cog button to open the edit
device page. The button `Reset Port State` is at the top of the _device
settings_ pane. This button clears the historic state of all ports on
that device. The active alerts then clear.



### <a name="faq29"> Why cannot Normal and Global View users see Oxidized?</a>

A device configuration often holds sensitive data. Only a global
administrator can therefore see the configurations.

### <a name="faq30"> What is the Demo User for?</a>

A demo user has full access with three limits. A demo user cannot add
or edit users, cannot remove devices, and cannot change passwords.

### <a name="faq31"> Why does modifying 'Default Alert Template' fail?</a>

The entry of this template can be absent from the database. Run this
command in the LibreNMS directory:

```bash
php artisan db:seed --class=DefaultAlertTemplateSeeder
```

### <a name="faq32"> Why does an alert un-mute itself?</a>

An alert that un-mutes itself usually cleared and then triggered again.
For the details, read the eventlog.

### <a name="faq33"> How do I change the Device Type?</a>

Open the device, then click the gear icon -> Edit. For your own custom
types, we recommend [Device
Groups](../Extensions/Device-Groups.md). The menu shows device groups
in the same way as device types.

### <a name="faq34"> Editing large device groups gives error messages</a>

A device group with many devices can give form errors in the web
interface, even with correct data. The cause is the PHP variable
`max_input_vars`. The PHP error log confirms this cause.

On a basic installation with Ubuntu 22.04 LTS, Nginx, and PHP 8.1 FPM,
edit the file `/etc/php/8.1/fpm/php.ini`. Set `max_input_vars` to at
least the size of the large group. A value of `10000` is enough for a
large installation.

### <a name="faq-where-do-i-update-my-database-credentials">Where do I update my database credentials?</a>

If you changed your database credentials, put the new details into
LibreNMS. Edit the `.env` file.

[.env](../Support/Environment-Variables.md#database):

```dotenv
DB_HOST=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
DB_PORT=
```

### <a name='my-reverse-proxy-is-not-working'>My reverse proxy is not working</a>

Your proxy must pass the correct variables. The minimum set is
`X-Forwarded-For` and `X-Forwarded-Proto`. Add `X-Forwarded-Port` when
your setup needs it.

You must also [set the proxies as
trusted](../Support/Environment-Variables.md#trusted-reverse-proxies).

If the subdirectory is on the reverse proxy and not on the web server,
set [APP_URL](../Support/Environment-Variables.md#base-url) and
`$config['base_url']`.

### <a name='my-alerts-are-not-delivered-on-time'>My alerts are not delivered on time</a>

If MySQL or MariaDB runs on a separate machine or container, set the
timezone correctly on the LibreNMS instance **and** on the database
instance. LibreNMS delivers alerts on the time of the database. A
difference between the two timezones therefore delays the alerts. This
occurs when the timezone of LibreNMS is later than the timezone of the
database.

### <a name='my-alert-templates-stopped-working'>My alert templates stopped working</a>

Read the documentation on the new [template
syntax](../Alerting/Templates.md). The syntax changed in version 1.42.
Convert your templates and your titles to this new syntax.

### <a name='how-do-i-use-trend-prediction-in-graphs'>How do I use trend prediction in graphs</a>

[Version 1.55](https://community.librenms.org/t/v1-55-release-changelog-august-2019/9428)
added a simple linear prediction in port graphs.

> The prediction works only on port graphs. It does not work on other
> graphs or on consolidated graphs.

To see a prediction:

- Click a `port` graph of a network device.
- Select a `From` date. This date must not be earlier than the date of
  the device in LibreNMS. Then select a future date in the `To` field.
- Click update.

The graph then shows a linear prediction line.

### <a name='move-db-to-another-server'>How do I move only the DB to another server?</a>

Another section describes a move of the whole LibreNMS installation to
another server. The steps below divide an all-in-one installation into
one LibreNMS installation and one separate database installation.
*Note: this section assumes a MySQL instance or a MariaDB instance.

- Stop the apache service and the mysql service on your LibreNMS installation.
- Comment out all the cron entries in `/etc/cron.d/librenms`.
- Dump your `librenms` database on your current install with `mysqldump librenms -u root -p > librenms.sql`.
- Stop and disable the MySQL server on your current install.
- On your new server, create a new database with the standard install command. A user for localhost is not necessary.
- Copy the dump to your new database server and import it with `mysql -u root -p librenms < librenms.sql`.
- Open mysql and add the permissions with these two commands:
```sql
GRANT ALL PRIVILEGES ON librenms.* TO 'librenms'@'IP_OF_YOUR_LNMS_SERVER' IDENTIFIED BY 'PASSWORD' WITH GRANT OPTION;
GRANT ALL PRIVILEGES ON librenms.* TO 'librenms'@'FQDN_OF_YOUR_LNMS_SERVER' IDENTIFIED BY 'PASSWORD' WITH GRANT OPTION;
FLUSH PRIVILEGES;
exit;
```
- Enable and restart the MySQL server.
- Edit your `config.php` file. Point the install to the new database server.
- **Very important**: your install directory on the LibreNMS server holds a `.env` file. Edit the `DBHOST` parameter in this file. Point it to your new server.
- Enable all the cron entries again and start apache.

### <a name='optional-requirements-for-snmpv3-sha2-auth'>What are the "optional requirements message" when I add SNMPv3 devices?</a>

The web interface can show the message "Optional requirements are not
met so some options are disabled" when you add a device. This message
means that your system does not have **openssl >= 1.1** and
**net-snmp >= 5.8**. These are the minimum versions for the
authentication algorithms SHA-224, SHA-256, SHA-384, and SHA-512.
The crypto algorithms AES-192 and AES-256 need **net-snmp** with the
compile option `--enable-blumenthal-aes`.


## Developing

### <a name="faq8"> How do I add support for a new OS?</a>

To write all the supporting code yourself, read [Supporting a new
OS](../Developing/Support-New-OS.md). If you can supply only the
information and want help with the code, do the steps below.

### <a name="faq20"> What information do you need to add a new OS?</a>

[Open a feature request in the community
forum](https://community.librenms.org/c/feature-requests). Supply the
output of Discovery, Poller, and snmpwalk as separate pastebin links.
The links must not expire. We recommend <https://paste.rs/> or
<https://paste.sh/>.

Use the command line to get the information, because snmpwalk returns a
large amount of data. In the commands below, replace HOSTNAME and
COMMUNITY with your own values. For a v1 device, use `snmpwalk` instead
of `snmpbulkwalk`.

> These commands upload the data to the <https://paste.rs/> servers.
> You can use another service.

```bash
lnms device:discover -vv HOSTNAME | curl --data-binary @- https://paste.rs/
lnms device:poll -vv HOSTNAME | curl --data-binary @- https://paste.rs/
snmpbulkwalk -OUneb -v2c -c COMMUNITY HOSTNAME . | curl --data-binary @- https://paste.rs/
```

Put the links from these commands into your community post.

If the OS is new, also give the correct OS name. Add any useful link,
such as the MIBs from the vendor and the logo.

### <a name="faq9"> What can I do to help?</a>

Everyone can contribute something different. These are some ways to
help LibreNMS.

- Code. Code support is the largest area of help. The software must
  develop and change to meet the needs of the users. Code support does
  not only mean the discovery of a new device:
  - Web interface. The new look and feel is not complete. Make
    suggestions, find and correct bugs, and update the design and the
    layout.
  - Poller and discovery code. Make it faster, add support for new
    devices, and update the old devices.
  - The main LibreNMS website. GitHub hosts this site, in the same way
    as the main repository. We accept contributions here too.
- Hardware. We do not need the physical device. SNMP access to the
  device makes new device support much easier.
  - MIBs are also useful.
  - Permission from the vendor to use their logo is also useful.
- Bugs. Report each bug that you find. Most corrections start with a
  report from a user.
- Feature requests. If you cannot write code, put a feature request in
  our [community forum](https://community.librenms.org) with enough
  detail. Someone then examines it. Often another person needs the same
  feature or has the time to write it. Please be patient, because every
  contributor works in their own time.
- Documentation. Every improvement to the documentation helps. Some
  features have no documentation or poor documentation, and there are
  spelling mistakes. You can send updates [through the GitHub
  website](https://help.github.com/articles/editing-files-in-another-user-s-repository/).
  You do not need git experience.
- Be nice. This is the foundation of this project. We expect everyone
  to be nice. People disagree, but they must do so with respect.
- Ask questions. A question often starts a deeper conversation with a
  good result. Never be afraid to ask a question.

### <a name="faq13"> How can I test another users branch?</a>

Anyone can develop LibreNMS. Another person can therefore work on a new
feature or on support for a device that you want. Tests from other
users help. Git makes these tests easy.

```bash
cd /opt/librenms
```

First, make sure that your current branch is in a good state:

```bash
git status
```

The output must show `nothing to commit, working directory clean`.

For example, the user `f0o` has a new development branch `issue-1337`.
To test that branch, run these commands:

```bash
git remote add f0o https://github.com/f0o/librenms.git
git remote update f0o
git checkout issue-1337
```

After the tests, go back to the master branch:

```bash
git checkout master
```

To get the new updates from the branch of `f0o`, stay on that branch
and run:

```bash
git pull f0o issue-1337
```
