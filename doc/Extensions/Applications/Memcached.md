## Memcached

This script monitors the memcached statistics.

### SNMP Extend

1. Copy the [memcached
   script](https://github.com/librenms/librenms-agent/blob/master/snmp/memcached)
   to `/etc/snmp/` on your remote server.

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/memcached -O /etc/snmp/memcached
    ```

2. Make the script executable:

    ```bash
    chmod +x /etc/snmp/memcached
    ```

3. Edit your `snmpd.conf` file, usually `/etc/snmp/snmpd.conf`, and add:

    ```bash
    extend memcached /etc/snmp/memcached
    ```

4. Restart snmpd on your host.

    LibreNMS discovers the application automatically, as described at
    the top of the page. If the discovery fails, do the steps under the
    `SNMP Extend` heading at the top of the page.