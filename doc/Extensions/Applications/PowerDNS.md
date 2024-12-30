## PowerDNS

An authoritative DNS server: <https://www.powerdns.com/auth.html>

=== "SNMP Extend"

    1. Copy the shell script, powerdns.py, to the desired host
    
    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/powerdns.py -O /etc/snmp/powerdns.py
    ```

    2. Make the script executable
    
    ```bash
    chmod +x /etc/snmp/powerdns.py
    ```

    3. Edit your snmpd.conf file and add:

    ```bash
    extend powerdns /etc/snmp/powerdns.py
    ```

    4. Restart snmpd on your host

    The application should be auto-discovered as described at the top of
    the page. If it is not, please follow the steps set out under `SNMP
    Extend` heading top of page.

=== "Agent"

    [Install the agent](../Agent-Setup.md) on this device if it isn't already

    and copy the `powerdns` script to `/usr/lib/check_mk_agent/local/`
