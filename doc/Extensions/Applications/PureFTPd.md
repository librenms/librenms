# PureFTPd

SNMP extend script to monitor PureFTPd.

## SNMP Extend

1. Download the script onto the desired host

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/pureftpd.py -O /etc/snmp/pureftpd.py
    ```

2. Make the script executable

    ```bash
    chmod +x /etc/snmp/pureftpd.py
    ```

3. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

    ```bash
    extend pureftpd sudo /etc/snmp/pureftpd.py
    ```

4. Edit your sudo users (usually `visudo`) and add at the bottom:

    ```bash
    snmp ALL=(ALL) NOPASSWD: /etc/snmp/pureftpd.py
    ```
    
    or the path where your pure-ftpwho is located


5. If pure-ftpwho is not located in /usr/sbin

    you will also need to create a config file, which is named `/etc/snmp/.pureftpd.json`: 

    ```json
    {"pureftpwho_cmd": "/usr/sbin/pure-ftpwho"
    }
    ```

5. Restart snmpd on your host
