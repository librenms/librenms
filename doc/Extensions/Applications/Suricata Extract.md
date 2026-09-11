## Suricata Extract

### SNMP Extend

1. Add these lines to your snmpd config and restart it. The path can
to be adjusted depending on where `suricata_extract_submit_extend` is
installed to.

    ```bash
    extend suricata_extract /usr/local/bin/suricata_extract_submit_extend
    ```

2. Restart snmpd on your system.

    ```bash
    sudo systemctl restart snmpd
    ```

    Then wait for the rediscovery of the system. You can also enable it manually for that server.
