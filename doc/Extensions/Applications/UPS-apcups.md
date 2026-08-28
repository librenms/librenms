
# UPS-apcups

A small shell script that exports apcacess ups status.

## SNMP Extend

1. Copy the shell script, unbound, to the desired host

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/ups-apcups -O /etc/snmp/ups-apcups
    ```

2. Make the script executable.

    ```bash
    chmod +x /etc/snmp/ups-apcups
    ```

3. Edit your `snmpd.conf` file, usually `/etc/snmp/snmpd.conf`, and add:

    ```bash
    extend ups-apcups /etc/snmp/ups-apcups
    ```

    If `apcaccess` is not in the PATH variable of snmpd, use a line like
    the one below.

    ```bash
    extend ups-apcups/usr/bin/env PATH=/sbin:/bin:/usr/sbin:/usr/bin:/usr/local/sbin:/usr/local/bin /etc/snmp/ups-apcups
    ```

4. Restart snmpd on your host.

LibreNMS discovers the application automatically, as described at the
top of the page. If the discovery fails, do the steps under the `SNMP
Extend` heading at the top of the page.