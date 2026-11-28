
# ZFS

## SNMP Extend

1: Install the depends.

=== "Debian/Ubuntu"

    ```bash
    apt-get install -y libjson-perl libmime-base64-perl libfile-slurp-perl
    ```

=== "FreeBSD"

    ```bash
    pkg install p5-JSON p5-MIME-Base64 p5-File-Slurp
    ```

=== "Generic"

    ```bash
    cpanm JSON MIME::Base64 File::Slurp
    ```
    
2: Fetch the script in question and make it executable.

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/zfs -O /etc/snmp/zfs
    chmod +x /etc/snmp/zfs
    ```

3: Run the script once as root to create the cache file, so the extend has
something to read before cron first fires.

    ```bash
    /etc/snmp/zfs -b -w -q
    ```

4: Add this line to `/etc/snmp/snmpd.conf` and restart snmpd.

    ```bash
    extend zfs /bin/cat /var/cache/zfs.extend
    ```

5: Add the following to `/etc/cron.d/librenms_zfs` to refresh the cache file.
Each pool is sampled over a live two second `zpool iostat` interval, so a run
takes roughly two seconds per pool, which is long enough for the extend to
time out if snmpd calls the script directly. To include the `zpool status`
output for the display, add the `-s` argument to the cron command.

    ```bash
    */5 * * * * root /etc/snmp/zfs -b -w -q
    ```