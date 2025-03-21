# NTP Server aka NTPD

A shell script that gets stats from ntp server (ntpd).

### SNMP Extend

1. Download the script onto the desired host.

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/ntp-server.sh -O /etc/snmp/ntp-server.sh
    ```

2. Make the script executable

    ```bash
    chmod +x /etc/snmp/ntp-server.sh
    ```

3. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

    ```bash
    extend ntp-server /etc/snmp/ntp-server.sh
    ```

4. Restart snmpd on your host

    ```bash
    sudo systemctl restart snmpd
    ```

    The application should be auto-discovered as described at the top of the page. If it is not, please follow the steps set out under `SNMP Extend` heading top of page.