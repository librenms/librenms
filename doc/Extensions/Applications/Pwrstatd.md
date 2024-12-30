## Pwrstatd

Pwrstatd (commonly known as powerpanel) is an application/service available from CyberPower to monitor their PSUs over USB.  It is currently capable of reading the status of only one PSU connected via USB at a time.  The powerpanel software is available here:
https://www.cyberpowersystems.com/products/software/power-panel-personal/

### SNMP Extend

1. Copy the python script, pwrstatd.py, to the desired host

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/pwrstatd.py -O /etc/snmp/pwrstatd.py
    ```

2. Make the script executable

    ```bash
    chmod +x /etc/snmp/pwrstatd.py
    ```

3. Edit your snmpd.conf file and add:

    ```bash
    extend pwrstatd /etc/snmp/pwrstatd.py
    ```

4. (Optional) Create a `/etc/snmp/pwrstatd.json` file and specify the path to the pwrstat executable [the default path is `/sbin/pwrstat`]:
    
    ```bash
    {
        "pwrstat_cmd": "/sbin/pwrstat"
    }
    ```

5. Restart snmpd.

    ```bash
    sudo systemctl restart snmpd
    ```