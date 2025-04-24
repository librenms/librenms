# Global Positioning System  demon (GPSD)

GPSD is a daemon that can be used to monitor GPS devices.

## Installation

=== "SNMP Extend"
    1. Download the script onto the desired host.

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/gpsd -O /etc/snmp/gpsd
    ```

    2. Make the script executable

    ```bash
    chmod +x /etc/snmp/gpsd
    ```

    3. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

    ```bash
    extend gpsd /etc/snmp/gpsd
    ```

    4. Restart snmpd on your host

    The application should be auto-discovered as described at the top of
    the page. If it is not, please follow the steps set out under `SNMP
    Extend` heading at the top of the page.

=== "Agent"

    [Install the agent](../Agent-Setup.md) on this device if it isn't already
    and copy the `gpsd` script to `/usr/lib/check_mk_agent/local/`

    You may need to configure `$server` or `$port`.

    Verify it is working by running `/usr/lib/check_mk_agent/local/gpsd`