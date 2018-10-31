source: Extensions/Services.md
path: blob/master/doc/
[TOC]

# Setting up services

Services within LibreNMS provides the ability to use Nagios plugins to perform additional monitoring outside of SNMP.

**These services are tied into an existing device so you need at least one device to be able to add it
to LibreNMS - localhost is a good one. This is needed in order for alerting to work properly.**

## Pre installed plugins

List of pre installed plugins in the format: \<GUI name\> - \<documentation URL\>

Many of these are from [monitoring-plugins.org](https://www.monitoring-plugins.org/doc/man/index.html). 

Plugins will only load if they are prefixed with "check_" and they have that stripped out when displaying in the "Add Serice" GUI "Type" dropdown list.

* ajp -
* apt - https://www.monitoring-plugins.org/doc/man/check_apt.html
* backuppc -
* bgpstate -
* breeze - https://www.monitoring-plugins.org/doc/man/check_breeze.html
* by_ssh - https://www.monitoring-plugins.org/doc/man/check_by_ssh.html
* cert_expire -
* checksums -
* clamav -
* clamd - https://www.monitoring-plugins.org/doc/man/check_clamd.html
* cluster - https://www.monitoring-plugins.org/doc/man/check_cluster.html
* cups -
* dbi - https://www.monitoring-plugins.org/doc/man/check_dbi.html
* dhcp - https://www.monitoring-plugins.org/doc/man/check_dhcp.html
* dig - https://www.monitoring-plugins.org/doc/man/check_dig.html
* disk - https://www.monitoring-plugins.org/doc/man/check_disk.html
* disk_smb - https://www.monitoring-plugins.org/doc/man/check_disk_smb.html
* dns - https://www.monitoring-plugins.org/doc/man/check_dns.html
* dnssec_delegation -
* drbd -
* dummy - https://www.monitoring-plugins.org/doc/man/check_dummy.html
* email_delivery -
* email_delivery_epn -
* entropy -
* etc_hosts -
* etc_resolv -
* file_age - https://www.monitoring-plugins.org/doc/man/check_file_age.html
* flexlm - https://www.monitoring-plugins.org/doc/man/check_flexlm.html
* fping - https://www.monitoring-plugins.org/doc/man/check_fping.html
* ftp -
* game - https://www.monitoring-plugins.org/doc/man/check_game.html
* graphite -
* haproxy -
* haproxy_stats -
* host -
* hp_bladechassis -
* hpasm -
* hpjd - https://www.monitoring-plugins.org/doc/man/check_hpjd.html
* http - [check_http docs](https://www.monitoring-plugins.org/doc/man/check_http.html)
* httpd_status -
* icmp - https://www.monitoring-plugins.org/doc/man/check_icmp.html
* ide_smart -
* ifoperstatus -
* ifstatus -
* imap -
* imap_quota -
* imap_quota_epn -
* imap_receive -
* imap_receive_epn -
* ipmi_sensor -
* ircd - https://www.monitoring-plugins.org/doc/man/check_ircd.html
* jabber - https://www.monitoring-plugins.org/doc/man/check_jabber.html
* ldap - https://www.monitoring-plugins.org/doc/man/check_ldap.html
* ldap_root -
* ldaps - https://www.monitoring-plugins.org/doc/man/check_ldaps.html
* libs -
* libvirt -
* lm_sensors -
* load - https://www.monitoring-plugins.org/doc/man/check_load.html
* log - https://www.monitoring-plugins.org/doc/man/check_log.html
* mailq - https://www.monitoring-plugins.org/doc/man/check_mailq.html
* memcached -
* memory -
* mongodb -
* mrtg - https://www.monitoring-plugins.org/doc/man/check_mrtg.html
* mrtgtraf - https://www.monitoring-plugins.org/doc/man/check_mrtgtraf.html
* multipath -
* mysql - https://www.monitoring-plugins.org/doc/man/check_mysql.html
* mysql_health -
* mysql_query - https://www.monitoring-plugins.org/doc/man/check_mysql_query.html
* nagios - https://www.monitoring-plugins.org/doc/man/check_nagios.html
* nfsmounts -
* nntp - https://www.monitoring-plugins.org/doc/man/check_nntp.html
* nntps - https://www.monitoring-plugins.org/doc/man/check_nntps.html
* nt - https://www.monitoring-plugins.org/doc/man/check_nt.html
* ntp - https://www.monitoring-plugins.org/doc/man/check_ntp.html
* ntp_peer - https://www.monitoring-plugins.org/doc/man/check_ntp_peer.html
* ntp_time - https://www.monitoring-plugins.org/doc/man/check_ntp_time.html
* nwstat - https://www.monitoring-plugins.org/doc/man/check_nwstat.html
* oracle - https://www.monitoring-plugins.org/doc/man/check_oracle.html
* overcr - https://www.monitoring-plugins.org/doc/man/check_overcr.html
* packages -
* pgsql - https://www.monitoring-plugins.org/doc/man/check_pgsql.html
* ping - https://www.monitoring-plugins.org/doc/man/check_ping.html
* pop - https://www.monitoring-plugins.org/doc/man/check_pop.html
* printer -
* procs - https://www.monitoring-plugins.org/doc/man/check_procs.html
* raid -
* rbl -
* real - https://www.monitoring-plugins.org/doc/man/check_real.html
* redis -
* rpc - https://www.monitoring-plugins.org/doc/man/check_rpc.html
* rta_multi -
* running_kernel -
* sensors - https://www.monitoring-plugins.org/doc/man/check_sensors.html
* shutdown -
* simap - https://www.monitoring-plugins.org/doc/man/check_simap.html
* smstools -
* smtp - https://www.monitoring-plugins.org/doc/man/check_smtp.html
* smtp_send -
* smtp_send_epn -
* snmp - https://www.monitoring-plugins.org/doc/man/check_snmp.html
* snmp_environment -
* snmp_time -
* soas -
* spop - https://www.monitoring-plugins.org/doc/man/check_spop.html
* ssh - https://www.monitoring-plugins.org/doc/man/check_ssh.html
* ssl_cert -
* ssmtp - https://www.monitoring-plugins.org/doc/man/check_ssmtp.html
* statusfile -
* swap - https://www.monitoring-plugins.org/doc/man/check_swap.html
* tcp - https://www.monitoring-plugins.org/doc/man/check_tcp.html
* time - https://www.monitoring-plugins.org/doc/man/check_time.html
* udp - https://www.monitoring-plugins.org/doc/man/check_udp.html
* ups - https://www.monitoring-plugins.org/doc/man/check_ups.html
* uptime - https://www.monitoring-plugins.org/doc/man/check_uptime.html
* users - https://www.monitoring-plugins.org/doc/man/check_wave.html
* v46 -
* wave - https://www.monitoring-plugins.org/doc/man/check_wave.html
* webinject -
* whois -
* zone_auth -
* zone_rrsig_expiration -

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
