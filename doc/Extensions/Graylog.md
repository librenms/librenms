# Graylog integration

We have simple integration for Graylog, you will be able to view any
logs from within LibreNMS that have been parsed by the syslog input
from within Graylog itself. This includes logs from devices which
aren't in LibreNMS still, you can also see logs for a specific device
under the logs section for the device.

Currently, LibreNMS does not associate shortnames from Graylog with
full FQDNS. If you have your devices in LibreNMS using full FQDNs,
such as hostname.example.com, be aware that rsyslogd, by default,
sends the shortname only. To fix this, add

`$PreserveFQDN on`

to your rsyslog config to send the full FQDN so device logs will be
associated correctly in LibreNMS. Also see near the bottom of this
document for tips on how to enable/suppress the domain part of
hostnames in syslog-messages for some platforms.

Graylog itself isn't included within LibreNMS, you will need to
install this separately either on the same infrastructure as LibreNMS
or as a totally standalone appliance.

Config is simple, here's an example based on Graylog 2.4:

!!! setting "external/graylog"
    ```bash
    lnms config:set graylog.server 'http://127.0.0.1'
    lnms config:set graylog.port 9000
    lnms config:set graylog.username admin
    lnms config:set graylog.password 'admin'
    lnms config:set graylog.version 2.4
    ```

## Timezone
Graylog messages are stored using GMT timezone. You can display
graylog messages in LibreNMS webui using your desired timezone by
setting the following option using `lnms config:set`:

!!! setting "external/graylog"
    ```bash
    lnms config:set graylog.timezone 'Europe/Bucharest'
    ```

Timezone must be PHP supported timezones, available at:
<https://php.net/manual/en/timezones.php>

## Graylog Version
If you are running a version earlier than Graylog then please set

!!! setting "external/graylog"
    ```bash
    lnms config:set graylog.version 2.1
    ```

to the version  number of your Graylog
install. Earlier versions than 2.1 use the default port `12900`

## URI
If you have altered the default uri for your Graylog setup then you
can override the default of `/api/` using

!!! setting "external/graylog"
    ```bash
    lnms config:set graylog.base_uri '/somepath/'
    ```

## User Credentials
If you don't want to use an admin account for connection to Graylog
Log into http://<graylog-server-ip>/api/api-browser/global/index.html using graylog admin credentials
Browse to: Roles: User roles
Click on: Create a new role
In JSON body paste this:

```
{
	"name": "LibreNMS-Read",
	"description": "Extended reading permissions for LibreNMS",
	"permissions" : [
		"searches:relative",
		"streams:read"
	]
}
```
Press “Try it out”
Log into graylog web ui as admin and add the role to the user

Otherwise you must give the user "admin" permissions from within
Graylog, "read" permissions alone are not sufficient.


## TLS Certificate
If you have enabled TLS for the Graylog API and you are using a
self-signed certificate, please make sure that the certificate is
trusted by your LibreNMS host, otherwise the connection will
fail. Additionally, the certificate's Common Name (CN) has to match
the FQDN or IP address specified in

!!! setting "external/graylog"
    ```bash
    lnms config:set graylog.server example.com
    ```

## Match Any Address
If you want to match the source address of the log entries against any
IP address of a device instead of only against the primary address and
the host name to assign the log entries to a device, you can activate
this function using

```bash
lnms config:set graylog.match-any-address true
```

## Recent Devices
There are 2 configuration parameters to influence the behaviour of the
"Recent Graylog" table on the overview page of the
devices.

!!! setting "external/graylog"
    ```bash
    lnms config:set graylog.device-page.rowCount 10
    ```

Sets the maximum number of rows to be displayed (default: 10)

!!! setting "external/graylog"
    ```bash
    lnms config:set graylog.device-page.loglevel 7
    ```

You can set which loglevels that should be displayed on the overview page. (default: 7, min:
0, max: 7)

!!! setting "external/graylog"
    ```bash
    lnms config:set graylog.device-page.loglevel 4
    ```

Shows only entries with a log level less than or equal to 4 (Emergency,
Alert, Critical, Error, Warning).

You can set a default Log Level Filter with
```bash
lnms config:set graylog.loglevel 7
```
 (applies to  /graylog and /device/device=/tab=logs/section=graylog/ (min: 0, max: 7)

## Domain and hostname handling

Suppressing/enabling the domain part of a hostname for specific platforms

You should see if what you get in syslog/Graylog matches up with your
configured hosts first. If you need to modify the syslog messages from
specific platforms, this may be of assistance:

### IOS (Cisco)

```
router(config)# logging origin-id hostname
```

or

```
router(config)# logging origin-id string
```

### JunOS (Juniper Networks)

```
set system syslog host yourlogserver.corp log-prefix YOUR_PREFERRED_STRING
```

### PanOS (Palo Alto Networks)

```
set deviceconfig setting management hostname-type-in-syslog hostname
```

or

```
set deviceconfig setting management hostname-type-in-syslog FQDN
```


