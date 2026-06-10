# FreeRADIUS

The FreeRADIUS application extension requires that status_server be
enabled in your FreeRADIUS config.  For more information see:
<https://wiki.freeradius.org/config/Status>

You should note that status requests increment the FreeRADIUS request
stats.  So LibreNMS polls will ultimately be reflected in your
stats/charts.

1. Go to your FreeRADIUS configuration directory (usually /etc/raddb
or /etc/freeradius).

2. `cd sites-enabled`

3. `ln -s ../sites-available/status status`

4. Restart FreeRADIUS.

5. You should be able to test with the radclient as follows...

```bash
echo "Message-Authenticator = 0x00, FreeRADIUS-Statistics-Type = 31, Response-Packet-Type = Access-Accept" | \
radclient -x localhost:18121 status adminsecret
```

Note that adminsecret is the default secret key in status_server.
Change if you've modified this.

=== "SNMP Extend"

    1. Copy the freeradius shell script, to the desired host.

        ```bash
        wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/freeradius.sh -O /etc/snmp/freeradius.sh
        ```

    2. Make the script executable

        ```bash
        chmod +x /etc/snmp/freeradius.sh
        ```

    3. If you've made any changes to the FreeRADIUS status_server config
    (secret key, port, etc.) edit freeradius.sh and adjust the config
    variable accordingly.

    4. Edit your snmpd.conf file and add:

        ```bash
        extend freeradius /etc/snmp/freeradius.sh
        ```

    5. Restart snmpd on the host in question.

    The application should be auto-discovered as described at the top of
    the page. If it is not, please follow the steps set out under `SNMP
    Extend` heading top of page.

=== "Agent"

    1. Install the script to your agent

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/freeradius.sh -O /usr/lib/check_mk_agent/local/freeradius.sh`
    ```

    2. Make the script executable

    ```bash
    chmod +x /usr/lib/check_mk_agent/local/freeradius.sh
    ```

    3. If you've made any changes to the FreeRADIUS status_server config
    (secret key, port, etc.) edit freeradius.sh and adjust the config
    variable accordingly.

    4. Edit the freeradius.sh script and set the variable 'AGENT' to '1'
    in the config.