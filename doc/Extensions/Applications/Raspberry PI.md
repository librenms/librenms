# Raspberry PI

SNMP extend script to get your PI data into your host.

## SNMP Extend

1. Download the script onto the desired host

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/raspberry.sh -O /etc/snmp/raspberry.sh
    ```

2. Make the script executable

    ```bash
    chmod +x /etc/snmp/raspberry.sh
    ```

3. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

    ```bash
    extend raspberry /usr/bin/sudo /bin/sh /etc/snmp/raspberry.sh
    ```

4. Edit your sudo users (usually `visudo`) and add at the bottom:

    ```bash
    snmp ALL=(ALL) NOPASSWD: /bin/sh /etc/snmp/raspberry.sh
    ```

!!! note 
    If you are using Raspian, the default user is `Debian-snmp`. Change `snmp` above to `Debian-snmp`. You can verify the user snmpd is using with `ps aux | grep snmpd`

5. Restart snmpd on PI host
