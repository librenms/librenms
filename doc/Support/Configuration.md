source: Support/Configuration.md
path: blob/master/doc/

# Configuration Docs

LibreNMS configuration is a set of key values.

The config is stored in two places:
Database: This applies to all pollers and can be set with either `lnms config:set` or in the Web UI. Database config takes precedence over config.php.
config.php: This applies to the local poller only.  Configs set here will be disabled in the Web UI to prevent unexpected behaviour.

The LibreNMS uses dot notation for config items:

| Database | config.php |
| -------- | ---------- |
| `snmp.community` | `$config['snmp']['community']` |
| `snmp.community.+` | `$config['snmp']['community'][]` |
| `snmp.v3.0.authalgo` | `$config['snmp']['v3'][0]['authalgo']` |

> The documentation has not been updated to reflect using `lnms config:set` to
> set config items, but it will work for all settings.  Not all settings have
> been defined in LibreNMS, but they can still be set with the `--ignore-checks`
> option.  Without that option input is checked for correctness, that does not
> mean it is not possible to set bad values.  Please report missing settings.

## CLI
`lnms config:get` will fetch the current config settings (composite of database, config.php, and defaults).  
`lnms config:set` will set the config setting in the database.  Calling `lnms config:set` on a setting with no value will reset it to the default value.

If you set up bash completion, you can use tab completion to find config settings.

### Examples

```bash
lnms config:get snmp.community
  [
      "public"
  ]


lnms config:set snmp.community.+ testing

lnms config:get snmp.community
  [
      "public",
      "testing"
  ]


lnms config:set snmp.community.0 private

lnms config:get snmp.community
  [
      "private",
      "testing"
  ]

lnms config:set snmp.community test
  Invalid format

lnms config:set snmp.community '["test", "othercommunity"]'

lnms config:get snmp.community
  [
      "test",
      "othercommunity"
  ]

lnms config:set snmp.community

  Reset snmp.community to the default? (yes/no) [no]:
  > yes


lnms config:get snmp.community
  [
      "public"
  ]
```

## Pre-load configuration

This feature is primarily for docker images and other automation.
When installing LibreNMS for the first time with a new database you can place yaml key value files
in `database/seeders/config` to pre-populate the config database.

Example snmp.yaml
```yaml
snmp.community:
    - public
    - private
snmp.max_repeaters: 30
```

## Directories

```bash
lnms config:set temp_dir /tmp
```

The temporary directory is where images and other temporary files are
created on your filesystem.

```bash
lnms config:set log_dir /opt/librenms/logs
```

Log files created by LibreNMS will be stored within this directory.

## Database config

Set these variables either in .env (/opt/librenms/.env by default) or in the environment.

```dotenv
DB_HOST=127.0.0.1
DB_DATABASE=librenms
DB_USERNAME=DBUSER
DB_PASSWORD="DBPASS"
```

Use non-standard port:

```dotenv
DB_PORT=3306
```

Use a unix socket:

```dotenv
DB_SOCKET=/run/mysqld/mysqld.sock
```

## Core

### PHP Settings

You can change the memory limits for php within `config.php`. The
value is in Megabytes and should just be an int value:

`lnms config:set php_memory_limit 128`

### Programs

A lot of these are self explanatory so no further information may be
provided. Any extensions that have dedicated  documentation page will
be linked to rather than having the config provided.

#### RRDTool

> You can configure these options within the WebUI now, please avoid
> setting these options within config.php
>
> Settings -> External Settings -> RRDTool Setup

```bash
lnms config:set rrdtool /usr/bin/rrdtool
```

Please see [1 Minute polling](1-Minute-Polling.md) for information on
configuring your install to record data more frequently.

#### fping

```bash
lnms config:set fping /usr/bin/fping
lnms config:set fping6 fping6
lnms config:set fping_options.timeout 500
lnms config:set fping_options.count 3
lnms config:set fping_options.interval 500
lnms config:set fping_options.tos 184
```

`fping` configuration options:

* `timeout` (`fping` parameter `-t`): Amount of time that fping waits
  for a response to its first request (in milliseconds). **See note
  below**
