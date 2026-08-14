# Mailscanner

### SNMP Extend

1. Download the script onto the host.
```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/mailscanner.php -O /etc/snmp/mailscanner.php
```

2. Make the script executable.

    ```bash
    chmod +x /etc/snmp/mailscanner.php
    ```

3. Edit your `snmpd.conf` file, usually `/etc/snmp/snmpd.conf`, and add:

    ```bash
    extend mailscanner /etc/snmp/mailscanner.php
    ```

4. Restart snmpd on your host.

    LibreNMS discovers the application automatically, as described at
    the top of the page. If the discovery fails, do the steps under the
    `SNMP Extend` heading at the top of the page.