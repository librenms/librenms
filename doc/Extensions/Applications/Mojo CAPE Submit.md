## Mojo CAPE Submit

### SNMP Extend

This assumes you've already configured `mojo_cape_submit` from `CAPE::Utils`.

1. Add the following to `snmpd.conf` and restarted SNMPD.

    ```bash
    extend mojo_cape_submit /usr/local/bin/mojo_cape_submit_extend
    ```

2. Restart snmpd on your host

    ```bash
    sudo systemctl restart snmpd
    ```

Then just wait for the machine in question to be rediscovered or enabled it in the device settings app page.