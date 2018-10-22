<?php
## Have a look in defaults.inc.php for examples of settings you can set here. DO NOT EDIT defaults.inc.php!

### Database config
$config['db_host'] = 'localhost';
$config['db_port'] = '3306';
$config['db_user'] = 'librenms';
$config['db_pass'] = '*mlu2rv2W';
$config['db_name'] = 'librenms';
$config['db_socket'] = '';

// This is the user LibreNMS will run as
//Please ensure this user is created and has the correct permissions to your install
$config['user'] = 'librenms';

### Locations - it is recommended to keep the default
#$config['install_dir']  = "/opt/librenms";

### This should *only* be set if you want to *force* a particular hostname/port
### It will prevent the web interface being usable form any other hostname
#$config['base_url']        = "http://librenms.company.com";

### Enable this to use rrdcached. Be sure rrd_dir is within the rrdcached dir
### and that your web server has permission to talk to rrdcached.
#$config['rrdcached']    = "unix:/var/run/rrdcached.sock";

### Default community
$config['snmp']['community'] = ("snmpromsh");

### Authentication Model
#$config['auth_mechanism'] = "mysql"; # default, other options: ldap, http-auth
#$config['http_auth_guest'] = "guest"; # remember to configure this user if you use http-auth
$config['auth_mechanism'] = 'active_directory';
$config['auth_ad_url'] = 'ldaps://ad.mssm.edu';
$config['auth_ad_domain'] = 'mssmcampus.mssm.edu';
$config['auth_ad_base_dn'] = 'dc=mssmcampus,dc=mssm,dc=edu';
$config['auth_ad_check_certificates'] = true;
$config['auth_ad_binduser'] = 'proxyuser';
$config['auth_ad_bindpassword'] = 'Pr0xypa$$';
$config['auth_ad_timeout'] = 5;
$config['auth_ad_debug'] = false;
$config['active_directory']['users_purge'] = 30;
$config['auth_ad_require_groupmembership'] = true;
$config['auth_ad_groups']['mssmunix']['level'] = 10;
$config['auth_ad_groups']['mktg-dsm']['level'] = 1;
#$config['auth_ad_groups']['ad-usergroup']['level'] = 5;

### List of RFC1918 networks to allow scanning-based discovery
#$config['nets'][] = "10.0.0.0/8";
#$config['nets'][] = "172.16.0.0/12";
#$config['nets'][] = "192.168.0.0/16";

# Update configuration
#$config['update_channel'] = 'release';  # uncomment to follow the monthly release channel
#$config['update'] = 0;  # uncomment to completely disable updates
$config['show_services']           = 1;
$config['nagios_plugins']   = "/usr/lib/nagios/plugins";
# New poller
$config['service_poller_workers']              = 24;     # Processes spawned for polling
$config['service_services_workers']            = 8;      # Processes spawned for service polling
$config['service_discovery_workers']           = 16;     # Processes spawned for discovery


//Optional Settings
$config['service_poller_frequency']            = 300;    # Seconds between polling attempts
$config['service_services_frequency']          = 300;    # Seconds between service polling attempts
$config['service_discovery_frequency']         = 21600;  # Seconds between discovery runs
$config['service_billing_frequency']           = 300;    # Seconds between billing calculations
$config['service_billing_calculate_frequency'] = 60;     # Billing interval
$config['service_poller_down_retry']           = 60;     # Seconds between failed polling attempts
$config['service_loglevel']                    = 'INFO'; # Must be one of 'DEBUG', 'INFO', 'WARNING', 'ERROR', 'CRITICAL'
$config['service_update_frequency']            = 86400;  # Seconds between LibreNMS update checks
$config['alert']['macros']['rule']['8_to_6'] = 'HOUR(NOW()) BETWEEN 8 AND 18';
$config['alert']['macros']['rule']['Mon_to_Fri'] = 'DAYOFWEEK(NOW()) BETWEEN 2 AND 6';

