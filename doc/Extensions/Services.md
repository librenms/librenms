source: Extensions/Services.md
path: blob/master/doc/
[TOC]

# Setting up services

Services within LibreNMS provides the ability to leverage Nagios plugins to
perform additional monitoring outside of SNMP. Services can also be used
in conjunction with your SNMP monitoring for larger monitoring functionality.

**Services must be tied to a device to function properly. A good generic
option is to use `localhost`, but it is suggested to attach the check to
the device you are monitoring.**

## Nagios plugins source

Plugins come from two main sources:

* [monitoring-plugins](https://www.monitoring-plugins.org)
* [pkg-nagios-plugins-contrib](https://github.com/bzed/pkg-nagios-plugins-contrib)

Note: Plugins will only load if they are prefixed with `check_`.
The `check_` prefix is stripped out when displaying in the "Add Service"
GUI "Type" dropdown list.

## Service Templates

Service Templates within LibreNMS provides the same ability as Nagios
does with Host Groups. Known as Device Groups in LibreNMS.
They are applied devices that belong to the specified Device Group.

Use the Apply buttons to manually create or update Services for the Service
Template.
Use the Remove buttons to manually remove Services for the Service Template.

After you Edit a Service Template, and then use Apply, all relevant changes are
pushed to existing Services previously created.

You can also enable Service Templates Auto Discovery to have Services
added / removed / updated on regular discover intervals.

When a Device is a member of multiple Device Groups, templates from
all of those Device Groups are applied.

If a Device is added or removed from a Device Group, when the Apply button
is used or Auto Discovery runs Services will be added / removed as
appropriate.

**Service Templates are tied into Device Groups, you need at least
one Device Group to be able to add Service Templates - You can define a
dummy one. The Device Group does not need members to add Service Templates.**

## Service Auto Discovery

To automatically create services for devices with available checks.

You need to enable the discover services within config.php with the following:

```php
$config['discover_services']           = true;
```

## Service Templates Auto Discovery

To automatically create services for devices with configured
Service Templates.

You need to enable the discover services within config.php with the following:

```php
$config['discover_services_templates']           = true;
```

## Setup

Service checks are now distributable if you run a distributed
setup. To leverage this, use the `dispatch` service. Alternatively,
you could also replace `check-services.php` with `services-wrapper.py` in
cron instead to run across all polling nodes.

If you need to debug the output of services-wrapper.py then you can
add `-d` to the end of the command - it is NOT recommended to do this
in cron.

Firstly, install Nagios plugins.

Debian / Ubuntu: `sudo apt install monitoring-plugins`
Centos: `yum install nagios-plugins-all`

Note: The plugins are bundled with the pre-build VM and Docker images.

Next, you need to enable the services within config.php with the following:

```php
$config['show_services']           = 1;
```

This will enable a new service menu within your navbar.

Debian/Ubuntu:
```php
$config['nagios_plugins']   = "/usr/lib/nagios/plugins";
```

Centos:
```php
$config['nagios_plugins']   = "/usr/lib64/nagios/plugins";
```

This will point LibreNMS at the location of the nagios plugins -
please ensure that any plugins you use are set to executable. For example:

Debian/Ubuntu:
```
chmod +x /usr/lib/nagios/plugins/*
```

Centos:
```
chmod +x /usr/lib64/nagios/plugins/*
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

Services uses the Nagios Alerting scheme where exit code:

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

## Related Polling / Discovery Options

These settings are related and should be investigated and set accordingly.
The below values are not defaults or recommended.

```php
$config['service_poller_enabled']           = true;
```
```php
$config['service_poller_workers']           = 16;
```
```php
$config['service_poller_frequency']         = 300;
```
```php
$config['service_poller_down_retry']        = 5;
```
```php
$config['service_discovery_enabled']        = true;
```
```php
$config['service_discovery_workers']        = 16;
```
```php
$config['service_discovery_frequency']      = 3600;
```
```php
$config['service_services_enabled']         = true;
```
```php
$config['service_services_workers']         = 16;
```
```php
$config['service_services_frequency']       = 60;
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

## CHECK_MRPE

In most cases, only Nagios plugins that run against a remote host with the -H option are available as services.  However, if you're remote host is running the [Check_MK agent](Agent-Setup.md) you may be able to use MRPE to monitor Nagios plugins that only execute locally as services.

For example, consider the fairly common check_cpu.sh Nagios plugin.
If you added..

> cpu_check /usr/lib/nagios/plugins/check_cpu.sh -c 95 -w 75

...to `/etc/check_mk/mrpe.cfg` on your remote host, you should be able to check its output by configuring a service using the [check_mrpe](https://raw.githubusercontent.com/librenms/librenms-agent/master/agent-local/check_mrpe) script.

 - Add [check_mrpe](https://raw.githubusercontent.com/librenms/librenms-agent/master/agent-local/check_mrpe) to the Nagios plugins directory on your LibreNMS server and make it executable.
- In LibreNMS, add a new service to the desired device with the type mrpe.
- Enter the IP address of the remote host and in parameters enter `-a cpu_check` (this should match the name used at the beginning of the line in the mrpe.cfg file).
