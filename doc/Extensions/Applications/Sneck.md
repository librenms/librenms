# Sneck

This is for replacing Nagios/Icinga or the LibreNMS service
integration in regards to NRPE. This allows LibreNMS to query what
checks were ran on the server and keep track of totals of OK, WARNING,
CRITICAL, and UNKNOWN statuses.

The big advantage over this compared to a NRPE are as below.

- It does not need to know what checks are configured on it.
- Also does not need to wait for the tests to run as sneck is meant to
  be ran via cron and the then return the cache when queried via SNMP,
  meaning a lot faster response time, especially if slow checks are
  being performed.
- Works over proxied SNMP connections.

Included are alert examples. Although for setting up custom ones, the
metrics below are provided.

| Metric              | Description                                                                                                           |
|---------------------|-----------------------------------------------------------------------------------------------------------------------|
| ok                  | Total OK checks                                                                                                       |
| warning             | Total WARNING checks                                                                                                  |
| critical            | Total CRITICAL checks                                                                                                 |
| unknown             | Total UNKNOWN checks                                                                                                  |
| errored             | Total checks that errored                                                                                             |
| time_to_polling     | Differnce in seconds between when polling data was generated and when polled                                          |
| time_to_polling_abs | The absolute value of time_to_polling.                                                                                |
| check_$CHECK        | Exit status of a specific check `$CHECK` is equal to the name of the check in question. So `foo` would be `check_foo` |

The standard Nagios/Icinga style exit codes are used and those are as
below.

| Exit | Meaning  |
|------|----------|
| 0    | okay     |
| 1    | warning  |
| 2    | critical |
| 3+   | unknown  |

To use `time_to_polling`, it will need to enabled via setting the
config item below. The default is false. Unless set to true, this
value will default to 0. If enabling this, one will want to make sure
that NTP is in use every were or it will alert if it goes over a
difference of 540s.

```
lnms config:set app.sneck.polling_time_diff true
```

For more information on Sneck, check it out at
[MetaCPAN](https://metacpan.org/dist/Monitoring-Sneck) or
[Github](https://github.com/VVelox/Monitoring-Sneck).

For poking systems using Sneck, also check out boop_snoot
if one wants to query those systems via the CLI. Docs on it
at [MetaCPAN](https://metacpan.org/dist/Monitoring-Sneck-Boop_Snoot) and
[Github](https://github.com/VVelox/Monitoring-Sneck-Boop_Snoot).

## Install prerequisites

=== "Debian/Ubuntu"

    ```bash
    apt-get install cpanminus libjson-perl libfile-slurp-perl libmime-base64-perl
    cpanm Monitoring::Sneck
    ```

=== "FreeBSD"

    ```bash
    pkg install p5-JSON p5-File-Slurp p5-MIME-Base64 p5-App-cpanminus
    cpanm Monitoring::Sneck
    ```

=== "Generic"

    ```bash
    cpanm Monitoring::Sneck
    ```

## SNMP Extend

2. Configure any of the checks you want to run in
   `/usr/local/etc/sneck.conf`. You con find it documented
   [here](https://metacpan.org/pod/Monitoring::Sneck#CONFIG-FORMAT).

3. Set it up in cron. This will mean you don't need to wait for all
   the checks to complete when polled via SNMP, which for like SMART
   or other long running checks will mean it timing out. Also means it
   does not need called via sudo as well.

    ```bash
    */5 * * * * /usr/bin/env PATH=/sbin:/bin:/usr/sbin:/usr/bin:/usr/local/sbin:/usr/local/bin /usr/local/bin/sneck -u 2> /dev/null > /dev/null
    ```

4. Set it up in the snmpd config and restart snmpd. The `-c` flag will
   tell read it to read from cache instead of rerunning the checks.

    ```bash
    extend sneck /usr/bin/env PATH=/sbin:/bin:/usr/sbin:/usr/bin:/usr/local/sbin:/usr/local/bin /usr/local/bin/sneck -c
    ```

5. In LibreNMS, enable the application for the server in question or wait for auto discovery to find it.
