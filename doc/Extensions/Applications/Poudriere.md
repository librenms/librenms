# Poudriere

## Intall prerequisites

=== "FreeBSD"

    ```
    pkg install p5-Data-Dumper p5-JSON p5-MIME-Base64 p5-File-Slurp
    ```

## SNMP Extend

1. Copy the extend into place

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/poudriere -O /usr/local/etc/snmp/poudriere
    ```

2. Make it executable.

    ```bash
    chmod +x /usr/local/etc/snmp/poudriere
    ```

4. Setup the cronjob. The extend needs to be ran as root. See `poudriere --help` for option info.
    ```
    4/5 * * * * root /usr/local/etc/snmp/poudriere -q -a -w -z
    ```

5. Add the extend to snmpd.conf and restart snmpd
    ```
    extend poudriere cat /var/cache/poudriere.json.snmp
    ```