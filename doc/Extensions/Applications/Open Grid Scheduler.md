## Open Grid Scheduler

Shell script to track the OGS/GE jobs running on clusters.

### SNMP Extend

1. Download the script onto the desired host.

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/agent-local/rocks.sh -O /etc/snmp/rocks.sh
    ```

2. Make the script executable

    ```bash
    chmod +x /etc/snmp/rocks.sh
    ```

3. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

    ```bash
    extend ogs /etc/snmp/rocks.sh
    ```

4. Restart snmpd.

    The application should be auto-discovered as described at the top of
    the page. If it is not, please follow the steps set out under `SNMP
    Extend` heading top of page.