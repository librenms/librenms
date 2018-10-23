<?php

## Have a look in includes/defaults.inc.php for examples of settings you can set here. DO NOT EDIT defaults.inc.php!

### Database config
$config['db_host'] = '127.0.0.1';
$config['db_user'] = 'root';
$config['db_pass'] = '';
$config['db_name'] = 'librenms_phpunit_78hunjuybybh';

// This is the user LibreNMS will run as
//Please ensure this user is created and has the correct permissions to your install
$config['user'] = 'librenms';

### This should *only* be set if you want to *force* a particular hostname/port
### It will prevent the web interface being usable form any other hostname
$config['base_url']        = "/";

### Enable this to use rrdcached. Be sure rrd_dir is within the rrdcached dir
### and that your web server has permission to talk to rrdcached.
#$config['rrdcached']    = "unix:/var/run/rrdcached.sock";

### Default community
$config['snmp']['community'] = array("public");

### Authentication Model
$config['auth_mechanism'] = "mysql"; # default, other options: ldap, http-auth
#$config['http_auth_guest'] = "guest"; # remember to configure this user if you use http-auth

### List of RFC1918 networks to allow scanning-based discovery
#$config['nets'][] = "10.0.0.0/8";
#$config['nets'][] = "172.16.0.0/12";
#$config['nets'][] = "192.168.0.0/16";

# Uncomment the next line to disable daily updates
#$config['update'] = 0;

# Number in days of how long to keep old rrd files. 0 disables this feature
$config['rrd_purge'] = 0;

# Uncomment to submit callback stats via proxy
#$config['callback_proxy'] = "hostname:port";

# Enable the in-built billing extension
$config['enable_billing'] = 1;

# Enable the in-built services support (Nagios plugins)
$config['show_services'] = 1;
