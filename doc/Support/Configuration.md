The options shown below also contain the default values.

If you would like to alter any of these then please add your config option to `config.php`.

### Directories

```php
$config['install_dir'] = "/opt/librenms";
```
Set the installation directory (defaults to /opt/librenms), if you clone the github branch to another location ensure you alter this.

```php
$config['temp_dir'] = "/tmp";
```
The temporary directory is where images and other temporary files are created on your filesystem.

```php
$config['log_dir'] = "/opt/librenms/logs";
```
Log files created by LibreNMS will be stored within this directory.

#### Database config

These are the configuration options you will need to use to specify to get started.

```php
$config['db_host'] = '127.0.0.1';
$config['db_user'] = '';
$config['db_pass'] = '';
$config['db_name'] = '';
```

You can also select between the mysql and mysqli php extensions:

```php
$config['db']['extension'] = 'mysqli';
```

#### Programs

A lot of these are self explanatory so no further information may be provided.

```php
$config['rrdtool'] = "/usr/bin/rrdtool";
```

```php
$config['fping']            = "/usr/bin/fping";
$config['fping6']           = "fping6";
$config['fping_options']['retries'] = 3;
$config['fping_options']['timeout'] = 500;
$config['fping_options']['count'] = 3;
$config['fping_options']['millisec'] = 200;
```
`fping` configuration options:

* `retries` (`fping` parameter `-r`): Number of times an attempt at pinging a target will be made, not including the first try.
* `timeout` (`fping` parameter `-t`): Amount of time that fping waits for a response to its first request (in milliseconds).
* `count` (`fping` parameter `-c`): Number of request packets to send to each target.
* `millisec` (`fping` parameter `-p`): Time in milliseconds that fping waits between successive packets to an individual target.

You can disable the fping / icmp check that is done for a device to be determined to be up on a global or per device basis.
**We don't advice disabling the fping / icmp check unless you know the impact, at worst if you have a large number of devices down 
then it's possible that the poller would no longer complete in 5 minutes due to waiting for snmp to timeout.**

Globally disable fping / icmp check:
```php
$config['icmp_check'] = false;
```

If you would like to do this on a per device basis then you can do so under Device -> Edit -> Misc -> Disable ICMP Test? On

```php
$config['snmpwalk']         = "/usr/bin/snmpwalk";
$config['snmpget']          = "/usr/bin/snmpget";
$config['snmpbulkwalk']     = "/usr/bin/snmpbulkwalk";
```
SNMP program locations.

```php
$config['whois']            = "/usr/bin/whois";
$config['ping']             = "/bin/ping";
$config['mtr']              = "/usr/bin/mtr";
$config['nmap']             = "/usr/bin/nmap";
$config['nagios_plugins']   = "/usr/lib/nagios/plugins";
$config['ipmitool']         = "/usr/bin/ipmitool";
$config['virsh']            = "/usr/bin/virsh";
$config['dot']              = "/usr/bin/dot";
$config['unflatten']        = "/usr/bin/unflatten";
$config['neato']            = "/usr/bin/neato";
$config['sfdp']             = "/usr/bin/sfdp";
```

#### Memcached

```php
$config['memcached']['enable']  = FALSE;
$config['memcached']['host']    = "localhost";
$config['memcached']['port']    = 11211;
```
Settings to enable memcached - currently it's not recommended to run memcached until support is improved.

#### RRDCached

```php
$config['rrdcached']    = "unix:/var/run/rrdcached.sock"; // or a tcp connection 127.0.0.1:42217
$config['rrdcached_dir'] = FALSE;
```
To enable rrdcached you need to set at least the `rrdcached` option. If `rrdcached` is a tcp socket then you need to configure `rrdcached_dir` as well.
This should be set based on your base directory for running rrdcached. For instance if -b for rrdcached is set to /var/lib/rrd but you are expecting 
LibreNMS to store them in /var/lib/rrd/librenms then you would need to set `rrdcached_dir` to librenms.

