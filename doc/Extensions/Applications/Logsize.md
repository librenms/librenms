
# Logsize

logsize is a small shell script that reports the size of log files.

## SNMP Extend

1. Download the 
## Logsize

### SNMP Extend

1. Download the script and make it executable.

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/logsize -O /etc/snmp/logsize
    chmod +x /etc/snmp/logsize
    ```

2. Install the requirements.

    === "Debian/Ubuntu"

        ```bash
        apt-get install libjson-perl libmime-base64-perl libfile-slurp-perl libtoml-perl libfile-find-rule-perl libstatistics-lite-perl
        cpanm Time::Piece
        ```

    === "FreeBSD"

        ```bash
        pkg install p5-File-Find-Rule p5-JSON p5-TOML p5-Time-Piece p5-MIME-Base64 p5-File-Slurp p5-Statistics-Lite
        ```

    === "Generic"

        ```bash
        cpanm File::Find::Rule JSON TOML Time::Piece MIME::Base64 File::Slurp Statistics::Lite Time::Piece
        ```

3. Configure the config at `/usr/local/etc/logsize.conf`. You can find the documentation for the config file in the extend. Below is a small example.

    ```conf
    # monitor log sizes of logs directly udner /var/log
    [sets.var_log]
    dir="/var/log/"

    # monitor remote logs from network devices
    [sets.remote_network]
    dir="/var/log/remote/network/"

    # monitor remote logs from windows sources
    [sets.remote_windows]
    dir="/var/log/remote/windows/"

    # monitor suricata flows logs sizes
    [sets.suricata_flows]
    dir="/var/log/suricata/flows/current"
    ```

4. If the directories all readable via SNMPD, this script can be ran
   via snmpd. Otherwise it needs setup in cron. Similarly is
   processing a large number of files, it may also need setup in cron
   if it takes the script awhile to run.

    ```cron
    */5 * * * * /etc/snmp/logsize -b 2> /dev/null > /dev/null
    ```

5. Make sure that `/var/cache/logsize_extend` exists and is writable
   by the user running the extend.

    ```bash
    mkdir -p /var/cache/logsize_extend
    ```

6. Configure it in the SNMPD config (usually `/etc/snmp/snmpd.conf`).

    === "If not using cron"

        Add:

        ```bash
        extend logsize  /etc/snmp/logsize -b
        ```

    === "If using cron"

        Add:

        ```bash
        extend logsize /bin/cat /var/cache/logsize_extend/extend_return
        ```
script and make it executable.

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/logsize -O /etc/snmp/logsize
    chmod +x /etc/snmp/logsize
    ```

2. Install the requirements.

    === "Debian/Ubuntu"

        ```bash
        apt-get install libjson-perl libmime-base64-perl libfile-slurp-perl libtoml-perl libfile-find-rule-perl libstatistics-lite-perl
        cpanm Time::Piece
        ```

    === "FreeBSD"

        ```bash
        pkg install p5-File-Find-Rule p5-JSON p5-TOML p5-Time-Piece p5-MIME-Base64 p5-File-Slurp p5-Statistics-Lite
        ```

    === "Generic"

        ```bash
        cpanm File::Find::Rule JSON TOML Time::Piece MIME::Base64 File::Slurp Statistics::Lite Time::Piece
        ```

3. Configure the config at `/usr/local/etc/logsize.conf`. You can find the documentation for the config file in the extend. Below is a small example.

    ```conf
    # monitor log sizes of logs directly udner /var/log
    [sets.var_log]
    dir="/var/log/"

    # monitor remote logs from network devices
    [sets.remote_network]
    dir="/var/log/remote/network/"

    # monitor remote logs from windows sources
    [sets.remote_windows]
    dir="/var/log/remote/windows/"

    # monitor suricata flows logs sizes
    [sets.suricata_flows]
    dir="/var/log/suricata/flows/current"
    ```

4. If the directories all readable via SNMPD, this script can be ran
   via snmpd. Otherwise it needs setup in cron. Similarly is
   processing a large number of files, it may also need setup in cron
   if it takes the script awhile to run.

    ```cron
    */5 * * * * /etc/snmp/logsize -b 2> /dev/null > /dev/null
    ```

5. Make sure that `/var/cache/logsize_extend` exists and is writable
   by the user running the extend.

    ```bash
    mkdir -p /var/cache/logsize_extend
    ```

6. Configure it in the SNMPD config (usually `/etc/snmp/snmpd.conf`).

    === "If not using cron"

        Add:

        ```bash
        extend logsize  /etc/snmp/logsize -b
        ```

    === "If using cron"

        Add:

        ```bash
        extend logsize /bin/cat /var/cache/logsize_extend/extend_return
        ```
