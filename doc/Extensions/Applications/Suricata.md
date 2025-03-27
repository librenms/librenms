
# Suricata

## SNMP Extend

1. Install the extend.
=== "Debian/Ubuntu"

    ```bash
    apt-get install libjson-perl libfile-path-perl libfile-slurp-perl libmime-base64-perl cpanminus
    cpanm Suricata::Monitoring
    ```

=== "FreeBSD"

    ```bash
    pkg install p5-JSON p5-File-Path p5-File-Slurp p5-Time-Piece p5-MIME-Base64 p5-Hash-Flatten p5-Carp p5-App-cpanminus
    cpanm Suricata::Monitoring
    ```

=== "Generic"

    ```bash
    cpanm Suricata::Monitoring
    ```


2. Setup cron. Below is a example.

    ```
    */5 * * * * /usr/local/bin/suricata_stat_check > /dev/null
    ```

3. Configure snmpd.conf

    ```bash
    extend suricata-stats /usr/bin/env PATH=/sbin:/bin:/usr/sbin:/usr/bin:/usr/local/sbin:/usr/local/bin suricata_stat_check -c
    ```

Or if you want to use try compressing the return via Base64+GZIP...

    ```bash
    extend suricata-stats /usr/bin/env PATH=/sbin:/bin:/usr/sbin:/usr/bin:/usr/local/sbin:/usr/local/bin suricata_stat_check -c -b
    ```

4. Restart snmpd on your system.

You will want to make sure Suricata is set to output the stats
to the eve file once a minute. This will help make sure that
it won't be to far back in the file and will make sure it is
recent when the cronjob runs.

Any configuration of suricata_stat_check should be done in the cron
setup. If the default does not work, check the docs for it at
[MetaCPAN for
suricata_stat_check](https://metacpan.org/dist/Suricata-Monitoring/view/bin/suricata_stat_check)
