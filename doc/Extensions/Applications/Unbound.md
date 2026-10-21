# Unbound

Unbound configuration:

```text
# Enable extended statistics.
server:
        extended-statistics: yes
        statistics-cumulative: yes

remote-control:
        control-enable: yes
        control-interface: 127.0.0.1

```

Restart your unbound after changing the configuration, verify it is
working by running `unbound-control stats`.

The counters (queries, cache hits and misses, opcode and rcode counts,
request-list counters) are stored as `DERIVE`, so the graphs show rates
whether or not `statistics-cumulative` is enabled, and the SNMP extend reads
them with `unbound-control stats_noreset`, so it never resets counters that
other tools may rely on. If you upgrade from a version that stored them as
`GAUGE`, either delete the existing `app-unbound-*.rrd` files of the device
(they are recreated at the next poll) or convert them in place with
`rrdtool tune <file> --data-source-type <ds>:DERIVE --minimum <ds>:0` for each
counter data source (`recursiontime` and the request-list `max` stay `GAUGE`).

### Agent or SNMP Extend

=== "SNMP Extend" 
    (Preferred and easiest method)

    1. Copy the shell script, unbound, to the desired host

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/unbound -O /etc/snmp/unbound
    ```

    2. Make the script executable.

    ```bash
    chmod +x /etc/snmp/unbound
    ```

    3. Edit your `snmpd.conf` file and add:

    ```bash
    extend unbound /usr/bin/sudo /etc/snmp/unbound
    ```

    4. Restart snmpd.

    LibreNMS discovers the application automatically, as described at
    the top of the page. If the discovery fails, do the steps under the
    `SNMP Extend` heading at the top of the page.

=== "Agent"

    If this device has no agent, [install the agent](../Agent-Setup.md) and copy the `unbound.sh` script to `/usr/lib/check_mk_agent/local/`
