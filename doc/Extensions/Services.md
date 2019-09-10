source: Extensions/Services.md
path: blob/master/doc/
[TOC]

# Setting up services

Services within LibreNMS provides the ability to use Nagios plugins to
perform additional monitoring outside of SNMP.

**These services are tied into an existing device so you need at least
one device to be able to add it to LibreNMS - localhost is a good
one. This is needed in order for alerting to work properly.**

## Pre installed plugins

Note: Plugins will only load if they are prefixed with "check_" and
they have that prefix stripped out when displaying in the "Add Serice"
GUI "Type" dropdown list.

Plugins come from two main places
[pkg-nagios-plugins-contrib](https://github.com/bzed/pkg-nagios-plugins-contrib)
and [monitoring-plugins](https://www.monitoring-plugins.org). This is
where you can find the documentation for most, if not all of the plugins.

The plugins are bundled with the pre build VM images via the package
`monitoring-plugins` in Ubuntu and `nagios-plugins-all` in CentOS.

## Setup

Service checks is now distributed aware. If you run a distributed
setup then you can now run `services-wrapper.py` in cron instead of
`check-services.php` across all polling nodes.

If you need to debug the output of services-wrapper.py then you can
add `-d` to the end of the command - it is NOT recommended to do this
in cron.

Firstly, install Nagios plugins however you would like, this could be
via yum, apt-get or direct from source.

Next, you need to enable the services within config.php with the following:

```php
$config['show_services']           = 1;
```

This will enable a new service menu within your navbar.

```php
$config['nagios_plugins']   = "/usr/lib/nagios/plugins";
```

This will point LibreNMS at the location of the nagios plugins -
please ensure that any plugins you use are set to executable. For example:

```
chmod +x /usr/lib/nagios/plugins/*
```

Finally, you now need to add services-wrapper.py to the current cron
file (/etc/cron.d/librenms typically) like:

```bash
*/5 * * * * librenms /opt/librenms/services-wrapper.py 1
```

Now you can add services via the main Services link in the navbar, or
via the 'Add Service' link within the device, services page.

Note that some services (procs, inodes, load and similar) will always
poll the local LibreNMS server it's running on, regardless of which
device you add it to.

## Performance data

By default, the check-services script will collect all performance
data that the Nagios script returns and display each datasource on a
separate graph. LibreNMS expects scripts to return using Nagios
convention for the response message structure:
[AEN200](https://nagios-plugins.org/doc/guidelines.html#AEN200)

However for some modules it would be better if some of this
information was consolidated on a single graph.
An example is the ICMP check. This check returns: Round Trip Average
(rta), Round Trip Min (rtmin) and Round Trip Max (rtmax).
These have been combined onto a single graph.

If you find a check script that would benefit from having some
datasources graphed together, please log an issue on GitHub with the
debug information from the script, and let us know which DS's should
go together. Example below:

```
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
```

## Alerting

Services uses the Nagios Alerting scheme where:

```
    0 = Ok,
    1 = Warning,
    2 = Critical,
```

To create an alerting rule to alert on service=critical, your alerting
rule would look like:

```
    %services.service_status = "2"
```

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

Service check is skipped when the associated device is not pingable,
and an appropriate entry is populated in the event log. Service check
is polled if it's `IP address` parameter is not equal to associated
device's IP address, even when the associated device is not pingable.

To override the default logic and always poll service checks, you can
disable ICMP testing for any device by switching `Disable ICMP Test`
setting (Edit -> Misc) to ON.

Service checks will never be polled on disabled devices.
