
# Sagan

For metrics the stats are migrated as below from the stats JSON.

`f_drop_percent` and `drop_percent` are computed based on the found data.

| Instance Key       | Stats JSON Key                     |
|--------------------|------------------------------------|
| uptime             | .stats.uptime                      |
| total              | .stats.captured.total              |
| drop               | .stats.captured.drop               |
| ignore             | .stats.captured.ignore             |
| threshold          | .stats.captured.theshold           |
| after              | .stats.captured.after              |
| match              | .stats.captured.match              |
| bytes              | .stats.captured.bytes_total        |
| bytes_ignored      | .stats.captured.bytes_ignored      |
| max_bytes_log_line | .stats.captured.max_bytes_log_line |
| eps                | .stats.captured.eps                |
| f_total            | .stats.flow.total                  |
| f_dropped          | .stats.flow.dropped                |

Those keys are appended with the name of the instance running with `_`
between the instance name and instance metric key. So `uptime` for
`ids` would be `ids_uptime`.

The default is named 'ids' unless otherwise specified via the extend.

There is a special instance name of `.total` which is the total of all
the instances. So if you want the total eps, the metric would be
`.total_eps`. Also worth noting that the alert value is the highest
one found among all the instances.

## SNMP Extend

1. Install prerequisites

=== "Debian/Ubuntu"

    ```bash
    apt-get install libjson-perl libfile-readbackwards-perl libfile-slurp-perl libmime-base64-perl cpanminus
    cpanm Sagan::Monitoring
    ```

=== "FreeBSD"

    ```bash
    pkg install p5-JSON p5-File-ReadBackwards p5-File-Slurp p5-MIME-Base64 p5-Time-Piece p5-App-cpanminus
    cpanm Sagan::Monitoring
    ```

=== "Generic"

    ```bash
    cpanm Sagan::Monitoring
    ```


1. Setup cron. Below is a example.

    ```bash
    */5 * * * * /usr/local/bin/sagan_stat_check > /dev/null
    ```

3. Configure snmpd.conf

    ```bash
    extend sagan-stats /usr/bin/env PATH=/sbin:/bin:/usr/sbin:/usr/bin:/usr/local/sbin:/usr/local/bin sagan_stat_check -c
    ```

4. Restart snmpd on your system.

You will want to make sure that sagan is setup to with the values set
below for stats-json processor, for a single instance setup..

```
enabled: yes
time: 300
subtract_old_values: true
filename: "$LOG_PATH/stats.json"
```

Any configuration of sagan_stat_check should be done in the cron
setup. If the default does not work, check the docs for it at
[MetaCPAN for sagan_stat_check](https://metacpan.org/dist/Sagan-Monitoring/view/bin/sagan_stat_check)

