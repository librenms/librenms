# HAProxy

Monitors HAProxy via the CSV stats page. Stats are collected for each
frontend, backend, server, and listener.

### Install prerequisites

=== "Debian/Ubuntu"

    ```bash
    apt-get install libjson-perl libmime-base64-perl libfile-slurp-perl libwww-perl liblwp-protocol-https-perl libtext-csv-perl
    ```

=== "FreeBSD"

    ```bash
    pkg install p5-JSON p5-MIME-Base64 p5-File-Slurp p5-libwww p5-LWP-Protocol-https p5-Text-CSV
    ```

=== "RedHat/CentOS"

    ```bash
    dnf install perl-JSON perl-MIME-Base64 perl-File-Slurp perl-libwww perl-LWP-Protocol-https perl-Text-CSV
    ```

=== "Generic"

    ```bash
    cpanm JSON MIME::Base64 File::Slurp LWP::UserAgent LWP::Protocol::https Text::CSV
    ```

### HAProxy Config

The CSV stats page needs to be enabled. Something like below will work
with the default URL used by the extend, `http://127.0.0.1/stats;csv`.

```
frontend stats
    bind 127.0.0.1:80
    mode http
    stats enable
    stats uri /stats
    stats refresh 10s
```

If HTTP basic auth is needed, the creds may be included in the URL
passed via `-u`, such as `http://user:pass@127.0.0.1/stats;csv`.

### SNMP Extend

1.  Copy the script to the desired host.

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/haproxy -O /etc/snmp/extends/haproxy
    ```

2.  Make the script executable

    ```bash
    chmod +x /etc/snmp/extends/haproxy
    ```

3.  Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

    ```
    extend haproxy /etc/snmp/extends/haproxy -b
    ```

    Or if running it via cron...

    ```
    # cron
    */5 * * * * root /etc/snmp/extends/haproxy -b -q -o /var/cache/haproxy.json.snmp

    # snmpd.conf
    extend haproxy /bin/cat /var/cache/haproxy.json.snmp
    ```

4.  Restart snmpd on your host

    The application should be auto-discovered as described at the top of
    the page. If it is not, please follow the steps set out under `SNMP
    Extend` heading top of page.

### Status Codes

The status for each item is graphed and saved as a metric as a number.
Transitional states, such as `UP 1/3` or `DOWN 1/2`, are treated as
the state before the space.

| Code | Status   |
|------|----------|
| 0    | DOWN     |
| 1    | UP, OPEN |
| 2    | NOLB     |
| 3    | MAINT    |
| 4    | DRAIN    |
| 5    | no check |
| 6    | FULL     |
| 7    | STOP     |
| 8    | PAUSED   |
| 9    | WAITING  |
| -1   | other    |

Any status not listed is saved as -1, so the status metric is always
present for alerting.

### Metrics

The metrics are named as below.

| Item     | Metric                               |
|----------|--------------------------------------|
| Frontend | `fe_<proxy>_<field>`                 |
| Backend  | `be_<proxy>_<field>`                 |
| Server   | `srv_<backend>_-_<server>_<field>`   |
| Listener | `li_<frontend>_-_<listener>_<field>` |
| Totals   | `totals_<field>`                     |
| Counts   | `count_<type>_<state>`               |

The parts in angle brackets are as below.

- proxy :: The name of the frontend or backend, as in the HAProxy config.
- backend :: The name of the backend a server belongs to.
- server :: The name of the server, as on its `server` line.
- frontend :: The name of the frontend a listener belongs to.
- listener :: The name of the listener. Listeners are only reported
  by HAProxy if `option socket-stats` is set.
- field :: One of the fields listed further down, such as `status`
  or `scur`.
- type :: `fe` for frontends, `be` for backends, `srv` for servers,
  or `li` for listeners.
- state :: The state being counted, see below.

For example, the status of the server `DMZ_Dotdot_Public` in the
backend `dotdot` is `srv_dotdot_-_DMZ_Dotdot_Public_status`.

Metric names are limited to 64 characters. Any metric whose name would
be longer than that is skipped, which may happen with long proxy or
server names. An alert rule using a skipped metric will never match,
and so will never fire. The counts metrics are always present and can
be used for alerting regardless.

For the status metric to fit, the names need to be within the lengths
below. Fields with longer names than `status` leave less room.

- Frontend or backend :: 54 characters.
- Server :: 50 characters for the backend and server names combined.
- Listener :: 51 characters for the frontend and listener names combined.

The counts are how many items of a type are in a given state. For
example, `count_srv_down` is the number of servers that are down.
The states are as below.

- total :: All items of that type.
- up :: Status of `UP`, `OPEN`, or `no check`.
- down :: Status of `DOWN`.
- maint :: Status of `MAINT`.
- drain :: Status of `DRAIN`.
- nolb :: Status of `NOLB`.
- other :: Any other status, such as `FULL`, `STOP`, `PAUSED`, or
  `WAITING`.

The totals are the values summed across all frontends, or across all
backends for fields only backends have.

The totals have a metric for every field they graph. Frontends, backends,
servers, and listeners only have the ones below, with the rest only
being graphed.

- status :: The status code, see above.
- scur :: Current sessions.
- slim :: Session limit.
- rate :: Sessions per second.
- qcur :: Requests currently queued. Backends and servers only.
- act :: Active servers that are up. Backends and servers only.
- bck :: Backup servers that are up. Backends and servers only.
- weight :: The load balancing weight. Backends and servers only.
- lastsess :: Seconds since the last session, with `-1` meaning
  never. Backends and servers only.
- qtime :: Average queue time in ms. Backends and servers only.
- ctime :: Average connect time in ms. Backends and servers only.
- rtime :: Average response time in ms. Backends and servers only.
- ttime :: Average total session time in ms. Backends and servers only.
- check_health :: The health check counter. Servers only.

### Alerting Examples

Any server down.

```
applications.app_type = "haproxy" AND application_metrics.metric = "count_srv_down" AND application_metrics.value > 0
```

Any backend down.

```
applications.app_type = "haproxy" AND application_metrics.metric = "count_be_down" AND application_metrics.value > 0
```

A specific server being anything other than up.

```
applications.app_type = "haproxy" AND application_metrics.metric = "srv_dotdot_-_DMZ_Dotdot_Public_status" AND application_metrics.value != 1
```

This only works if the metric name fits in 64 characters, see
Metrics above. If it does not, use `count_srv_down` instead.
