# NTP Client

A shell script that gets stats from ntp client.

## SNMP Extend

1. Download the script onto the host.

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/ntp-client -O /etc/snmp/ntp-client
    ```

2. Make the script executable.

    ```bash
    chmod +x /etc/snmp/ntp-client
    ```

3. Edit your `snmpd.conf` file, usually `/etc/snmp/snmpd.conf`, and add:

    ```bash
    extend ntp-client /etc/snmp/ntp-client
    ```

4. Restart snmpd on your host.

    LibreNMS discovers the application automatically, as described at the
top of the page. If the discovery fails, do the steps under the `SNMP
Extend` heading at the top of the page.