# Linux Softnet Stat
 
### Install prereqs

    === "Debian/Ubuntu"

        ```bash
        apt-get install libfile-slurp-perl libjson-perl libmime-base64-perl
        ```

    === "FreeBSD"

        ```bash
        pkg install p5-File-Slurp p5-JSON p5-MIME-Base64    
        ```

    === "Generic"

        ```bash
        cpanm File::Slurp JSON MIME::Base64 
        ```

### SNMP Extend

1. Download the script into the desired host.

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/linux_softnet_stat -O /etc/snmp/linux_softnet_stat
    ```

3. Make the script executable

    ```bash
    chmod +x /etc/snmp/linux_softnet_stat
    ```

4. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

    ```bash
    extend linux_softnet_stat /etc/snmp/linux_softnet_stat -b
    ```

    Then either enable the application Linux Softnet Stat or wait for it to be re-discovered.