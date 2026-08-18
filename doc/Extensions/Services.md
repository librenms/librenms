# Nagios Plugins - Services

Services in LibreNMS use Nagios plugins for monitoring outside SNMP.
You can also use services with your SNMP monitoring for a wider
coverage.


## Setting up Services

**A service needs a device. `localhost` is a generic option. We
recommend the monitored device instead.**

### Nagios plugins source

Plugins come from two main sources:

* [monitoring-plugins](https://www.monitoring-plugins.org)
* [pkg-nagios-plugins-contrib](https://github.com/bzed/pkg-nagios-plugins-contrib)

Note: a plugin loads only with the `check_` prefix. The "Type" dropdown
list of the "Add Service" page removes this prefix.

### Service Templates

Service templates in LibreNMS work in the same way as host groups in
Nagios. LibreNMS calls them device groups.
They apply to the devices of the selected device group.

The Apply buttons create and update the services of the service
template.
The Remove buttons remove the services of the service template.

After an edit of a service template, click Apply. LibreNMS then sends
the changes to the existing services.

Service Templates Auto Discovery adds, removes, and updates the
services at each discovery interval.

A device in several device groups gets the templates of all those
groups.

You add or remove a device in a device group. LibreNMS then adds or
removes the services at the next click of Apply or at the next auto
discovery run.

**A service template needs a device group. You need at least one device
group. A dummy group is valid. The device group needs no member.**

### Service Auto Discovery

This setting in `config.php` creates the services of the devices with
available checks automatically:

```php
$config['discover_services']           = true;
```

### Service Templates Auto Discovery

This setting in `config.php` creates the services of the devices with
service templates automatically:

```php
$config['discover_services_templates']           = true;
```

### Setup

A distributed setup can distribute the service checks. Use the
`dispatch` service. You can also replace `check-services.php` with
`services-wrapper.py` in cron. The checks then run on all polling
nodes.

To debug the output of `services-wrapper.py`, add `-d` to the end of
the command. Do NOT use this flag in cron.

First install the Nagios plugins.

Debian / Ubuntu: `sudo apt install monitoring-plugins`
Centos: `yum install nagios-plugins-all`

Note: the prebuilt VM images and Docker images hold the plugins.

Then enable the services in `config.php`:

```php
$config['show_services']           = 1;
```

A new service menu then appears in your navigation bar.

Debian/Ubuntu:
```php
$config['nagios_plugins']   = "/usr/lib/nagios/plugins";
```

Centos:
```php
$config['nagios_plugins']   = "/usr/lib64/nagios/plugins";
```

This setting gives LibreNMS the location of the Nagios plugins. Make
each plugin executable. For example:

Debian/Ubuntu:
```
chmod +x /usr/lib/nagios/plugins/*
```

Centos:
```
chmod +x /usr/lib64/nagios/plugins/*
```

Then add `services-wrapper.py` to your cron file, usually
`/etc/cron.d/librenms`:

```bash
*/5 * * * * librenms /opt/librenms/services-wrapper.py 1
```

You can now add services with the Services link in the navigation bar.
You can also use the 'Add Service' link on the services page of a
device.

Note: some services always poll the local LibreNMS server, at any
device. Examples are procs, inodes, and load.

### Performance data

By default, the `check-services` script collects all the performance
data of the Nagios script. It shows each datasource on a separate
graph. A script must return the response message in the Nagios
structure:
[AEN200](https://nagios-plugins.org/doc/guidelines.html#AEN200)

Some modules are clearer with this information on one graph.
The ICMP check is an example. It returns the round trip average (rta),
the round trip minimum (rtmin), and the round trip maximum (rtmax).
LibreNMS puts these values on one graph.

If a check script needs several datasources on one graph, open an issue
on GitHub. Add the debug information of the script and the list of the
datasources for each graph. For example:

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

### Alerting

Services uses the Nagios Alerting scheme where exit code:

```
    0 = Ok,
    1 = Warning,
    2 = Critical,
```

This alerting rule alerts on service=critical:

```
    %services.service_status = "2"
```

### Debug

Change user to librenms for example

```
su - librenms
```

Then run this command to troubleshoot the services.

```
./check-services.php -d
```

### Related Polling / Discovery Options

These settings are related. Examine them and set your own values. The
values below are not the defaults and are not recommendations.

!!! setting "poller/scheduledtasks"
    ```bash
    lnms config:set schedule_type.services dispatcher
    ```

!!! setting "poller/dispatcherservice"
    ```bash
    lnms config:set service_services_workers 16
    lnms config:set service_discovery_workers 300
    ```
Also read [Dispatcher Service](../Extensions/Dispatcher-Service.md).

### Service checks polling logic

LibreNMS skips a service check when the device does not answer a ping.
It writes an entry in the event log. LibreNMS polls the service check
when its `IP address` parameter differs from the IP address of the
device. This behaviour also applies to a device without a ping
response.

To poll the service checks always, disable the ICMP test of the device.
Set `Disable ICMP Test` to ON under Edit -> Misc.

LibreNMS never polls the service checks of a disabled device.

### CHECK_MRPE

Usually, only the Nagios plugins with the `-H` option for a remote host
are available as services. If your remote host runs the [Check_MK
agent](Agent-Setup.md), MRPE can monitor the local-only Nagios plugins
as services.

The common `check_cpu.sh` Nagios plugin is an example. Add this line:

> cpu_check /usr/lib/nagios/plugins/check_cpu.sh -c 95 -w 75

Add it to `/etc/check_mk/mrpe.cfg` on your remote host. You can then
read its output with a service that uses the
[check_mrpe](https://raw.githubusercontent.com/librenms/librenms-agent/master/agent-local/check_mrpe)
script.

 - Add [check_mrpe](https://raw.githubusercontent.com/librenms/librenms-agent/master/agent-local/check_mrpe) to the Nagios plugins directory on your LibreNMS server and make it executable.
- In LibreNMS, add a new service to the desired device with the type mrpe.
- Enter the IP address of the remote host. In the parameters, enter
  `-a cpu_check`. This value must match the name at the start of the
  line in the `mrpe.cfg` file.
