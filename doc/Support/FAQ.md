source: Support/FAQ.md
path: blob/master/doc/

# Getting started

- [How do I install LibreNMS?](#faq1)
- [How do I add a device?](#faq2)
- [How do I get help?](#faq3)
- [What are the supported OSes for installing LibreNMS on?](#faq4)
- [Do you have a demo available?](#faq5)

# Support

- [How does LibreNMS use MIBs?](#how-does-librenms-use-mibs)
- [Why do I get blank pages sometimes in the WebUI?](#faq6)
- [Why do I not see any graphs?](#faq10)
- [How do I debug pages not loading correctly?](#faq7)
- [How do I debug the discovery process?](#faq11)
- [How do I debug the poller process?](#faq12)
- [Why do I get a lot apache or rrdtool zombies in my process list?](#faq14)
- [Why do I see traffic spikes in my graphs?](#faq15)
- [Why do I see gaps in my graphs?](#faq17)
- [How do I change the IP / hostname of a device?](#faq16)
- [My device doesn't finish polling within 300 seconds](#faq19)
- [Things aren't working correctly?](#faq18)
- [What do the values mean in my graphs?](#faq21)
- [Why does a device show as a warning?](#faq22)
- [Why do I not see all interfaces in the Overall traffic graph for a device?](#faq23)
- [How do I move my LibreNMS install to another server?](#faq24)
- [Why is my EdgeRouter device not detected?](#faq25)
- [Why are some of my disks not showing?](#faq26)
- [Why are my disks reporting an incorrect size?](#faq27)
- [What does mean \"ignore alert tag\" on device, component, service and port?](#faq28)
- [Why can't Normal and Global View users see Oxidized?](#faq29)
- [What is the Demo User for?](#faq30)
- [Why does modifying 'Default Alert Template' fail?](#faq31)
- [Why would alert un-mute itself](#faq32)
- [How do I change the Device Type?](#faq33)
- [Where do I update my database credentials?](#faq-where-do-i-update-my-database-credentials)
- [My reverse proxy is not working](#my-reverse-proxy-is-not-working)
- [My alerts aren't being delivered on time](#my-alerts-aren't-being-delivered-on-time)
- [My alert templates stopped working](#my-alert-templates-stopped-working)
- [How do I use trend prediction in graphs](#how-do-i-use-trend-prediction-in-graphs)
- [How do I move only the DB to another server](#move-db-to-another-server)
- [What are the "optional requirements message" when I add SNMPv3 devices](#optional-requirements-for-snmpv3-sha2-auth)
- [How do I clean up alerts from my switches and routers about ports being down or changing speed](#network-config-permanent-change)

# Developing

- [How do I add support for a new OS?](#faq8)
- [What information do you need to add a new OS?](#faq20)
- [What can I do to help?](#faq9)
- [How can I test another users branch?](#faq13)

## <a name="faq1"> How do I install LibreNMS?</a>

This is currently well documented within the doc folder of the installation files.

Please see the following [doc](../Installation/Installing-LibreNMS.md)

## <a name="faq2"> How do I add a device?</a>

You have two options for adding a new device into LibreNMS.

1: Using the command line via ssh you can add a new device by changing
   to the directory of your LibreNMS install and typing (be sure to
   put the correct details).

```bash
./addhost.php [community] [v1|v2c] [port] [udp|udp6|tcp|tcp6]
```

> Please note that if the community contains special characters such
> as `$` then you will need to wrap it in `'`. I.e: `'Pa$$w0rd'`.

2: Using the web interface, go to Devices and then Add Device. Enter
   the details required for the device that you want to add and then
   click 'Add Host'.

## <a name="faq3"> How do I get help?</a>

[Getting Help](index.md)

## <a name="faq4"> What are the supported OSes for installing LibreNMS on?</a>

Supported is quite a strong word :) The 'officially' supported distros are:

- Ubuntu / Debian
- Red Hat / CentOS
- Gentoo

However we will always aim to help wherever possible so if you are
running a distro that isn't one of the above then give it a try anyway
and if you need help then jump on the [discord
server](https://t.libren.ms/discord).

## <a name="faq5"> Do you have a demo available?</a>

We do indeed, you can find access to the demo [here](https://demo.librenms.org)

## <a name='how-does-librenms-use-mibs'>How does LibreNMS use MIBs?</a>

LibreNMS does not parse MIBs to discover sensors for devices.
LibreNMS uses static discovery definitions written in YAML or PHP.
Therefore, updating a MIB alone will not improve OS support, the
definitions must be updated.  LibreNMS only uses MIBs to make OIDs
easier to read.

## <a name="faq6"> Why do I get blank pages sometimes in the WebUI?</a>

You can enable debug information by setting `APP_DEBUG=true` in your
.env. (Do not leave this enabled, it could leak private data)

If the page you are trying to load has a substantial amount of data in
it then it could be that the php memory limit needs to be increased in
[config.php](Configuration.md#core).

## <a name="faq10"> Why do I not see any graphs?</a>

The easiest way to check if all is well is to run `./validate.php` as
root from within your install directory. This should give you info on
why things aren't working.

One other reason could be a restricted snmpd.conf file or snmp view
which limits the data sent back. If you use net-snmp then we suggest
using the [included
snmpd.conf](https://raw.githubusercontent.com/librenms/librenms/master/snmpd.conf.example)
file.

## <a name="faq7"> How do I debug pages not loading correctly?</a>

A debug system is in place which enables you to see the output from
php errors, warnings and notices along with the MySQL queries that
have been run for that page.

You can enable debug information by setting `APP_DEBUG=true` in your
.env. (Do not leave this enabled, it could leak private data) To see
additional information, run `./scripts/composer_wrapper.php install`,
to install additional debug tools. This will add a debug bar at the
bottom of every page that will show you detailed debug information.

## <a name="faq11"> How do I debug the discovery process?</a>

Please see the [Discovery Support](Discovery%20Support.md) document
for further details.

## <a name="faq12"> How do I debug the poller process?</a>

Please see the [Poller Support](Poller%20Support.md) document
for further details.

## <a name="faq14"> Why do I get a lot apache or rrdtool zombies in my process list?</a>

If this is related to your web service for LibreNMS then this has been
tracked down to an issue within php which the developers aren't
fixing. We have implemented a work around which means you shouldn't be
seeing this. If you are, please report this in [issue
443](https://github.com/librenms/librenms/issues/443).

## <a name="faq15"> Why do I see traffic spikes in my graphs?</a>

This occurs either when a counter resets or the device sends back
bogus data making it look like a counter reset. We have enabled
support for setting a maximum value for rrd files for ports.

Before this all rrd files were set to 100G max values, now you can
enable support to limit this to the actual port speed.

rrdtool tune will change the max value when the interface speed is
detected as being changed (min value will be set for anything 10M or
over) or when you run the included script (./scripts/tune_port.php) -
see [RRDTune doc](../Extensions/RRDTune.md)

 SNMP ifInOctets and ifOutOctets are counters, which means they start
 at 0 (at device boot) and count up from there. LibreNMS records the
 value every 5 minutes and uses the difference between the previous
 value and the current value to calculate rate. (Also, this value
 resets to 0 when it hits the max value)

Now, when the value is not recorded for awhile RRD (our time series
storage) does not record a 0, it records the last value, otherwise,
there would be even worse problems. Then finally we get the current
ifIn/OutOctets value and record that. Now, it appears as though all of
the traffic since it stopped getting values have occurred in the last
5 minute interval.

So whenever you see spikes like this, it means we have not received data from the device for several polling intervals. The cause can vary quite a bit: bad snmp implementations, intermittent network connectivity, broken poller, and more.

## <a name="faq17"> Why do I see gaps in my graphs?</a>

This is most commonly due to the poller not being able to complete
it's run within 300 seconds. Check which devices are causing this by
going to /poll-log/ within the Web interface.

When you find the device(s) which are taking the longest you can then
look at the Polling module graph under Graphs -> Poller -> Poller
Modules Performance. Take a look at what modules are taking the
longest and disabled un used modules.

If you poll a large number of devices / ports then it's recommended to
run a local recursive dns server such as pdns-recursor.

Running RRDCached is also highly advised in larger installs but has
benefits no matter the size.

## <a name="faq16"> How do I change the IP / hostname of a device?</a>

There is a host rename tool called renamehost.php in your librenms
root directory. When renaming you are also changing the device's IP /
hostname address for monitoring.

Usage:

```bash
./renamehost.php <old hostname> <new hostname>
```

You can also rename a device in the Web UI by going to the device,
then clicking settings Icon -> Edit.

## <a name="faq19"> My device doesn't finish polling within 300 seconds</a>

We have a few things you can try:

- Disable unnecessary polling modules under edit device.
- Set a max repeater value within the snmp settings for a device. What
  to set this to is tricky, you really should run an snmpbulkwalk with
  -Cr10 through -Cr50 to see what works best. 50 is usually a good
  choice if the device can cope.

## <a name="faq18"> Things aren't working correctly?</a>

Run `./validate.php` as root from within your install.

Re-run `./validate.php` once you've resolved any issues raised.

You have an odd issue - we'd suggest you join our [discord
server](https://t.libren.ms/discord) to discuss.

## <a name="faq21"> What do the values mean in my graphs?</a>

The values you see are reported as metric values. Thanks to a post on
[Reddit](https://www.reddit.com/r/networking/comments/4xzpfj/rrd_graph_interface_error_label_what_is_the_m/)
here are those values:

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

## <a name="faq22"> Why does a device show as a warning?</a>

This is indicating that the device has rebooted within the last 24
hours (by default). If you want to adjust this threshold then you can
do so by setting `$config['uptime_warning'] = '84600';` in
`config.php`. The value must be in seconds.

## <a name="faq23"> Why do I not see all interfaces in the Overall traffic graph for a device?</a>

By default numerous interface types and interface descriptions are
excluded from this graph. The excluded defaults are:

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

If you would like to re-include l2vlan interfaces for instance, you
first need to `unset` the config array and set your options:

```php
unset($config['device_traffic_iftype']);
$config['device_traffic_iftype'][] = '/loopback/';
$config['device_traffic_iftype'][] = '/tunnel/';
$config['device_traffic_iftype'][] = '/virtual/';
$config['device_traffic_iftype'][] = '/mpls/';
$config['device_traffic_iftype'][] = '/ieee8023adLag/';
$config['device_traffic_iftype'][] = '/ppp/';
```

## <a name="faq24"> How do I move my LibreNMS install to another server?</a>

If you are moving from one CPU architecture to another then you will
need to dump the rrd files and re-create them. If you are in this
scenario then you can use [Dan Brown's migration
scripts](https://vlan50.com/2015/04/17/migrating-from-observium-to-librenms/).

If you are just moving to another server with the same CPU
architecture then the following steps should be all that's needed:

- Install LibreNMS as per our normal documentation; you don't need to
  run through the web installer or building the sql schema.
- Stop cron by commenting out all lines in `/etc/cron.d/librenms`
- Dump the MySQL database `librenms` from your old server (`mysqldump
  librenms -u root -p > librenms.sql`)...
- and import it into your new server (`mysql -u root -p librenms < librenms.sql`).
- Copy the `rrd/` folder to the new server.
- Copy the `.env` and `config.php` files to the new server.
- Check for modified files (eg specific os, ...) with `git status` and
  migrate them.
- Ensure ownership of the copied files and folders (substitute your
  user if necessary) - `chown -R librenms:librenms /opt/librenms`
- Delete old pollers on the GUI (gear icon --> Pollers --> Pollers)
- Validate your installation (/opt/librenms/validate.php)
- Re-enable cron by uncommenting all lines in `/etc/cron.d/librenms`

## <a name="faq25"> Why is my EdgeRouter device not detected?</a>

If you have `service snmp description` set in your config then this
will be why, please remove this. For some reason Ubnt have decided
setting this value should override the sysDescr value returned which
breaks our detection.

If you don't have that set then this may be then due to an update of
EdgeOS or a new device type, please [create an
issue](https://github.com/librenms/librenms/issues/new).

## <a name="faq26"> Why are some of my disks not showing?</a>

If you are monitoring a linux server then net-snmp doesn't always
expose all disks via hrStorage (HOST-RESOURCES-MIB). We have
additional support which will retrieve disks via dskTable
(UCD-SNMP-MIB). To expose these disks you need to add additional
config to your snmpd.conf file. For example, to expose `/dev/sda1`
which may be mounted as `/storage` you can specify:

`disk /dev/sda1`

Or

`disk /storage`

Restart snmpd and LibreNMS should populate the additional disk after a fresh discovery.

### <a name="faq27"> Why are my disks reporting an incorrect size?</a>

There is a known issue for net-snmp, which causes it to report
incorrect disk size and disk usage when the size of the disk (or raid)
are larger then 16TB, a workaround has been implemented but is not
active on Centos 6.8 by default due to the fact that this workaround
breaks official SNMP specs, and as such could cause unexpected
behaviour in other SNMP tools. You can activate the workaround by
adding to /etc/snmp/snmpd.conf :

`realStorageUnits 0`

## <a name="faq28"> What does mean \"ignore alert tag\" on device, component, service and port?</a>

Tag device, component, service and port to ignore alerts. Alert checks will still run.
However, ignore tag can be read in alert rules. For example on device, if `devices.ignore = 0`
or `macros.device = 1` condition is is set and ignore alert tag is on,
the alert rule won't match. The alert rule is ignored.

## <a name="network-config-permanent-change"> How do I clean up alerts from my switches and routers about ports being down or changing speed</a>

Some properties used for alerting (ending in `_prev`) are only updated when a
change is detected, and not every time the poller runs. This means that if you
make a permanant change to your network such as removing a device, performing a
major firmware upgrade, or downgrading a WAN connection, you may be stuck with
some unresolvable alerts.

If a port will be permantly down, it's best practice to configure it to be
administratively down on the device to prevent malicious access. You can then
only run alerts on ports with `ifAdminStatus = up`. Otherwise, you'll need to
reset the device port state history.

On the device generating alerts, use the cog button to go to the edit device
page. At the top of the _device settings_ pane is a button labelled `Reset Port
State` - this will clear the historic state for all ports on that device,
allowing any active alerts to clear.

## <a name="faq8"> How do I add support for a new OS?</a>

Please see [Supporting a new OS](../Developing/Support-New-OS.md) if you are adding all
the support yourself, i.e. writing all of the supporting code. If you are only able
to supply supporting info, and would like the help of others to write up the code, please
follow the below steps.

## <a name="faq20"> What information do you need to add a new OS?</a>

Please [open a feature request in the community forum](https://community.librenms.org/c/feature-requests) and provide
the output of Discovery, Poller, and Snmpwalk as separate non-expiring
<https://p.libren.ms/> links :

Please use preferably the command line to obtain the information.
Especially, if snmpwalk results in a large amount of data. Replace the
relevant information in these commands such as HOSTNAME and
COMMUNITY. Use `snmpwalk` instead of `snmpbulkwalk` for v1 devices.

> These commands will automatically upload the data to LibreNMS servers.

```bash
./discovery.php -h HOSTNAME -d | ./pbin.sh
./poller.php -h HOSTNAME -r -f -d | ./pbin.sh
snmpbulkwalk -OUneb -v2c -c COMMUNITY HOSTNAME .  | ./pbin.sh
```

You can use the links provided by these commands within the community post.

If possible please also provide what the OS name should be if it doesn't exist already,
as well as any useful link (MIBs from vendor, logo, etc etc)

## <a name="faq9"> What can I do to help?</a>

Thanks for asking, sometimes it's not quite so obvious and everyone
can contribute something different. So here are some ways you can help
LibreNMS improve.

- Code. This is a big thing. We want this community to grow by the
  software developing and evolving to cater for users needs. The
  biggest area that people can help make this happen is by providing
  code support. This doesn't necessarily mean contributing code for
  discovering a new device:
  - Web UI, a new look and feel has been adopted but we are not
      finished by any stretch of the imagination. Make suggestions,
      find and fix bugs, update the design / layout.
  - Poller / Discovery code. Improving it (we think a lot can be done
    to speed things up), adding new device support and updating old
    ones.
  - The LibreNMS main website, this is hosted on GitHub like the main
    repo and we accept use contributions here as well :)
- Hardware. We don't physically need it but if we are to add device
  support, it's made a whole lot easier with access to the kit via
  SNMP.
  - If you've got MIBs, they are handy as well :)
  - If you know the vendor and can get permission to use logos that's also great.
- Bugs. Found one? We want to know about it. Most bugs are fixed after
  being spotted and reported by someone, I'd love to say we are
  amazing developers and will fix all bugs before you spot them but
  that's just not true.
- Feature requests. Can't code / won't code. No worries, chuck a
  feature request into our [community
  forum](https://community.librenms.org) with enough detail and
  someone will take a look. A lot of the time this might be what
  interests someone, they need the same feature or they just have
  time. Please be patient, everyone who contributes does so in their
  own time.
- Documentation. Documentation can always be improved and every little
  bit helps. Not all features are currently documented or documented
  well, there's spelling mistakes etc. It's very easy to submit
  updates [through the GitHub
  website](https://help.github.com/articles/editing-files-in-another-user-s-repository/),
  no git experience needed.
- Be nice, this is the foundation of this project. We expect everyone
  to be nice. People will fall out, people will disagree but please do
  it so in a respectable way.
- Ask questions. Sometimes just by asking questions you prompt deeper
  conversations that can lead us to somewhere amazing so please never
  be afraid to ask a question.

## <a name="faq13"> How can I test another users branch?</a>

LibreNMS can and is developed by anyone, this means someone may be
working on a new feature or support for a device that you want. It can
be helpful for others to test these new features, using Git, this is
made easy.

```bash
cd /opt/librenms
```

Firstly ensure that your current branch is in good state:

```bash
git status
```

If you see `nothing to commit, working directory clean` then let's go for it :)

Let's say that you want to test a users (f0o) new development branch
(issue-1337) then you can do the following:

```bash
git remote add f0o https://github.com/f0o/librenms.git
git remote update f0o
git checkout issue-1337
```

Once you are done testing, you can easily switch back to the master branch:

```bash
git checkout master
```

If you want to pull any new updates provided by f0o's branch then
whilst you are still in it, do the following:

```bash
git pull f0o issue-1337
```

## <a name="faq29"> Why can't Normal and Global View users see Oxidized?</a>

Configs can often contain sensitive data. Because of that only global
admins can see configs.

## <a name="faq30"> What is the Demo User for?</a>

Demo users allow full access except adding/editing users and deleting
devices and can't change passwords.

## <a name="faq31"> Why does modifying 'Default Alert Template' fail?</a>

This template's entry could be missing in the database. Please run
this from the LibreNMS directory:

```bash
php artisan db:seed --class=DefaultAlertTemplateSeeder
```

## <a name="faq32"> Why would alert un-mute itself?</a>

If alert un-mutes itself then it most likely means that the alert
cleared and is then triggered again. Please review eventlog as it will
tell you in there.

## <a name="faq33"> How do I change the Device Type?</a>

You can change the Device Type by going to the device you would like
to change, then click on the Gear Icon -> Edit. If you would like to
define custom types, we suggest using [Device
Groups](/Extensions/Device-Groups/). They will be listed in the
menu similarly to device types.

## <a name="faq-where-do-i-update-my-database-credentials">Where do I update my database credentials?</a>

If you've changed your database credentials then you will need to
update LibreNMS with those new details.
Please edit `.env`

[.env](../Support/Environment-Variables.md#database):

```dotenv
DB_HOST=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
DB_PORT=
```

## <a name='my-reverse-proxy-is-not-working'>My reverse proxy is not working</a>

Make sure your proxy is passing the proper variables.
At a minimum: X-Forwarded-For and X-Forwarded-Proto (X-Forwarded-Port if needed)

You also need to [Set the proxy or proxies as trusted](../Support/Environment-Variables.md#trusted-reverse-proxies)

If you are using a subdirectory on the reverse proxy and not on the actual web server,
you may need to set [APP_URL](../Support/Environment-Variables.md#base-url) and `$config['base_url']`.

## <a name='my-alerts-aren't-being-delivered-on-time'>My alerts aren't being delivered on time</a>

If you're running MySQL/MariaDB on a separate machine or container
make sure the timezone is set properly on both the LibreNMS **and**
MySQL/MariaDB instance. Alerts will be delivered according to
MySQL/MariaDB's time, so a mismatch between the two can cause alerts
to be delivered late if LibreNMS is on a timezone later than
MySQL/MariaDB.

## <a name='my-alert-templates-stopped-working'>My alert templates stopped working</a>

You should probably have a look in the documentation concerning the
new [template syntax](../Alerting/Templates.md). Since version 1.42,
syntax changed, and you basically need to convert your templates to
this new syntax (including the titles).

## <a name='how-do-i-use-trend-prediction-in-graphs'>How do I use trend prediction in graphs</a>

As of [Ver. 1.55](https://community.librenms.org/t/v1-55-release-changelog-august-2019/9428) a new feature has been added where you can view a simple linear prediction in port graphs.

> It doesn't work on non-port graphs or consolidated graphs at the time this FAQ entry was written.

To view a prediction:

- Click on any `port` graph of any network device
- Select a `From` date to your liking (not earlier than the device was actually added to LNMS), and then select a future date in the `To` field.
- Click update

You should now see a linear prediction line on the graph.
## <a name='move-db-to-another-server'>How do I move only the DB to another server?</a>

There is already a reference how to move your whole LNMS installation to another server. But the following steps will help you to split up an "All-in-one" installation to one LibreNMS installation with a separate database install.
*Note: This section assumes you have a MySQL/MariaDB instance

- Stop the apache and mysql service in you LibreNMS installation.
- Edit out all the cron entries in `/etc/cron.d/librenms`.
- Dump your `librenms`database on your current install by issuing `mysqldump librenms -u root -p > librenms.sql`.
- Stop and disable the MySQL server on your current install.
- On your new server make sure you create a new database with the standard install command, no need to add a user for localhost though.
- Copy this over to your new database server and import it with `mysql -u root -p librenms < librenms.sql`.
- Enter to mysql and add permissions with the following two commands:
```sql
GRANT ALL PRIVILEGES ON librenms.* TO 'librenms'@'IP_OF_YOUR_LNMS_SERVER' IDENTIFIED BY 'PASSWORD' WITH GRANT OPTION;
GRANT ALL PRIVILEGES ON librenms.* TO 'librenms'@'FQDN_OF_YOUR_LNMS_SERVER' IDENTIFIED BY 'PASSWORD' WITH GRANT OPTION;
FLUSH PRIVILEGES;
exit;
```
- Enable and restart MySQL server.
- Edit your `config.php` file to point the install to the new database server location.
- **Very important**: On your LibreNMS server, inside your install directory is a `.env` file, in it you need to edit the `DBHOST` paramater to point to your new server location.
- After all this is done, enable all the cron entries again and start apache.
## <a name='optional-requirements-for-snmpv3-sha2-auth'>What are the "optional requirements message" when I add SNMPv3 devices?</a>
When you add a device via the WebUI you may see a little message stating "Optional requirements are not met so some options are disabled". Do not panic. This simply means your system does not contain **openssl >= 1.1** and **net-snmp >= 5.8**, which are the minimum specifications needed to be able to use SHA-224|256|384|512 as auth algorithms.
For crypto algorithms AES-192, AES-256 you need **net-snmp** compiled with `--enable-blumenthal-aes`.
