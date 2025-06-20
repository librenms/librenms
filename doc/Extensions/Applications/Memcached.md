## Memcached

This script allows you to monitor memcached stats

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

3. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

    ```bash
    extend memcached /etc/snmp/memcached
    ```

4. Restart snmpd on your host

    The application should be auto-discovered as described at the top of
    the page. If it is not, please follow the steps set out under `SNMP
    Extend` heading top of page.