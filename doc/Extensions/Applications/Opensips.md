# Opensips

Script that reports load-average/memory/open-files stats of Opensips

### SNMP Extend

1. Download the script onto the desired host

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/opensips-stats.sh -O /etc/snmp/opensips-stats.sh
    ```

2. Make the script executable:

    ```bash
    chmod +x /etc/snmp/opensips-stats.sh
    ```

3. Verify it is working by running `/etc/snmp/opensips-stats.sh`

4. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

    ```bash
    extend opensips /etc/snmp/opensips-stats.sh
    ```