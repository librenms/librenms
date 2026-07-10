# Routinator

A Python script that monitors [Routinator](https://github.com/NLnetLabs/routinator),
the NLnet Labs RPKI Relying Party software (RPKI validator), and exports its
status with SNMP Extend.

The script reads Routinator's JSON status API (`/api/v1/status`) and aggregates
the per-repository and per-client series into compact counts so it does not
flood LibreNMS with constantly-changing graphs.

## SNMP Extend

1. Download the Python script onto the Routinator host

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/routinator.py -O /etc/snmp/routinator.py
    ```

2. Make the script executable

    ```bash
    chmod +x /etc/snmp/routinator.py
    ```

3. (Optional) create the config file `/etc/snmp/routinator.json` if Routinator's
   HTTP server is not on the default `http://127.0.0.1:8323`. The default port
   is commonly changed, so confirm the real `http-listen` value.

    ```json
    {
        "url": "http://127.0.0.1:8323/api/v1/status",
        "timeout": 5,
        "include_failed_uris": true,
        "max_failed_uris": 25
    }
    ```

    `url` may instead be supplied as separate `host` and `port` keys. If the
    file is absent the built-in defaults are used.

4. Edit the snmpd.conf file to include the extend by adding the following line
   to the end of the config file:

    ```bash
    extend routinator /etc/snmp/routinator.py
    ```

    !!! note
        Some distributions need sudo-permissions for the script to work with
        SNMP Extend. See the instructions on the section SUDO for more
        information. A localhost HTTP fetch usually does not need it.

5. Restart snmpd service on the host

    Application should be auto-discovered and its stats presented on the
    Apps-page on the host. Note: Applications module needs to be enabled on the
    host or globally for the statistics to work as intended.

## Notes

- The per-RTR-client graphs are only populated when Routinator is started with
  `--rtr-client-metrics`. Without it the client section is empty and the rest of
  the graphs still work.
