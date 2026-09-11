# mailcow-dockerized postfix

## SNMP Extend

1. Download the script into the desired host.

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/mailcow-dockerized-postfix -O /etc/snmp/mailcow-dockerized-postfix
    ```

2. Make the script executable.

    ```bash
    chmod +x /etc/snmp/mailcow-dockerized-postfix
    ```
    > A Debian based OS can need the `pflogsumm` package. Make sure that
    > this package is installed.

3. Edit your `snmpd.conf` file, usually `/etc/snmp/snmpd.conf`, and add:

    ```bash
    extend mailcow-postfix /etc/snmp/mailcow-dockerized-postfix
    ```

4. Restart snmpd on your host.

    LibreNMS discovers the application automatically, as described at
    the top of the page. If the discovery fails, do the steps under the
    `SNMP Extend` heading at the top of the page.