#### WebUI Settings

```php
$config['base_url'] = "http://demo.librenms.org";
```
LibreNMS will attempt to detect the URL you are using but you can override that here.

```php
$config['site_style']       = "light";
```
Currently we have a number of styles which can be set which will alter the navigation bar look. dark, light and mono with light being the default.

```php
$config['stylesheet']       = "css/styles.css";
```
You can override a large number of visual elements by creating your own css stylesheet and referencing it here.

```php
$config['page_refresh']     = "300";
```
Set how often pages are refreshed in seconds. The default is every 5 minutes. Some pages don't refresh at all by design.

```php
$config['front_page']       = "pages/front/default.php";
$config['front_page_settings']['top']['ports'] = 10;
$config['front_page_settings']['top']['devices'] = 10;
$config['front_page_down_box_limit'] = 10;
$config['vertical_summary'] = 0; // Enable to use vertical summary on front page instead of horizontal
$config['top_ports']        = 1; // This enables the top X ports box
$config['top_devices']      = 1; // This enables the top X devices box
```
A number of home pages are provided within the install and can be found in html/pages/front/. You can change the default by 
setting `front_page`. The other options are used to alter the look of those pages that support it (default.php supports these options).

```php
$config['login_message']    = "Unauthorised access or use shall render the user liable to criminal and/or civil prosecution.";
```
This is the default message on the login page displayed to users.

```php
$config['public_status']    = false;
```
If this is set to true then an overview will be shown on the login page of devices and the status.

```php
$config['show_locations']          = 1;  # Enable Locations on menu
$config['show_locations_dropdown'] = 1;  # Enable Locations dropdown on menu
$config['show_services']           = 0;  # Enable Services on menu
$config['int_customers']           = 1;  # Enable Customer Port Parsing
$config['summary_errors']          = 0;  # Show Errored ports in summary boxes on the dashboard
$config['customers_descr']         = 'cust'; // The description to look for in ifDescr. Can be an array as well array('cust','cid');
$config['transit_descr']           = ""; // Add custom transit descriptions (can be an array)
$config['peering_descr']           = ""; // Add custom peering descriptions (can be an array)
$config['core_descr']              = ""; // Add custom core descriptions (can be an array)
$config['custom_descr']            = ""; // Add custom interface descriptions (can be an array)
$config['int_transit']             = 1;  # Enable Transit Types
$config['int_peering']             = 1;  # Enable Peering Types
$config['int_core']                = 1;  # Enable Core Port Types
$config['int_l2tp']                = 0;  # Enable L2TP Port Types
```
Enable / disable certain menus from being shown in the WebUI.

```php
$config['web_mouseover']      = TRUE;
```
You can disable the mouseover popover for mini graphs by setting this to FALSE.

```php
$config['show_overview_tab'] = TRUE;
```
Enable or disable the overview tabe for a device.

```php
$config['overview_show_sysDescr'] = TRUE;
```
Enable or disable the sysDescr output for a device.

```php
$config['device_traffic_iftype'][] = '/loopback/';
```
Interface types that aren't graphed in the WebUI. The default array contains more items, please see includes/defaults.inc.php for the full list.

```php
$config['enable_clear_discovery'] = 1;
```
Administrators are able to clear the last discovered time of a device which will force a full discovery run within the configured 5 minute cron window.

```php
$config['enable_footer'] = 1;
```
Disable the footer of the WebUI by setting `enable_footer` to 0.

You can enable the old style network map (only available for individual devices with links discovered via xDP) by setting:
```php
$config['gui']['network-map']['style'] = 'old';
````

#### Add host settings
The following setting controls how hosts are added.  If a host is added as an ip address it is checked to ensure the ip is not already present.  If the ip is present the host is not added.
If host is added by hostname this check is not performed.  If the setting is true hostnames are resovled and the check is also performed.  This helps prevents accidental duplicate hosts.
```php
$config['addhost_alwayscheckip']   = FALSE; #TRUE - check for duplicate ips even when adding host by name.
                                            #FALSE- only check when adding host by ip.
