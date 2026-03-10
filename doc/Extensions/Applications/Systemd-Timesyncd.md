# Systemd-Timesyncd

A shell script that gets stats from `systemd-timesyncd` using `timedatectl`.

## SNMP Extend

1. Download the script onto the desired host.

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/systemd-timesyncd -O /etc/snmp/systemd-timesyncd
    ```

2. Make the script executable

    ```bash
    chmod +x /etc/snmp/systemd-timesyncd
    ```

3. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

    ```bash
    extend systemd-timesyncd /etc/snmp/systemd-timesyncd
    ```

4. Restart snmpd on your host

    ```bash
    systemctl restart snmpd.service
    ```

    The application should be auto-discovered as described at the top of the page. If it is not, please follow the steps set out under `SNMP Extend` heading top of page.
