# Voip-monitor

Shell script that reports cpu-load/memory/open-files files stats of Voip Monitor

## SNMP Extend

1.  Download the script onto the desired host

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/voipmon-stats.sh -O /etc/snmp/voipmon-stats.sh
    ```

2.  Make the script executable

    ```bash
    chmod +x /etc/snmp/voipmon-stats.sh
    ```

3.  Edit your snmpd.conf file (usually `/etc/snmp/voipmon-stats.sh`) and add:

    ```bash
    extend voipmon /etc/snmp/voipmon-stats.sh
    ```