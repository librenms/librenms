## Suricata Extract

### SNMP Extend

1. Add the following to your snmpd config and restart. Path may have
to be adjusted depending on where `suricata_extract_submit_extend` is
installed to.

    ```bash
    extend suricata_extract /usr/local/bin/suricata_extract_submit_extend
    ```

2. Restart snmpd on your system.

    ```bash
    sudo systemctl restart snmpd
    ```

    Then just wait for the system to be rediscovered or enable it manually for the server in question.
