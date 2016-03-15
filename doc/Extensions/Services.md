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

Now you can add services via the main Services link in the navbar, or via the 'Add Service' link within the device, services page.

## Performance data

By default, the poller module will collect all performance data that the check script returns and display each datasource on a separate graph.
However for some modules it would be better if some of this information was consolidated on a single graph.
An example is the ICMP check. This check returns: Round Trip Average (rta), Round Trip Min (rtmin) and Round Trip Max (rtmax).
These have been combined onto a single graph.

If you find a check script that would benefit from having some datasources graphed together, please log an issue on GitHub with the debug information from the poller, and let us know which DS's should go together. Example below:

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
