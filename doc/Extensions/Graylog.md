# Graylog integration

LibreNMS has a simple integration for Graylog. You can see any
logs in LibreNMS. The syslog input of Graylog parses these logs
from within Graylog itself. This includes logs from devices which
are not in LibreNMS. You can also see the logs of a specific device
under the logs section for the device.

Currently, LibreNMS does not associate shortnames from Graylog with
full FQDNS. If you have your devices in LibreNMS using full FQDNs,
such as hostname.example.com, be aware that rsyslogd, by default,
sends the shortname only. To fix this, add

`$PreserveFQDN on`

to your rsyslog config. It sends the full FQDN, so the device logs are
associated correctly in LibreNMS. Also see near the bottom of this
document for tips on how to enable/suppress the domain part of
hostnames in syslog-messages for some platforms.

LibreNMS does not include Graylog. You must
install this separately either on the same infrastructure as LibreNMS
or as a totally standalone appliance.

The configuration is simple. This example uses Graylog 2.4:

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
To connect to Graylog without an admin account,
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
trusted by your LibreNMS host. Without this trust, the connection
fails. The Common Name (CN) of the certificate must also match the
FQDN or the IP address in

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

You can set the log levels of the overview page. (default: 7, min:
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
 (applies to  /graylog and /device/<device_id>/logs/graylog/ (min: 0, max: 7)

## Domain and hostname handling

Suppressing/enabling the domain part of a hostname for specific platforms

Compare the output of syslog and Graylog to your
configured hosts first. If you need to modify the syslog messages from
specific platforms, these notes help:

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


