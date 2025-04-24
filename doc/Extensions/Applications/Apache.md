# Apache

Either use SNMP extend or use the agent.

!!! note Prerequisites
    That you need to install and configure the Apache [mod_status](https://httpd.apache.org/docs/2.4/en/mod/mod_status.html)  module before trying the script.

=== "SNMP Extend"

    1. Download the script onto the desired host (the host must be added to LibreNMS devices)

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/apache-stats.py -O /etc/snmp/apache-stats.py
    ```

    2. Make the script executable

    ```bash
    chmod +x /etc/snmp/apache-stats.py
    ```

    3. Create the cache directory, '/var/cache/librenms/' and make sure
    that it is owned by the user running the SNMP daemon.

    ```bash
    mkdir -p /var/cache/librenms/
    ```

    4. Verify it is working by running /etc/snmp/apache-stats.py Package `urllib3` for python3 needs to be installed. In Debian-based systems for example you can achieve this by issuing:

    ```bash
    apt-get install python3-urllib3
    ```

    5. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

    ```bash
    extend apache /etc/snmp/apache-stats.py
    ```

    6. Restart snmpd on your host

    ```bash
    sudo systemctl snmpd restart
    ```

    7. Test by running

    ```bash
    snmpwalk <various options depending on your setup> localhost NET-SNMP-EXTEND-MIB::nsExtendOutput2Table
    ```

=== "Agent"

    ## Install prerequisites

    === "Debian/Ubuntu"

        ```bash
        apt-get install libwww-perl
        ```

    ### install agent

    [Install the agent](../Agent-Setup.md)) on this device if it isn't already
    and copy the `apache` script to `/usr/lib/check_mk_agent/local/`

    1. Verify it is working by running `/usr/lib/check_mk_agent/local/apache`

    2. Create the cache directory, '/var/cache/librenms/' and make sure
    that it is owned by the user running the SNMP daemon.

        ```bash
        mkdir -p /var/cache/librenms/
        ```

    3. On the device page in Librenms, edit your host and check the
    `Apache` under the Applications tab.
