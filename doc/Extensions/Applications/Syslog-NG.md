# Syslog-NG

Queries Syslog-NG via `syslog-ng-ctl stats` and returns stats based on that information.

### Install prerequisites

=== "Debian/Ubuntu"

    ```bash
    apt install libjson-xs-perl libmime-base64-perl libfile-slurp-perl libstatistics-lite-perl
    ```

=== "FreeBSD"

    ```bash
    pkg install p5-JSON-XS p5-File-Slurp p5-MIME-Base64 p5-Statistics-Lite
    ```

=== "Generic"

    ```bash
    cpanm JSON::XS File::Slurp MIME::Base64 Statistics::Lite
    ```

### SNMP Extend

1.  Copy the shell script to the desired host.

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/syslog-ng -O /etc/snmp/syslog-ng
    ```

2.  Make the script executable

    ```bash
    chmod +x /etc/snmp/syslog-ng
    ```

3.  Edit your snmpd.conf file
    If on a slow system running it via cron may be needed.

    edit (usually `/etc/snmp/snmpd.conf`) and add:

    ```bash
    extend syslog-ng /bin/cat /var/cache/syslog-ng.extend/snmp
    ```

    Setup cronjob to run every 5 minutes. add the following to cron `\etc/crontab.d/syslog-ng`:

    ```bash
    */5 * * * * /etc/snmp/syslog-ng -w -q
    ```

5.  Restart snmpd on your host

    The application should be auto-discovered as described at the top of
    the page. If it is not, please follow the steps set out under `SNMP
    Extend` heading top of page.
