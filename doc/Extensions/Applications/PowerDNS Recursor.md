# PowerDNS Recursor

A recursive DNS server: <https://www.powerdns.com/recursor.html>

## Direct, Agent or SNMP Extend
=== "Direct"

    The LibreNMS polling host must be able to connect to port 8082 on the
    monitored device. The web-server must be enabled, see the Recursor
    docs: <https://doc.powerdns.com/md/recursor/settings/#webserver>

    ## Variables

    `$config['apps']['powerdns-recursor']['api-key']` required, this is
    defined in the Recursor config

    `$config['apps']['powerdns-recursor']['port']` numeric, defines the
    port to connect to PowerDNS Recursor on.  The default is 8082

    `$config['apps']['powerdns-recursor']['https']` true or false,
    defaults to use http.

=== "Agent"

    [Install the agent](../Agent-Setup.md) on this device if it isn't already
    and copy the `powerdns-recursor` script to
    `/usr/lib/check_mk_agent/local/`

    This script uses `rec_control get-all` to collect stats.

=== "SNMP Extend"

    1. Copy the shell script, powerdns-recursor, to the desired host
    
    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/powerdns-recursor -O /etc/snmp/powerdns-recursor
    ```

    2. Make the script executable
    
    ```bash
    chmod +x /etc/snmp/powerdns-recursor
    ```

    3. Edit your snmpd.conf file and add:

    ```bash
    extend powerdns-recursor /etc/snmp/powerdns-recursor
    ```

    4. Restart snmpd on your host

    The application should be auto-discovered as described at the top of
    the page. If it is not, please follow the steps set out under `SNMP
    Extend` heading top of page.

