# Mdadm

It allows you to checks mdadm health and array data

##  Install prereqs

This script require: `jq`

=== "Debian/Ubuntu"

    ```bash
    sudo apt install jq
    ```

### SNMP Extend

1. Download the script onto the desired host.

    ```bash
    sudo wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/mdadm -O /etc/snmp/mdadm
    ```

3. Make the script executable

    ```bash
    sudo chmod +x /etc/snmp/mdadm
    ```

4. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

    ```bash
    extend mdadm /etc/snmp/mdadm
    ```

5. Verify it is working by running

    ```bash
    sudo /etc/snmp/mdadm
    ```

6. Restart snmpd on your host

    ```bash
    sudo service snmpd restart
    ```

    The application should be auto-discovered as described at the
    top of the page. If it is not, please follow the steps set out
    under `SNMP Extend` heading top of page.