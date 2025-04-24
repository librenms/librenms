# Freeswitch

A small shell script that reports various Freeswitch call status.

Install via the agent or extend.

=== "Agent"

    1. [Install the agent](../Agent-Setup.md) on this device if it isn't already
    and copy the `freeswitch` script to `/usr/lib/check_mk_agent/local/`

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/agent-local/freeswitch -O /usr/lib/check_mk_agent/local/freeswitch`
    ```

    2. Make the script executable

    ```bash
    chmod +x /usr/lib/check_mk_agent/local/freeswitch
    ```

    3. Configure `FSCLI` in the script. You may also have to create an
    `/etc/fs_cli.conf` file if your `fs_cli` command requires
    authentication.

    4. Verify it is working by running `/usr/lib/check_mk_agent/local/freeswitch`

=== "SNMP Extend"

    1. Download the script onto the desired host

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/agent-local/freeswitch -O /etc/snmp/freeswitch
    ```

    2. Make the script executable

    ```bash
    chmod +x /etc/snmp/freeswitch
    ```

    3. Configure `FSCLI` in the script. You may also have to create an
    `/etc/fs_cli.conf` file if your `fs_cli` command requires
    authentication.

    4. Verify it is working by running `/etc/snmp/freeswitch`

    5. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

    ```bash
    extend freeswitch /etc/snmp/freeswitch
    ```

    6. Restart snmpd on your host

    The application should be auto-discovered as described at the top of
    the page. If it is not, please follow the steps set out under `SNMP
    Extend` heading top of page.
