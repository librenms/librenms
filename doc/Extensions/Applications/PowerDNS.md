## PowerDNS

An authoritative DNS server: <https://www.powerdns.com/auth.html>

=== "SNMP Extend"

    1. Copy the shell script, powerdns.py, to the desired host
    
    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/powerdns.py -O /etc/snmp/powerdns.py
    ```

    2. Make the script executable.
    
    ```bash
    chmod +x /etc/snmp/powerdns.py
    ```

    3. Edit your `snmpd.conf` file and add:

    ```bash
    extend powerdns /etc/snmp/powerdns.py
    ```

    4. Restart snmpd on your host.

    LibreNMS discovers the application automatically, as described at
    the top of the page. If the discovery fails, do the steps under the
    `SNMP Extend` heading at the top of the page.

=== "Agent"

    If this device has no agent, [install the agent](../Agent-Setup.md)

    and copy the `powerdns` script to `/usr/lib/check_mk_agent/local/`

=== "Permissions"

   If snmpd runs as an unprivileged user, use sudo.
   Here is a rough outline of one way to accomplish this.

   Add `Debian-snmp ALL=(ALL) NOPASSWD: /usr/bin/pdns_control list` to your sudoers file
   
   In powerdns.py, modify the process from `[pdnscontrol, "list"]` to `["/usr/bin/sudo", pdnscontrol, "list"]`
   
