# Supervisord

It shows you the totals per status and also the uptime per process. That way you can add alerts for instance when there are process in state `FATAL`.

## SNMP Extend

1. Copy the python script to the desired host.

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/supervisord.py -O /etc/snmp/supervisord.py
    ```

    Notice that this will use the default unix socket path. Modify the `unix_socket_path` variable in the script if your path differs from the default.

2. Make the script executable

    ```
    chmod +x /etc/snmp/supervisord.py
    ```

3. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

    ```
    extend supervisord /etc/snmp/supervisord.py
    ```

4. Restart snmpd on your host

    ```bash
    systemctl restart snmpd
    ```