```

#### SNMP Settings

```php
$config['snmp']['timeout'] = 1;            # timeout in seconds
$config['snmp']['retries'] = 5;            # how many times to retry the query
$config['snmp']['transports'] = array('udp', 'udp6', 'tcp', 'tcp6');
$config['snmp']['version'] = "v2c";         # Default version to use
$config['snmp']['port'] = 161;
```
Default SNMP options including retry and timeout settings and also default version and port.

```php
$config['snmp']['community'][0] = "public";
```
The default v1/v2c snmp community to use, you can expand this array with `[1]`, `[2]`, `[3]`, etc.

```php
$config['snmp']['v3'][0]['authlevel'] = "noAuthNoPriv";  # noAuthNoPriv | authNoPriv | authPriv
$config['snmp']['v3'][0]['authname'] = "root";           # User Name (required even for noAuthNoPriv)
$config['snmp']['v3'][0]['authpass'] = "";               # Auth Passphrase
$config['snmp']['v3'][0]['authalgo'] = "MD5";            # MD5 | SHA
$config['snmp']['v3'][0]['cryptopass'] = "";             # Privacy (Encryption) Passphrase
$config['snmp']['v3'][0]['cryptoalgo'] = "AES";          # AES | DES
```
The default v3 snmp details to use, you can expand this array with `[1]`, `[2]`, `[3]`, etc.

#### Auto discovery settings

```php
$config['autodiscovery']['xdp']            = TRUE;
$config['autodiscovery']['ospf']           = TRUE;
$config['autodiscovery']['bgp']            = TRUE;
$config['autodiscovery']['snmpscan']       = TRUE;
$config['discover_services']               = FALSE;
```
Auto discovery options, xdp covers LLDP, CDP and FDP. `discover_services` will discover services from SNMP.

```php
$config['autodiscovery']['nets-exclude'][] = "0.0.0.0/8";
$config['autodiscovery']['nets-exclude'][] = "127.0.0.0/8";
$config['autodiscovery']['nets-exclude'][] = "169.254.0.0/16";
$config['autodiscovery']['nets-exclude'][] = "224.0.0.0/4";
$config['autodiscovery']['nets-exclude'][] = "240.0.0.0/4";
```
Arrays of subnets to exclude in auto discovery mode.

```php
$config['discovery_by_ip'] = true;
```
Enable auto discovery by IP. By default we only discover based on hostnames but manually adding by IP is allowed.
Please note this could lead to duplicate devices being added based on IP, Hostname or sysName.

#### Email configuration

> You can configure these options within the WebUI now, please avoid setting these options within config.php

```php
$config['email_backend']              = 'mail';
$config['email_from']                 = NULL;
$config['email_user']                 = $config['project_id'];
$config['email_sendmail_path']        = '/usr/sbin/sendmail';
$config['email_smtp_host']            = 'localhost';
$config['email_smtp_port']            = 25;
$config['email_smtp_timeout']         = 10;
$config['email_smtp_secure']          = NULL;
$config['email_smtp_auth']            = FALSE;
$config['email_smtp_username']        = NULL;
$config['email_smtp_password']        = NULL;
```
What type of mail transport to use for delivering emails. Valid options for `email_backend` are mail, sendmail or smtp. 
The varying options after that are to support the different transports.

#### Alerting

Please see [Alerting](http://docs.librenms.org/Extensions/Alerting/) section of the docs for configuration options.

#### Billing

Please see [Billing](http://docs.librenms.org/Extensions/Billing-Module/) section of the docs for setup and configuration options.

#### Global module support

```php
$config['enable_bgp']                   = 1; # Enable BGP session collection and display
$config['enable_syslog']                = 0; # Enable Syslog
$config['enable_inventory']             = 1; # Enable Inventory
$config['enable_pseudowires']           = 1; # Enable Pseudowires
$config['enable_vrfs']                  = 1; # Enable VRFs
$config['enable_printers']              = 0; # Enable Printer support
$config['enable_sla']                   = 0; # Enable Cisco SLA collection and display
```

#### Port extensions

```php
$config['port_descr_parser']            = "includes/port-descr-parser.inc.php";
```
You can extend the included port description parser with your own script here.

```php
$config['enable_ports_etherlike']       = 0;
$config['enable_ports_junoseatmvp']     = 0;
$config['enable_ports_adsl']            = 1;
$config['enable_ports_poe']             = 0;
```
Enable / disable additional port statistics.

#### External integration

```php
$config['rancid_configs'][]             = '/var/lib/rancid/network/configs/';
$config['rancid_ignorecomments']        = 0;
```
Rancid configuration, `rancid_configs` is an array containing all of the locations of your rancid files. 
Setting `rancid_ignorecomments` will disable showing lines that start with #

```php
$config['oxidized']['enabled']         = FALSE;
$config['oxidized']['url']             = 'http://127.0.0.1:8888';
```
To enable Oxidized support set enabled to `TRUE`. URL needs to be configured to point to the REST API for Oxidized. This 
is then used to retrieve the config for devices.


```php
$config['collectd_dir']                 = '/var/lib/collectd/rrd';
```
Specify the location of the collectd rrd files.

```php
$config['collectd_sock']                 = 'unix:///var/run/collectd.sock';
```
Specify the location of the collectd unix socket. Using a socket allows the collectd graphs to be flushed to disk before being drawn. Be sure that your web server has permissions to write to this socket.

```php
$config['smokeping']['dir']             = "/var/lib/smokeping/";
```
Set the smokeping directory location.

```php
$config['nfsen_enable'] = 0;
$config['nfsen_split_char']   = "_";
$config['nfsen_rrds']   = "/var/nfsen/profiles-stat/live/";
$config['nfsen_suffix']   = "_yourdomain_com";
```
NFSen integration support.
`nfsen_split_char` Is the character to replace with full stops to match the device hostname.
`nfsen_rrds` Is the location of the rrd files.
`nfsen_suffix` The domain to remove from the nfsen files.

#### Location mapping

```php
$config['location_map']['Under the Sink'] = "Under The Sink, The Office, London, UK";
```
The above is an example, this will rewrite basic snmp locations so you don't need to configure full location within snmp.

#### Interfaces to be ignored

```php
$config['bad_if'][] = "voip-null";
$config['bad_iftype'][] = "voiceEncap";
```
Numerous defaults exist for this array already (see includes/defaults.inc.php for the full list). You can expand this list 
by continuing the array.
`bad_if` is matched against the ifDescr value.
`bad_iftype` is matched against the ifType value.
`bad_if_regexp` is matched against the ifDescr value as a regular expression.

#### Interfaces to be rewritten

```php
$config['rewrite_if']['cpu'] = 'Management Interface';
$config['rewrite_if_regexp']['/cpu /'] = 'Management ';
```
Entries defined in `rewrite_if` are being replaced completely.
Entries defined in `rewrite_if_regexp` only replace the match.
Matches are compared case-insensitive.

#### Storage configuration

```php
$config['ignore_mount_removable']  = 1;
$config['ignore_mount_network']    = 1;
$config['ignore_mount_optical']    = 1;

