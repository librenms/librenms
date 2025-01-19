# HV Monitor

HV Monitor provides a generic way to monitor hypervisors. Currently
CBSD+bhyve on FreeBSD and Libvirt+QEMU on Linux are support.

For more information see
HV::Monitor on
[Github](https://github.com/VVelox/HV-Monitor)
or [MetaCPAN](https://metacpan.org/dist/HV-Monitor).

### SNMP Extend

1.  Install the SNMP Extend.

    === "Debian/Ubuntu"
        ```bash
        apt-get install libjson-perl libmime-base64-perl cpanminus
        cpanm HV::Monitor
        ```

    === "FreeBSD"
        ```bash
        pkg install p5-App-cpanminus p5-JSON p5-MIME-Base64 p5-Module-List
        cpanm HV::Monitor
        ```

    === "Generic"
        ```bash
        cpanm JSON MIME::Base64 Module::List
        ```


2. Set it up to be be ran by cron by root. Yes, you can directly call
   this script from SNMPD, but be aware, especially with Libvirt,
   there is a very real possibility of the snmpget timing out,
   especially if a VM is spinning up/down as virsh domstats can block
   for a few seconds or so then.

    ```bash
    */5 * * * * /usr/local/bin/hv_monitor > /var/cache/hv_monitor.json -c 2> /dev/null
    ```

3.  Setup snmpd.conf as below.

    ```bash
    extend hv-monitor /bin/cat
    /var/cache/hv_monitor.json

    ```

4. Restart SNMPD.

5. Either wait for it to be re-discovered or manually enable it.

