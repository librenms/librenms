
# UPS-apcups

A small shell script that exports apcacess ups status.

## SNMP Extend

1. Copy the shell script, unbound, to the desired host

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/ups-apcups -O /etc/snmp/ups-apcups
    ```

2. Make the script executable

    ```bash
    chmod +x /etc/snmp/ups-apcups
    ```

3. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

    ```bash
    extend ups-apcups /etc/snmp/ups-apcups
    ```

    If 'apcaccess' is not in the PATH enviromental variable snmpd is using, you may need to do something like below.

    ```bash
    extend ups-apcups/usr/bin/env PATH=/sbin:/bin:/usr/sbin:/usr/bin:/usr/local/sbin:/usr/local/bin /etc/snmp/ups-apcups
    ```

4. Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.