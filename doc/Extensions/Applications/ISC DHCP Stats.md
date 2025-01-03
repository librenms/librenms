# ISC DHCP Stats

A small python3 script that reports current DHCP leases stats and pool usage of ISC DHCP Server.

Also you have to install the dhcpd-pools and the required Perl
modules.

### Install prerequisites

=== "Debian/Ubuntu"

    ```bash
    apt install cpanminus libmime-base64-perl libfile-slurp-perl
    cpanm Net::ISC::DHCPd::Leases
    ```

=== "FreeBSD"

    ```bash
    pkg install p5-JSON p5-MIME-Base64 p5-App-cpanminus p5-File-Slurp
    cpanm Net::ISC::DHCPd::Leases
    ```

=== "Generic"

    ```bash
    cpanm Net::ISC::DHCPd::Leases MIME::Base64 File::Slurp
    ```

### SNMP Extend

1.  Copy the shell script to the desired host.

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/dhcp -O /etc/snmp/dhcp
    ```

2.  Make the script executable

    ```bash
    chmod +x /etc/snmp/dhcp
    ```

3.  Edit your snmpd.conf file
    If on a slow system running it via cron may be needed.

    === "If not using cron"

        edit (usually `/etc/snmp/snmpd.conf`) and add:

        ```bash
        extend dhcpstats /etc/snmp/dhcp -z
        ```

    === "If using cron"

        edit (usually `/etc/snmp/snmpd.conf`) and add:

        ```bash
        extend dhcpstats /etc/snmp/dhcp -Z -w /var/cache/dhcp_extend
        ```

        Setup cronjob to run every 5 minutes. add the following to cron `\etc/crontab.d/librenms_dhcp`:

        ```bash
        */5 * * * * /etc/snmp/dhcp -Z -w /var/cache/dhcp_extend
        ```

    The following options are also supported.

    | Option     | Description                     |
    |------------|---------------------------------|
    | `-c $file` | Path to dhcpd.conf.             |
    | `-l $file` | Path to lease file.             |
    | `-Z`       | Enable GZip+Base64 compression. |
    | `-d`       | Do not de-dup.                  |
    | `-w $file` | File to write it out to.        |

5.  Restart snmpd on your host

    The application should be auto-discovered as described at the top of
    the page. If it is not, please follow the steps set out under `SNMP
    Extend` heading top of page.