* `count` (`fping` parameter `-c`): Number of request packets to send
  to each target.
* `interval` (`fping` parameter `-p`): Time in milliseconds that fping
  waits between successive packets to an individual target.
* `tos` (`fping`parameter `-O`): Set the type of service flag (TOS). Value can be either decimal or hexadecimal (0xh) format. Can be used to ensure that ping packets are queued in following QOS mecanisms in the network. Table is accessible in the [TOS Wikipedia page](https://en.wikipedia.org/wiki/Type_of_service).

> NOTE: Setting a higher timeout value than the interval value can
> lead to slowing down poller. Example:
>
> timeout: 3000
>
> count: 3
>
> interval: 500
>
> In this example, interval will be overwritten by the timeout value
> of 3000 which is 3 seconds. As we send three icmp packets (count:
> 3), each one is delayed by 3 seconds which will result in fping
> taking > 6 seconds to return results.

You can disable the fping / icmp check that is done for a device to be
determined to be up on a global or per device basis. **We don't advise
disabling the fping / icmp check unless you know the impact, at worst
if you have a large number of devices down then it's possible that the
poller would no longer complete in 5 minutes due to waiting for snmp
to timeout.**

Globally disable fping / icmp check:

```bash
lnms config:set icmp_check false
```

If you would like to do this on a per device basis then you can do so
under Device -> Edit -> Misc -> Disable ICMP Test? On

#### traceroute

LibreNMS uses traceroute / traceroute6 to record debug information
when a device is down due to icmp AND you have
`lnms config:set debug.run_trace true` set.

```bash
lnms config:set traceroute /usr/bin/traceroute
lnms config:set traceroute6 /usr/bin/traceroute6
```

#### SNMP

```bash
lnms config:set snmpwalk /usr/bin/snmpwalk
lnms config:set snmpget /usr/bin/snmpget
lnms config:set snmpbulkwalk /usr/bin/snmpbulkwalk
```

SNMP program locations.

```bash
lnms config:set whois /usr/bin/whois
lnms config:set ping /bin/ping
lnms config:set mtr /usr/bin/mtr
lnms config:set nmap /usr/bin/nmap
lnms config:set nagios_plugins /usr/lib/nagios/plugins
lnms config:set ipmitool /usr/bin/ipmitool
lnms config:set virsh /usr/bin/virsh
lnms config:set dot /usr/bin/dot
lnms config:set sfdp /usr/bin/sfdp
```

## Authentication

Generic Authentication settings.

Password minimum length for auth that allows user creation

```bash
lnms config:set password.min_length 8
```

## Proxy support

For alerting and the callback functionality, we support the use of a
http proxy setting. These can be any one of the following:

```bash
lnms config:set callback_proxy proxy.domain.com
lnms config:set http_proxy proxy.domain.com
```

We can also make use of one of these environment variables which can be set in `/etc/environment`:

```bash
http_proxy=proxy.domain.com
https_proxy=proxy.domain.com
```

## RRDCached

Please refer to [RRDCached](../Extensions/RRDCached.md)

## WebUI Settings

```bash
lnms config:set base_url http://demo.librenms.org
```

LibreNMS will attempt to detect the URL you are using but you can override that here.

```bash
lnms config:set site_style light
```

Currently we have a number of styles which can be set which will alter
the navigation bar look. dark, light and mono with light being the default.

```bash
lnms config:set webui.custom_css.+ css/custom/styles.css
```

You can override a large number of visual elements by creating your
own css stylesheet and referencing it here, place any custom css files
into  `html/css/custom` so they will be ignored by auto updates. You
can specify as many css files as you like, the order they are within
your config will be the order they are loaded in the browser.

```bash
lnms config:set title_image images/custom/yourlogo.png
```

You can override the default logo with yours, place any custom images
files into `html/images/custom` so they will be ignored by auto updates.

```bash
lnms config:set page_refresh 300
```

Set how often pages are refreshed in seconds. The default is every 5
minutes. Some pages don't refresh at all by design.

```bash
lnms config:set front_page default
```

You can create your own front page by adding a blade file in `resources/views/overview/custom/`
and setting `front_page` to it's name.
For example, if you create `resources/views/overview/custom/foobar.blade.php`, set `front_page` to `foobar`.

```bash
// This option exists in the web UI, edit it under Global Settings -> webui
lnms config:set webui.default_dashboard_id 0
```

Allows the specification of a global default dashboard page for any user who
has not set one in their user preferences.  Should be set to dashboard_id of an
existing dashboard that is shared or shared(read).  Otherwise, the system will
automatically create each user an empty dashboard called `Default` on their
first login.

```bash
lnms config:set login_message "Unauthorised access or use shall render the user liable to criminal and/or civil prosecution."
```

This is the default message on the login page displayed to users.

```bash
lnms config:set public_status true
```

If this is set to true then an overview will be shown on the login page of devices and the status.

```bash
lnms config:set show_locations true  # Enable Locations on menu
lnms config:set show_locations_dropdown true  # Enable Locations dropdown on menu
lnms config:set show_services false  # Disable Services on menu
lnms config:set int_customers true  # Enable Customer Port Parsing
lnms config:set summary_errors false  # Show Errored ports in summary boxes on the dashboard
lnms config:set customers_descr '["cust"]'  # The description to look for in ifDescr. Can have multiple '["cust","cid"]'
lnms config:set transit_descr '["transit"]'  # Add custom transit descriptions (array)
lnms config:set peering_descr '["peering"]'  # Add custom peering descriptions (array)
lnms config:set core_descr '["core"]'  # Add custom core descriptions  (array)
lnms config:set custom_descr '["This is Custom"]'  # Add custom interface descriptions (array)
lnms config:set int_transit true  # Enable Transit Types
lnms config:set int_peering true  # Enable Peering Types
lnms config:set int_core true  # Enable Core Port Types
lnms config:set int_l2tp false  # Disable L2TP Port Types
```

Enable / disable certain menus from being shown in the WebUI.

You are able to adjust the number and time frames of the quick select
time options for graphs and the mini graphs shown per row.

Quick select:

```bash
lnms config:set graphs.mini.normal '{
    "day": "24 Hours",
    "week": "One Week",
    "month": "One Month",
    "year": "One Year"
}'

lnms config:set graphs.mini.widescreen '{
    "sixhour": "6 Hours",
    "day": "24 Hours",
    "twoday": "48 Hours",
    "week": "One Week",
    "twoweek": "Two Weeks",
    "month": "One Month",
    "twomonth": "Two Months",
    "year": "One Year",
    "twoyear": "Two Years"
}'
```

Mini graphs:

```bash
lnms config:set graphs.row.normal '{
    "sixhour": "6 Hours",
    "day": "24 Hours",
    "twoday": "48 Hours",
    "week": "One Week",
    "twoweek": "Two Weeks",
    "month": "One Month",
    "twomonth": "Two Months",
    "year": "One Year",
    "twoyear": "Two Years"
}'
```

```bash
lnms config:set web_mouseover true
```

You can disable the mouseover popover for mini graphs by setting this to false.

```bash
lnms config:set enable_lazy_load true
```

You can disable image lazy loading by setting this to false.

```bash
lnms config:set overview_show_sysDescr true
```

Enable or disable the sysDescr output for a device.

```bash
lnms config:set force_ip_to_sysname false
```

When using IP addresses as a hostname you can instead represent the
devices on the WebUI by its SNMP sysName resulting in an easier to
read overview of your network. This would apply on networks where you
don't have DNS records for most of your devices.

```bash
lnms config:set force_hostname_to_sysname false
```

When using a dynamic DNS hostname or one that does not resolve, this
option would allow you to make use of the SNMP sysName instead as the
preferred reference to the device.

```bash
lnms config:set device_traffic_iftype.+ '/loopback/'
```

Interface types that aren't graphed in the WebUI. The default array
contains more items, please see misc/config_definitions.json for the full list.

```bash
lnms config:set enable_clear_discovery true
```

Administrators are able to clear the last discovered time of a device
which will force a full discovery run within the configured 5 minute cron window.

```bash
lnms config:set enable_footer true
```

Disable the footer of the WebUI by setting `enable_footer` to 0.

You can enable the old style network map (only available for
individual devices with links discovered via xDP) by setting:

```bash
lnms config:set gui.network-map.style old
```

```bash
lnms config:set percentile_value 90
```

Show the `X`th percentile in the graph instead of the default 95th percentile.

```bash
lnms config:set shorthost_target_length 15
```

The target maximum hostname length when applying the shorthost() function.
You can increase this if you want to try and fit more of the hostname in graph titles.
The default value is 12 However, this can possibly break graph
generation if this is very long.

You can enable dynamic graphs within the WebUI under Global Settings
-> Webui Settings -> Graph Settings.

Graphs will be movable/scalable without reloading the page:
![Example dynamic graph usage](img/dynamic-graph-usage.gif)

## Stacked Graphs

You can enable stacked graphs instead of the default inverted
graphs. Enabling them is possible via webui Global Settings -> Webui
Settings -> Graph settings -> Use stacked graphs

## Add host settings

The following setting controls how hosts are added.  If a host is
added as an ip address it is checked to ensure the ip is not already
present. If the ip is present the host is not added. If host is added
by hostname this check is not performed.  If the setting is true
hostnames are resolved and the check is also performed.  This helps
prevents accidental duplicate hosts.

```bash
lnms config:set addhost_alwayscheckip false # true - check for duplicate ips even when adding host by name.
                                            # false- only check when adding host by ip.
```

By default we allow hosts to be added with duplicate sysName's, you
can disable this with the following config:

```bash
lnms config:set allow_duplicate_sysName false
```

## Global poller and discovery modules

Enable or disable discovery or poller modules.

This setting has an order of precedence Device > OS > Global.
So if the module is set at a more specific level, it will override the
less specific settings.

Global:

```bash
lnms config:set discovery_modules.arp-table false

lnms config:set discovery_modules.entity-state true
lnms config:set poller_modules.entity-state true
```

Per OS:

```bash
lnms config:set os.ios.discovery_modules.arp-table false

lnms config:set os.ios.discovery_modules.entity-state true
lnms config:set os.ios.poller_modules.entity-state true
```

## SNMP Settings

Default SNMP options including retry and timeout settings and also
default version and port.

```bash
lnms config:set snmp.timeout 1                         # timeout in seconds
lnms config:set snmp.retries 5                         # how many times to retry the query
lnms config:set snmp.transports '["udp", "udp6", "tcp", "tcp6"]'    # Transports to use
lnms config:set snmp.version '["v2c", "v3", "v1"]'       # Default versions to use
lnms config:set snmp.port 161                          # Default port
lnms config:set snmp.exec_timeout 1200                 # execution time limit in seconds
```

> NOTE: `timeout` is the time to wait for an answer and `exec_timeout`
> is the max time to run a query.

The default v1/v2c snmp community to use, you can expand this array
with `[1]`, `[2]`, `[3]`, etc.

```bash
lnms config:set snmp.community.0 public
```
>NOTE: This list of SNMP communities is used for auto discovery, and as a default set for any manually added device.

The default v3 snmp details to use, you can expand this array with
`[1]`, `[2]`, `[3]`, etc.

```bash
lnms config:set snmp.v3.0 '{
    authlevel: "noAuthNoPriv",
    authname: "root",
    authpass: "",
    authalgo: "MD5",
    cryptopass: "",
    cryptoalgo: "AES"
}'
```

```
authlevel   noAuthNoPriv | authNoPriv | authPriv
authname    User Name (required even for noAuthNoPriv)
authpass    Auth Passphrase
authalgo    MD5 | SHA | SHA-224 | SHA-256 | SHA-384 | SHA-512
cryptopass  Privacy (Encryption) Passphrase
cryptoalgo  AES | AES-192 | AES-256 | AES-256-C | DES
```

## Auto discovery settings

Please refer to [Auto-Discovery](../Extensions/Auto-Discovery.md)

## Email configuration

> You can configure these options within the WebUI now, please avoid
> setting these options within config.php

```bash
lnms config:set email_backend mail
lnms config:set email_from librenms@yourdomain.local
lnms config:set email_user `lnms config:get project_id`
lnms config:set email_sendmail_path /usr/sbin/sendmail
lnms config:set email_smtp_host localhost
lnms config:set email_smtp_port 25
lnms config:set email_smtp_timeout 10
lnms config:set email_smtp_secure tls
lnms config:set email_smtp_auth false
lnms config:set email_smtp_username NULL
lnms config:set email_smtp_password NULL
```

What type of mail transport to use for delivering emails. Valid
options for `email_backend` are mail, sendmail or smtp. The varying
options after that are to support the different transports.

## Alerting

Please refer to [Alerting](../Alerting/index.md)

## Billing

Please refer to [Billing](../Extensions/Billing-Module.md)

## Global module support

```bash
lnms config:set enable_bgp true # Enable BGP session collection and display
lnms config:set enable_syslog false # Enable Syslog
lnms config:set enable_inventory true # Enable Inventory
lnms config:set enable_pseudowires true # Enable Pseudowires
lnms config:set enable_vrfs true # Enable VRFs
```

## Port extensions

Please refer to [Port-Description-Parser](../Extensions/Port-Description-Parser.md)

```bash
lnms config:set enable_ports_etherlike false
lnms config:set enable_ports_junoseatmvp false
lnms config:set enable_ports_adsl true
lnms config:set enable_ports_poe false
```

Enable / disable additional port statistics.

## Port Group

Assign a new discovered Port automatically to Port Group with this Port Group ID
(0 means no Port Group assignment)

```php
lnms config:set default_port_group 0
```

## External integration

### Rancid

```bash
lnms config:set rancid_configs.+ /var/lib/rancid/network/configs/
lnms config:set rancid_repo_type svn
lnms config:set rancid_ignorecomments false
```

Rancid configuration, `rancid_configs` is an array containing all of
the locations of your rancid files. Setting `rancid_ignorecomments`
will disable showing lines that start with #

### Oxidized

Please refer to [Oxidized](../Extensions/Oxidized.md)

### CollectD

```bash
lnms config:set collectd_dir /var/lib/collectd/rrd
```

Specify the location of the collectd rrd files. Note that the location
in config.php should be consistent with the location set in
/etc/collectd.conf and etc/collectd.d/rrdtool.conf

```bash
<Plugin rrdtool>
        DataDir "/var/lib/collectd/rrd"
        CreateFilesAsync false
        CacheTimeout 120
        CacheFlush   900
        WritesPerSecond 50
</Plugin>
```

/etc/collectd.conf

```bash
LoadPlugin rrdtool
<Plugin rrdtool>
       DataDir "/var/lib/collectd/rrd"
       CacheTimeout 120
       CacheFlush   900
</Plugin>
```

/etc/collectd.d/rrdtool.conf

```bash
lnms config:set collectd_sock unix:///var/run/collectd.sock
```

Specify the location of the collectd unix socket. Using a socket
allows the collectd graphs to be flushed to disk before being
drawn. Be sure that your web server has permissions to write to this socket.

### Smokeping

Please refer to [Smokeping](../Extensions/Smokeping.md)

### NFSen

Please refer to [NFSen](../Extensions/NFSen.md)

### Location mapping

If you just want to set GPS coordinates on a location, you should
visit Devices > Geo Locations > All Locations and edit the coordinates
there.

Exact Matching:

```bash
lnms config:set location_map '["Under the Sink": "Under The Sink, The Office, London, UK"]'
```

Regex Matching:

```bash
lnms config:set location_map_regex '["/Sink/": "Under The Sink, The Office, London, UK"]'
```

Regex Match Substitution:

```bash
lnms config:set location_map_regex_sub '["/Sink/": "Under The Sink, The Office, London, UK [lat, long]"]'
```

If you have an SNMP SysLocation of "Rack10,Rm-314,Sink", Regex Match
Substition yields "Rack10,Rm-314,Under The Sink, The Office, London,
UK [lat, long]". This allows you to keep the SysLocation string short
and keeps Rack/Room/Building information intact after the substitution.

The above are examples, these will rewrite device snmp locations so
you don't need to configure full location within snmp.

## Interfaces to be ignored

Interfaces can be automatically ignored during discovery by modifying
bad_if\* entries in a default array, unsetting a default array and
customizing it, or creating an OS specific array. The preferred method
for ignoring interfaces is to use an OS specific array. The default
arrays can be found in misc/config_definitions.json. OS specific
definitions (includes/definitions/\_specific_os_.yaml) can contain
bad_if\* arrays, but should only be modified via pull-request as
manipulation of the definition files will block updating:

Examples:

**Add entries to default arrays**
```bash
lnms config:set bad_if.+ voip-null
lnms config:set bad_iftype.+ voiceEncap
lnms config:set bad_if_regexp.+ '/^lo[0-9].*/'    # loopback
```

**Override default bad_if values**
```bash
lnms config:set bad_if '["voip-null", "voiceEncap", "voiceFXO"]'
```

**Create an OS specific array**
```bash
lnms config:set os.iosxe.bad_iftype.+ macSecControlledIF
lnms config:set os.iosxe.bad_iftype.+ macSecUncontrolledIF
```

**Various bad_if\* selection options available**

`bad_if` is matched against the ifDescr value.

`bad_iftype` is matched against the ifType value.

`bad_if_regexp` is matched against the ifDescr value as a regular expression.

`bad_ifname_regexp` is matched against the ifName value as a regular expression.

`bad_ifalias_regexp` is matched against the ifAlias value as a regular expression.

## Interfaces that shouldn't be ignored

Examples:

```bash
lnms config:set good_if.+ FastEthernet
lnms config:set os.ios.good_if.+ FastEthernet
```

`good_if` is matched against ifDescr value. This can be a bad_if value
as well which would stop that port from being ignored. i.e. if bad_if
and good_if both contained FastEthernet then ports with this value in
the ifDescr will be valid.

## Interfaces to be rewritten

```bash
lnms config:set rewrite_if '{"cpu": "Management Interface"}'
lnms config:set rewrite_if_regexp '{"/cpu /": "Management "}'
```

Entries defined in `rewrite_if` are being replaced completely.
Entries defined in `rewrite_if_regexp` only replace the match.
Matches are compared case-insensitive.

## Entity sensors to be ignored

Some devices register bogus sensors as they are returned via SNMP but
either don't exist or just don't return data. This allows you to
ignore those based on the descr field in the database. You can either
ignore globally or on a per os basis.

```bash
lnms config:set bad_entity_sensor_regex.+ '/Physical id [0-9]+/'
lnms config:set os.ios.bad_entity_sensor_regex '["/Physical id [0-9]+/"]'
```

## Entity sensors limit values

Vendors may give some limit values (or thresholds) for the discovered
sensors. By default, when no such value is given, both high and low
limit values are guessed, based on the value measured during the initial discovery.

When it is preferred to have no high and/or low limit values at all if
these are not provided by the vendor, the guess method can be disabled:

```bash
lnms config:set sensors.guess_limits false
```

## Ignoring Health Sensors

It is possible to filter some sensors from the configuration:

* Ignore all temperature sensors

```bash
lnms config:set disabled_sensors.current true
```

* Filter all sensors matching regexp ```'/PEM Iout/'```.

```bash
lnms config:set disabled_sensors_regex.+ '/PEM Iout/'
```

* Filter all 'current' sensors for Operating System 'vrp'.

```bash
lnms config:set os.vrp.disabled_sensors.current true
```

* Filter all sensors matching regexp ```'/PEM Iout/'``` for Operating System iosxe.

```bash
lnms config:set os.iosxe.disabled_sensors_regex '/PEM Iout/'
```

## Storage configuration

Mounted storage / mount points to ignore in discovery and polling.

```bash
lnms config:set ignore_mount_removable true
lnms config:set ignore_mount_network true
lnms config:set ignore_mount_optical true

lnms config:set ignore_mount.+ /kern
lnms config:set ignore_mount.+ /mnt/cdrom
lnms config:set ignore_mount.+ /proc
lnms config:set ignore_mount.+ /dev

lnms config:set ignore_mount_string.+ packages
lnms config:set ignore_mount_string.+ devfs
lnms config:set ignore_mount_string.+ procfs
lnms config:set ignore_mount_string.+ UMA
lnms config:set ignore_mount_string.+ MALLOC

lnms config:set ignore_mount_regexp.+ '/on: \/packages/'
lnms config:set ignore_mount_regexp.+ '/on: \/dev/'
lnms config:set ignore_mount_regexp.+ '/on: \/proc/'
lnms config:set ignore_mount_regexp.+ '/on: \/junos^/'
lnms config:set ignore_mount_regexp.+ '/on: \/junos\/dev/'
lnms config:set ignore_mount_regexp.+ '/on: \/jail\/dev/'
lnms config:set ignore_mount_regexp.+ '/^(dev|proc)fs/'
lnms config:set ignore_mount_regexp.+ '/^\/dev\/md0/'
lnms config:set ignore_mount_regexp.+ '/^\/var\/dhcpd\/dev,/'
lnms config:set ignore_mount_regexp.+ '/UMA/'
```

Custom storage warning percentage

```bash
lnms config:set storage_perc_warn 60
```

## IRC Bot

Please refer to [IRC Bot](../Extensions/IRC-Bot.md)

## Authentication

Please refer to [Authentication](../Extensions/Authentication.md)

## Cleanup options

Please refer to [Cleanup Options](../Support/Cleanup-options.md)

## Syslog options

Please refer to [Syslog](../Extensions/Syslog.md)

## Virtualization

```bash
lnms config:set enable_libvirt true
lnms config:set libvirt_protocols '["qemu+ssh","xen+ssh"]'
lnms config:set libvirt_username root
```

Enable this to switch on support for libvirt along with `libvirt_protocols`
to indicate how you connect to libvirt.  You also need to:

1. Generate a non-password-protected ssh key for use by LibreNMS, as the
    user which runs polling & discovery (usually `librenms`).
1. On each VM host you wish to monitor:
   1. Configure public key authentication from your LibreNMS server/poller by
      adding the librenms public key to `~root/.ssh/authorized_keys`.
   1. (xen+ssh only) Enable libvirtd to gather data from xend by setting
      `(xend-unix-server yes)` in `/etc/xen/xend-config.sxp` and
      restarting xend and libvirtd.

To test your setup, run `virsh -c qemu+ssh://vmhost/system list` or
`virsh -c xen+ssh://vmhost list` as your librenms polling user.

## BGP Support

```bash
lnms config:set astext.65332 "Cymru FullBogon Feed"
```

You can use this array to rewrite the description of ASes that you have discovered.

## Auto updates

Please refer to [Updating](../General/Updating.md)

## IPMI

Setup the types of IPMI protocols to test a host for and in what
order. Don't forget to install ipmitool on the monitoring host.

```bash
lnms config:set ipmi.type '["lanplus", "lan", "imb", "open"]'
```

## Distributed poller settings

Please refer to [Distributed Poller](../Extensions/Distributed-Poller.md)

## API Settings

## CORS Support

<https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS>

CORS support for the API is disabled by default. Below you will find
the standard options, all of which you can configure.

```bash
lnms config:set api.cors.enabled false
lnms config:set api.cors.origin '["*"]'
lnms config:set api.cors.maxage '86400'
lnms config:set api.cors.allowmethods '["POST", "GET", "PUT", "DELETE", "PATCH"]'
lnms config:set api.cors.allowheaders '["Origin", "X-Requested-With", "Content-Type", "Accept", "X-Auth-Token"]'
lnms config:set api.cors.exposeheaders '["Cache-Control", "Content-Language", "Content-Type", "Expires", "Last-Modified", "Pragma"]'
lnms config:set api.cors.allowmethods '["POST", "GET", "PUT", "DELETE", "PATCH"]'
lnms config:set api.cors.allowheaders '["Origin", "X-Requested-With", "Content-Type", "Accept", "X-Auth-Token"]'
lnms config:set api.cors.exposeheaders '["Cache-Control", "Content-Language", "Content-Type", "Expires", "Last-Modified", "Pragma"]'
lnms config:set api.cors.allowcredentials false
```
