## Mojo CAPE Submit

### SNMP Extend

This section assumes a configured `mojo_cape_submit` from `CAPE::Utils`.

1. Add the following to `snmpd.conf` and restarted SNMPD.

    ```bash
    extend mojo_cape_submit /usr/local/bin/mojo_cape_submit_extend
    ```

2. Restart snmpd on your host.

    ```bash
    sudo systemctl restart snmpd
    ```

Then wait for the rediscovery of the machine. You can also enable it on the app page of the device settings.