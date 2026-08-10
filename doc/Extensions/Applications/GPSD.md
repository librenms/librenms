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

    If you run into timeout issues with this, you may need to run it configure it like
    below. If `time gpspipe -w -n 20` is regularly longer than what your SNMP time out is
    for, this is required.

    For cron...

    ```
    */5 * * * * /etc/snmp/gpsd 2> /dev/null > /var/cache/gpsd.snmp
    ```

    For snmpd.conf...

    ```
    extend gpsd /usr/bin/cat /var/cache/gpsd.snmp
    ```

=== "Agent"

    [Install the agent](../Agent-Setup.md) on this device if it isn't already
    and copy the `gpsd` script to `/usr/lib/check_mk_agent/local/`

    You may need to configure `$server` or `$port`.

    Verify it is working by running `/usr/lib/check_mk_agent/local/gpsd`
