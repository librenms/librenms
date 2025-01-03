# Opensearch\Elasticsearch

### Install prereqs

=== "Debian/Ubuntu"

    ```bash
    apt-get install libjson-perl libfile-slurp-perl liblwp-protocol-https-perl libmime-base64-perl
    ```

=== "FreeBSD"

    ```bash
    pkg install p5-JSON p5-File-Slurp p5-LWP-Protocol-https p5-MIME-Base64
    ```

=== "Generic"

    ```bash
    cpanm JSON Libwww File::Slurp LWP::Protocol::HTTPS MIME::Base64
    ```


### SNMP Extend

1. Download the script onto the desired host.

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/opensearch -O /etc/snmp/opensearch
    ```

2. Make it executable

    ```bash
    chmod +x /etc/snmp/opensearch
    ```

3. Update your `snmpd.conf`.

    Can be directly or via cron. It recommended to use cron if under heavy load it time out waiting for Opensearch.

    === "If not using cron"

        ```
        extend opensearch /etc/snmp/opensearch
        ```

    === "If using cron"

        ```bash
        extend opensearch /bin/cat /var/cache/opensearch.json.snmp
        ```

        Update root crontab with. This is required as it will this will
        likely time out otherwise. Use `*/1` if you want to have the most
        recent stats when polled or to `*/5` if you just want at exactly a 5
        minute interval.

        ```bash
        */5 * * * * /etc/snmp/opensearch -w -q
        ```

5. Restart snmpd on your host.

    ```bash
    sudo systemctl restart snmpd
    ```

6. Enable it or wait for the device to be re-disocvered.

