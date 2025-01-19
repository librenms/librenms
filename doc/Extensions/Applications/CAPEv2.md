
## CAPEv2

1. Copy the shell script to the desired host.

```bash
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/cape -O /etc/snmp/cape
```

2. Make the script executable

```bash
chmod +x /etc/snmp/cape
```

3. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```bash
extend cape /etc/snmp/cape
```

4. Install the required packages.

=== "Debian/Ubuntu"
    ```bash
    apt-get install libfile-readbackwards-perl libjson-perl libconfig-tiny-perl libdbi-perl libfile-slurp-perl libstatistics-lite-perl
    ```

5. Restart snmpd on your host

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.
