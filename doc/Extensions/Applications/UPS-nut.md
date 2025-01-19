
# UPS-nut

A small shell script that exports nut ups status.

### SNMP Extend

1. Copy the shell script, unbound, to the desired host

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/ups-nut.sh -O /etc/snmp/ups-nut.sh
    ```

2. Make the script executable

    ```bash
    chmod +x /etc/snmp/ups-nut.sh
    ```

3. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

    ```bash
    extend ups-nut /etc/snmp/ups-nut.sh
    ```

4. Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

Optionally if you have multiple UPS or your UPS is not named APCUPS you can specify its name as an argument into `/etc/snmp/ups-nut.sh`

    ```bash
    extend ups-nut /etc/snmp/ups-nut.sh ups1
    extend ups-nut /etc/snmp/ups-nut.sh ups2
    ```
