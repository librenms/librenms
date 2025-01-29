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

### Agent or SNMP Extend

=== "SNMP Extend" 
    (Preferred and easiest method)

    1. Copy the shell script, unbound, to the desired host

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/unbound -O /etc/snmp/unbound
    ```

    2. Make the script executable

    ```bash
    chmod +x /etc/snmp/unbound
    ```

    3. Edit your snmpd.conf file and add:

    ```bash
    extend unbound /usr/bin/sudo /etc/snmp/unbound
    ```

    4. Restart snmpd.

    The application should be auto-discovered as described at the top of
    the page. If it is not, please follow the steps set out under `SNMP
    Extend` heading top of page.

=== "Agent"

    [Install the agent](../Agent-Setup.md) on this device if it isn't already and copy the `unbound.sh` script to `/usr/lib/check_mk_agent/local/`
