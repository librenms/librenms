# Mdadm

It monitors the mdadm health and the array data.

##  Install prereqs

This script require: `jq`

=== "Debian/Ubuntu"

    ```bash
    sudo apt install jq
    ```

### SNMP Extend

1. Download the script onto the host.

    ```bash
    sudo wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/mdadm -O /etc/snmp/mdadm
    ```

3. Make the script executable.

    ```bash
    sudo chmod +x /etc/snmp/mdadm
    ```

4. Edit your `snmpd.conf` file, usually `/etc/snmp/snmpd.conf`, and add:

    ```bash
    extend mdadm /etc/snmp/mdadm
    ```

5. Verify it is working by running

    ```bash
    sudo /etc/snmp/mdadm
    ```

6. Restart snmpd on your host.

    ```bash
    sudo service snmpd restart
    ```

    LibreNMS discovers the application automatically, as described at
    the top of the page. If the discovery fails, do the steps under the
    `SNMP Extend` heading at the top of the page.