# Chronyd

A shell script that gets the stats from chronyd and exports them with SNMP Extend.

## SNMP Extend

1. Download the shell script onto the desired host

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/chrony -O /etc/snmp/chrony
    ```

2. Make the script executable

    ```bash
    chmod +x /etc/snmp/chrony
    ```

3. Edit the snmpd.conf file to include the extend by adding the following line to the end of the config file:

    ```bash
    extend chronyd /etc/snmp/chrony
    ```

    !!! note
        Some distributions need sudo-permissions for the script to work with SNMP Extend. See the instructions on the section SUDO for more information.

4. Restart snmpd service on the host

    Application should be auto-discovered and its stats presented on the Apps-page on the host. Note: Applications module needs to be enabled on the host or globally for the statistics to work as intended.