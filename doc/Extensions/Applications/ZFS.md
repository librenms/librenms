
# ZFS

## SNMP Extend

1: Install the depends.

=== "Debian/Ubuntu"

    ```bash
    apt-get install -y libjson-perl libmime-base64-perl libfile-slurp-perl
    ```

=== "FreeBSD"

    ```bash
    pkg install p5-JSON p5-MIME-Base64 p5-File-Slurp
    ```

=== "Generic"

    ```bash
    cpanm JSON MIME::Base64 File::Slurp
    ```
    
2: Fetch the script in question and make it executable.

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/zfs -O /etc/snmp/zfs
    chmod +x /etc/snmp/zfs
    ```

3: Add this line to `/etc/snmp/snmpd.conf` and restart snmpd. With the
`-s` argument, the script returns the status for the display.

    ```bash
    extend zfs /etc/snmp/zfs -b
    ```