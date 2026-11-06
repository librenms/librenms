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

Metric names are limited to 64 characters. Any metric whose name would
be longer than that is skipped, which may happen with long proxy or
server names. The counts metrics are always present and can be used
for alerting regardless.

For counts, type is one of `fe`, `be`, `srv`, or `li` and state is
one of `total`, `up`, `down`, `maint`, `drain`, `nolb`, or `other`.
`up` includes `UP`, `OPEN`, and `no check`.

Backends and servers also have a `lastsess` metric, the number of
seconds since the last session, with `-1` meaning never.

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