$config['ignore_mount'][] = "/kern";
$config['ignore_mount'][] = "/mnt/cdrom";
$config['ignore_mount'][] = "/proc";
$config['ignore_mount'][] = "/dev";

$config['ignore_mount_string'][] = "packages";
$config['ignore_mount_string'][] = "devfs";
$config['ignore_mount_string'][] = "procfs";
$config['ignore_mount_string'][] = "UMA";
$config['ignore_mount_string'][] = "MALLOC";

$config['ignore_mount_regexp'][] = "/on: \/packages/";
$config['ignore_mount_regexp'][] = "/on: \/dev/";
$config['ignore_mount_regexp'][] = "/on: \/proc/";
$config['ignore_mount_regexp'][] = "/on: \/junos^/";
$config['ignore_mount_regexp'][] = "/on: \/junos\/dev/";
$config['ignore_mount_regexp'][] = "/on: \/jail\/dev/";
$config['ignore_mount_regexp'][] = "/^(dev|proc)fs/";
$config['ignore_mount_regexp'][] = "/^\/dev\/md0/";
$config['ignore_mount_regexp'][] = "/^\/var\/dhcpd\/dev,/";
$config['ignore_mount_regexp'][] = "/UMA/";
```
Mounted storage / mount points to ignore in discovery and polling.

#### IRC Bot

Please see [IRC Bot](http://docs.librenms.org/Extensions/IRC-Bot/) section of the docs for configuration options.

#### Authentication

```php
$config['auth_mechanism']           = "mysql";
```
This is the authentication type to use for the WebUI. MySQL is the default and configured when following the installation 
instructions. ldap and http-auth are also valid options. For instructions on the different authentication modules please 
see [Authentication](http://doc.librenms.org/Extensions/Authentication/).

```php
$config['auth_remember']            = '30';
```
If the user selects to be remembered on the login page, how long in days do we remember that use for.

```php
$config['allow_unauth_graphs']      = 0;
$config['allow_unauth_graphs_cidr'] = array();
```
This option will enable unauthenticated access to the graphs from `allow_unauth_graphs_cidr` ranges that you allow. Use 
 of this option is highly discouraged in favour of the [API](http://docs.librenms.org/API/API-Docs/) that is now available.

#### Cleanup options

These options rely on daily.sh running from cron as per the installation instructions.

```php
$config['syslog_purge']                                   = 30;
$config['eventlog_purge']                                 = 30;
$config['authlog_purge']                                  = 30;
$config['perf_times_purge']                               = 30;
$config['device_perf_purge']                              = 30;
```
This option will ensure data within LibreNMS over 1 month old is automatically purged. You can alter these individually, 
values are in days.

#### Syslog options

```php
$config['syslog_filter'][] = "last message repeated";
```
This array can be used to filter out syslog messages that you don't want to be stored or seen within LibreNMS

#### Virtualization

```php
$config['enable_libvirt'] = 1;
$config['libvirt_protocols']    = array("qemu+ssh","xen+ssh");
```
Enable this to switch on support for libvirt along with `libvirt_protocols`
to indicate how you connect to libvirt.  You also need to:

 1. Generate a non-password-protected ssh key for use by LibreNMS, as the
    user which runs polling & discovery (usually `librenms`).
 2. On each VM host you wish to monitor:
   - Configure public key authentication from your LibreNMS server/poller by
     adding the librenms public key to `~root/.ssh/authorized_keys`.
   - (xen+ssh only) Enable libvirtd to gather data from xend by setting
     `(xend-unix-server yes)` in `/etc/xen/xend-config.sxp` and
     restarting xend and libvirtd.

To test your setup, run `virsh -c qemu+ssh://vmhost/system list` or
`virsh -c xen+ssh://vmhost list` as your librenms polling user.
 
#### BGP Support

```php
$config['astext'][65332] = "Cymru FullBogon Feed";
```
You can use this array to rewrite the description of ASes that you have discovered.

#### Auto updates

```php
$config['update'] = 1;
```
By default, LibreNMS will auto update itself every 24 hours. You can stop this from happening by setting `update` to 0.

#### IPMI
Setup the types of IPMI protocols to test a host for and it what order.

```php
$config['ipmi']['type'] = array();
$config['ipmi']['type'][] = "lanplus";
$config['ipmi']['type'][] = "lan";
$config['ipmi']['type'][] = "imb";
$config['ipmi']['type'][] = "open";
```

#### Distributed poller settings

Please see [Distributed Poller](http://docs.librenms.org/Extensions/Distributed-Poller/) section of the docs for setup and configuration options.
