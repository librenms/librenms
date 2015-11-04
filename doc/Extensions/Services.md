# Setting up Services

Services within LibreNMS provides the ability to use Nagios plugins to perform additional monitoring outside of SNMP.

These services are tied into an existing device so you need at least one device that supports SNMP to be able to add it 
to LibreNMS - localhost is a good one.

## Setup

Firstly, install Nagios plugins however you would like, this could be via yum, apt-get or direct from source.

Next, you need to enable the services within config.php with the following:

```php
$config['show_services']           = 1;
```
This will enable a new service menu within your navbar.

```php
$config['nagios_plugins']   = "/usr/lib/nagios/plugins";
```

This will point LibreNMS at the location of the nagios plugins - please ensure that any plugins you use are set to executable.

Finally, you now need to add check-services.php to the current cron file (/etc/cron.d/librenms typically) like:
```bash
*/5 * * * * librenms /opt/librenms/check-services.php >> /dev/null 2>&1
```

Now you can add services via the main Services link in the navbar, or via the Services link within the device page.

> **Please note that at present the service checks will only return the status and the response from the check 
no graphs will be generated. **

## Supported checks

- ftp
- icmp
- spop
- ssh
- ssl_cert
- http
- domain_expire
- mysql
- imap
- dns
- telnet
- smtp
- pop
- simap
- ntp
- ircd
