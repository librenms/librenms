# NTP Client

A shell script that gets stats from ntp client.

## SNMP Extend

1. Download the script onto the desired host.

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/ntp-client -O /etc/snmp/ntp-client
    ```

2. Make the script executable

    ```bash
    chmod +x /etc/snmp/ntp-client
    ```

3. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

    ```bash
    extend ntp-client /etc/snmp/ntp-client
    ```

4. Restart snmpd on your host

    The application should be auto-discovered as described at the top of the page. If it is not, please follow the steps set out under `SNMP Extend` heading top of page.