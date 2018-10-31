source: Extensions/Services.md
path: blob/master/doc/
[TOC]

# Setting up services

Services within LibreNMS provides the ability to use Nagios plugins to perform additional monitoring outside of SNMP.

**These services are tied into an existing device so you need at least one device to be able to add it
to LibreNMS - localhost is a good one. This is needed in order for alerting to work properly.**

## Pre installed plugins

List of pre installed plugins in the format: \<name\> - \<origin\>

Plugins will only load if they are prefixed with "check_" and they have that prefix stripped out when displaying in the "Add Serice" GUI "Type" dropdown list.

* [ajp](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_ajp) - pkg-nagios-plugins-contrib
* [apt](https://www.monitoring-plugins.org/doc/man/check_apt.html) - monitoring-plugins
* [backuppc](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_backuppc) - pkg-nagios-plugins-contrib
* [bgpstate](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_bgpstate) - pkg-nagios-plugins-contrib
* [breeze](https://www.monitoring-plugins.org/doc/man/check_breeze.html) - monitoring-plugins
* [by_ssh](https://www.monitoring-plugins.org/doc/man/check_by_ssh.html) - monitoring-plugins
* [cert_expire](https://github.com/bzed/pkg-nagios-plugins-contrib/blob/5c25f573a59345cc9fa000d3143eb668f3350c8a/dsa/checks/dsa-check-cert-expire) - pkg-nagios-plugins-contrib
* [checksums](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_checksums) - pkg-nagios-plugins-contrib
* [clamav](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_clamav) - 
* [clamd](https://www.monitoring-plugins.org/doc/man/check_clamd.html) - monitoring-plugins
* [cluster](https://www.monitoring-plugins.org/doc/man/check_cluster.html) - monitoring-plugins
* [cups](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_cups) - pkg-nagios-plugins-contrib
* [dbi](https://www.monitoring-plugins.org/doc/man/check_dbi.html) - monitoring-plugins
* [dhcp](https://www.monitoring-plugins.org/doc/man/check_dhcp.html) - monitoring-plugins
* [dig](https://www.monitoring-plugins.org/doc/man/check_dig.html) - monitoring-plugins
* [disk](https://www.monitoring-plugins.org/doc/man/check_disk.html) - monitoring-plugins
* [disk_smb](https://www.monitoring-plugins.org/doc/man/check_disk_smb.html) - monitoring-plugins
* [dns](https://www.monitoring-plugins.org/doc/man/check_dns.html) - monitoring-plugins
* [dnssec_delegation](https://github.com/bzed/pkg-nagios-plugins-contrib/blob/5c25f573a59345cc9fa000d3143eb668f3350c8a/dsa/checks/dsa-check-dnssec-delegation) - pkg-nagios-plugins-contrib
* [drbd](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_drbd) - pkg-nagios-plugins-contrib
* [dummy](https://www.monitoring-plugins.org/doc/man/check_dummy.html) - monitoring-plugins
* [email_delivery](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_email_delivery) - pkg-nagios-plugins-contrib
* [email_delivery_epn]() - 
* [entropy](https://github.com/bzed/pkg-nagios-plugins-contrib/blob/5c25f573a59345cc9fa000d3143eb668f3350c8a/dsa/checks/dsa-check-entropy) - pkg-nagios-plugins-contrib
* [etc_hosts]() - 
* [etc_resolv]() - 
* [file_age](https://www.monitoring-plugins.org/doc/man/check_file_age.html) - monitoring-plugins
* [flexlm](https://www.monitoring-plugins.org/doc/man/check_flexlm.html) - monitoring-plugins
* [fping](https://www.monitoring-plugins.org/doc/man/check_fping.html) - monitoring-plugins
* [ftp]() - 
* [game](https://www.monitoring-plugins.org/doc/man/check_game.html) - monitoring-plugins
* [graphite](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_graphite) - pkg-nagios-plugins-contrib
* [haproxy](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_haproxy) - pkg-nagios-plugins-contrib
* [haproxy_stats](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_haproxy_stats) - pkg-nagios-plugins-contrib
* [host]() - 
* [hp_bladechassis](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_hp_bladechassis) - pkg-nagios-plugins-contrib
* [hpasm](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_hpasm) - 
* [hpjd](https://www.monitoring-plugins.org/doc/man/check_hpjd.html) - monitoring-plugins
* [http](https://www.monitoring-plugins.org/doc/man/check_http.html) - monitoring-plugins
* [httpd_status](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_httpd_status) - pkg-nagios-plugins-contrib
* [icmp](https://www.monitoring-plugins.org/doc/man/check_icmp.html) - monitoring-plugins
* [ide_smart]() - 
* [ifoperstatus]() - 
* [ifstatus]() - 
* [imap]() - 
* [imap_quota]() - 
* [imap_quota_epn]() - 
* [imap_receive]() - 
* [imap_receive_epn]() - 
* [ipmi_sensor]() - 
* [ircd](https://www.monitoring-plugins.org/doc/man/check_ircd.html) - monitoring-plugins
* [jabber](https://www.monitoring-plugins.org/doc/man/check_jabber.html) - monitoring-plugins
* [ldap](https://www.monitoring-plugins.org/doc/man/check_ldap.html) - monitoring-plugins
* [ldap_root]() - 
* [ldaps](https://www.monitoring-plugins.org/doc/man/check_ldaps.html) - monitoring-plugins
* [libs](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_libs) - pkg-nagios-plugins-contrib
* [libvirt](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_libvirt) - pkg-nagios-plugins-contrib
* [lm_sensors]() - 
* [load](https://www.monitoring-plugins.org/doc/man/check_load.html) - monitoring-plugins
* [log](https://www.monitoring-plugins.org/doc/man/check_log.html) - monitoring-plugins
* [mailq](https://www.monitoring-plugins.org/doc/man/check_mailq.html) - monitoring-plugins
* [memcached](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_memcached) - pkg-nagios-plugins-contrib
* [memory](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_memory) - pkg-nagios-plugins-contrib
* [mongodb](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_mongodb) - pkg-nagios-plugins-contrib
* [mrtg](https://www.monitoring-plugins.org/doc/man/check_mrtg.html) - monitoring-plugins
* [mrtgtraf](https://www.monitoring-plugins.org/doc/man/check_mrtgtraf.html) - monitoring-plugins
* [multipath](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_multipath) - pkg-nagios-plugins-contrib
* [mysql](https://www.monitoring-plugins.org/doc/man/check_mysql.html) - monitoring-plugins
* [mysql_health](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_mysql_health) - pkg-nagios-plugins-contrib
* [mysql_query](https://www.monitoring-plugins.org/doc/man/check_mysql_query.html) - monitoring-plugins
* [nagios](https://www.monitoring-plugins.org/doc/man/check_nagios.html) - monitoring-plugins
* [nfsmounts](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_nfsmounts) - pkg-nagios-plugins-contrib
* [nntp](https://www.monitoring-plugins.org/doc/man/check_nntp.html) - monitoring-plugins
* [nntps](https://www.monitoring-plugins.org/doc/man/check_nntps.html) - monitoring-plugins
* [nt](https://www.monitoring-plugins.org/doc/man/check_nt.html) - monitoring-plugins
* [ntp](https://www.monitoring-plugins.org/doc/man/check_ntp.html) - monitoring-plugins
* [ntp_peer](https://www.monitoring-plugins.org/doc/man/check_ntp_peer.html) - monitoring-plugins
* [ntp_time](https://www.monitoring-plugins.org/doc/man/check_ntp_time.html) - monitoring-plugins
* [nwstat](https://www.monitoring-plugins.org/doc/man/check_nwstat.html) - monitoring-plugins
* [oracle](https://www.monitoring-plugins.org/doc/man/check_oracle.html) - monitoring-plugins
* [overcr](https://www.monitoring-plugins.org/doc/man/check_overcr.html) - monitoring-plugins
* [packages](https://github.com/bzed/pkg-nagios-plugins-contrib/blob/5c25f573a59345cc9fa000d3143eb668f3350c8a/dsa/checks/dsa-check-packages) - pkg-nagios-plugins-contrib
* [pgsql](https://www.monitoring-plugins.org/doc/man/check_pgsql.html) - monitoring-plugins
* [ping](https://www.monitoring-plugins.org/doc/man/check_ping.html) - monitoring-plugins
* [pop](https://www.monitoring-plugins.org/doc/man/check_pop.html) - monitoring-plugins
* [printer](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_printer) - pkg-nagios-plugins-contrib
* [procs](https://www.monitoring-plugins.org/doc/man/check_procs.html) - monitoring-plugins
* [raid](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_raid) - pkg-nagios-plugins-contrib
* [rbl](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_rbl) - pkg-nagios-plugins-contrib
* [real](https://www.monitoring-plugins.org/doc/man/check_real.html) - monitoring-plugins
* [redis](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_redis) - pkg-nagios-plugins-contrib
* [rpc](https://www.monitoring-plugins.org/doc/man/check_rpc.html) - monitoring-plugins
* [rta_multi]() - 
* [running_kernel](https://github.com/bzed/pkg-nagios-plugins-contrib/blob/5c25f573a59345cc9fa000d3143eb668f3350c8a/dsa/checks/dsa-check-running-kernel) - pkg-nagios-plugins-contrib
* [sensors](https://www.monitoring-plugins.org/doc/man/check_sensors.html) - monitoring-plugins
* [shutdown]() - 
* [simap](https://www.monitoring-plugins.org/doc/man/check_simap.html) - monitoring-plugins
* [smstools](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_smstools) - pkg-nagios-plugins-contrib
* [smtp](https://www.monitoring-plugins.org/doc/man/check_smtp.html) - monitoring-plugins
* [smtp_send]() - 
* [smtp_send_epn]() - 
* [snmp](https://www.monitoring-plugins.org/doc/man/check_snmp.html) - monitoring-plugins
* [snmp_environment](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_snmp_environment) - pkg-nagios-plugins-contrib
* [snmp_time](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_snmp_time) - pkg-nagios-plugins-contrib
* [soas](https://github.com/bzed/pkg-nagios-plugins-contrib/blob/5c25f573a59345cc9fa000d3143eb668f3350c8a/dsa/checks/dsa-check-soas) - pkg-nagios-plugins-contrib
* [spop](https://www.monitoring-plugins.org/doc/man/check_spop.html) - monitoring-plugins
* [ssh](https://www.monitoring-plugins.org/doc/man/check_ssh.html) - monitoring-plugins
* [ssl_cert](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_ssl_cert) - pkg-nagios-plugins-contrib
* [ssmtp](https://www.monitoring-plugins.org/doc/man/check_ssmtp.html) - monitoring-plugins
* [statusfile](https://github.com/bzed/pkg-nagios-plugins-contrib/blob/5c25f573a59345cc9fa000d3143eb668f3350c8a/dsa/checks/dsa-check-statusfile) - pkg-nagios-plugins-contrib
* [swap](https://www.monitoring-plugins.org/doc/man/check_swap.html) - monitoring-plugins
* [tcp](https://www.monitoring-plugins.org/doc/man/check_tcp.html) - monitoring-plugins
* [time](https://www.monitoring-plugins.org/doc/man/check_time.html) - monitoring-plugins
* [udp](https://www.monitoring-plugins.org/doc/man/check_udp.html) - monitoring-plugins
* [ups](https://www.monitoring-plugins.org/doc/man/check_ups.html) - monitoring-plugins
* [uptime](https://www.monitoring-plugins.org/doc/man/check_uptime.html) - monitoring-plugins
* [users](https://www.monitoring-plugins.org/doc/man/check_users.html) - monitoring-plugins
* [v46](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_v46) - pkg-nagios-plugins-contrib
* [wave](https://www.monitoring-plugins.org/doc/man/check_wave.html) - monitoring-plugins
* [webinject](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_webinject) - pkg-nagios-plugins-contrib
* [whois](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_whois) - pkg-nagios-plugins-contrib
* [zone_auth](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_zone_auth) - pkg-nagios-plugins-contrib
* [zone_rrsig_expiration](https://github.com/bzed/pkg-nagios-plugins-contrib/tree/master/check_zone_rrsig_expiration) - pkg-nagios-plugins-contrib

## Setup

> Service checks is now distributed aware. If you run a distributed setup then you can now run 
`services-wrapper.py` in cron instead of `check-services.php` across all polling nodes.

If you need to debug the output of services-wrapper.py then you can add `-d` to the end of the command - it is NOT recommended to do this in cron.

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
For example: 
```
chmod +x /usr/lib/nagios/plugins/*
```

Finally, you now need to add services-wrapper.py to the current cron file (/etc/cron.d/librenms typically) like:
```bash
*/5 * * * * librenms /opt/librenms/services-wrapper.py 1
```

Now you can add services via the main Services link in the navbar, or via the 'Add Service' link within the device, services page.

Note that some services (procs, inodes, load and similar) will always poll the local LibreNMS server it's running on, regardless of which device you add it to.

## Performance data

By default, the check-services script will collect all performance data that the Nagios script returns and display each datasource on a separate graph.
However for some modules it would be better if some of this information was consolidated on a single graph.
An example is the ICMP check. This check returns: Round Trip Average (rta), Round Trip Min (rtmin) and Round Trip Max (rtmax).
These have been combined onto a single graph.

If you find a check script that would benefit from having some datasources graphed together, please log an issue on GitHub with the debug information from the script, and let us know which DS's should go together. Example below:

    ./check-services.php -d
    -- snip --
    Nagios Service - 26
    Request:  /usr/lib/nagios/plugins/check_icmp localhost
    Perf Data - DS: rta, Value: 0.016, UOM: ms
    Perf Data - DS: pl, Value: 0, UOM: %
    Perf Data - DS: rtmax, Value: 0.044, UOM: ms
    Perf Data - DS: rtmin, Value: 0.009, UOM: ms
    Response: OK - localhost: rta 0.016ms, lost 0%
    Service DS: {
        "rta": "ms",
        "pl": "%",
        "rtmax": "ms",
        "rtmin": "ms"
    }
    OK u:0.00 s:0.00 r:40.67
    RRD[update /opt/librenms/rrd/localhost/services-26.rrd N:0.016:0:0.044:0.009]
    -- snip --

## Alerting

Services uses the Nagios Alerting scheme where:

	0 = Ok,
	1 = Warning,
	2 = Critical,

To create an alerting rule to alert on service=critical, your alerting rule would look like:

    %services.service_status = "2"
    
## Debug

Change user to librenms for example 
```
su - librenms
```
then you can run the following command to help troubleshoot services. 
```
./check-services.php -d
```
## Service checks polling logic

Service check is skipped when the associated device is not pingable, and an appropriate entry is populated in the event log. 
Service check is polled if it's `IP address` parameter is not equal to associated device's IP address, even when the associated device is not pingable.

To override the default logic and always poll service checks, you can disable ICMP testing for any device by switching `Disable ICMP Test` setting (Edit -> Misc) to ON.

Service checks will never be polled on disabled devices.
