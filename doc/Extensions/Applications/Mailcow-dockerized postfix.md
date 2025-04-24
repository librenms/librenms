# mailcow-dockerized postfix

## SNMP Extend

1. Download the script into the desired host.

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/mailcow-dockerized-postfix -O /etc/snmp/mailcow-dockerized-postfix
    ```

2. Make the script executable

    ```bash
    chmod +x /etc/snmp/mailcow-dockerized-postfix
    ```
    > Maybe you will need to install `pflogsumm` on debian based OS. Please check if you have package installed.

3. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

    ```bash
    extend mailcow-postfix /etc/snmp/mailcow-dockerized-postfix
    ```

4. Restart snmpd on your host

    The application should be auto-discovered as described at the top of
    the page. If it is not, please follow the steps set out under `SNMP
    Extend` heading top of page.