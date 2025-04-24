## Portactivity

### SNMP Extend

1. Install missing packages - Ubuntu is shown below.

    ```bash
    apt install libparse-netstat-perl
    apt install libjson-perl
    ```

2. Copy the Perl script to the desired host (the host must be added to LibreNMS devices)

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/portactivity -O /etc/snmp/portactivity
    ```

3. Make the script executable

    ```bash
    chmod +x /etc/snmp/portactivity
    ```

4. Edit your `snmpd.conf` file (usually `/etc/snmp/snmpd.conf`) and add:

    ```bash
    extend portactivity /etc/snmp/portactivity -p http,ldap,imap
    ```

!!! note "portactivity"
    Will monitor HTTP, LDAP, and IMAP. The `-p` switch specifies what ports to use. This is a comma seperated list.
    
    These must be found in '/etc/services' or where ever NSS is set to fetch it from. If not, it will throw an error.
    
    If you want to JSON returned by it to be printed in a pretty format use the `-P` flag.

5. Restart snmpd on your host.

    ```bash
    sudo systemctl restart snmpd
    ```

Please note that for only TCP[46] services are supported.