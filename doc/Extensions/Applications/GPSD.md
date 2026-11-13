# Global Positioning System daemon (GPSD)

GPSD is a daemon that can be used to monitor GPS devices.

!!! note Prerequisites
    That you need to install and run the GPSD daemon before executing this script.

## Installation

=== "SNMP Extend (bash)"
    1. Download the script onto the host.

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/gpsd -O /etc/snmp/gpsd
    ```

    2. Make the script executable.

    ```bash
    chmod +x /etc/snmp/gpsd
    ```

    3. Edit your `snmpd.conf` file, usually `/etc/snmp/snmpd.conf`, and add:

    ```bash
    extend gpsd /etc/snmp/gpsd
    ```

    4. Restart snmpd on your host.

    LibreNMS discovers the application automatically, as described at
    the top of the page. If the discovery fails, do the steps under the
    `SNMP Extend` heading at the top of the page.

    If a timeout occurs, configure it as below
    below. If `time gpspipe -w -n 20` is regularly longer than what your SNMP time out is
    for, this is required.

    For cron...

    ```
    */5 * * * * /etc/snmp/gpsd 2> /dev/null > /var/cache/gpsd.snmp
    ```

    For snmpd.conf...

    ```
    extend gpsd /usr/bin/cat /var/cache/gpsd.snmp
    ```

=== "SNMP Extend (python)"
    1. Download the script onto the host.
    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/gpsd.py -O /etc/snmp/gpsd.py
    ```
    2. Make the script executable.
    ```bash
    chmod +x /etc/snmp/gpsd.py
    ```
    3. Edit your `snmpd.conf` file, usually `/etc/snmp/snmpd.conf`, and add:
    ```bash
    extend gpsd /etc/snmp/gpsd.py
    ```
    4. (Optional, defaults are specified in brackets) Create an /etc/snmp/gpsd.json file and specify:
        a. "cache_file"    - String path to the cache file: ["/var/cache/librenms/gpsd.json"]
        b. "gpspipe_cmd"   - String path to the gpspipe binary: ["/usr/bin/gpspipe"]
        c. "packet_count"  - Integer specifying the number of packets to receive from gpsd
                             before terminating: [20]
        d. Example file format
            ```
            {
                "cache_file": "/var/cache/librenms/gpsd.json",
                "gpspipe_cmd": "/usr/bin/gpspipe",
                "packet_count": 20
            }
            ```
    5. (Optional, but recommended) Create a cron.d file (Ex: `/etc/cron.d/gpsd`)  to build a cachefile every 5 minutes to speed up snmp query exection time:
    ```bash
    */5 * * * * root /etc/snmp/gpsd.py -m
    ```
    6. If SELinux is in Enforcing mode, you must import a module so the script can poll sockets and read the cache file:
    ```
    cat << EOF > snmpd_gpsd.te
    module snmp_gpsd 1.0;

    require {
            type snmpd_t;
            type var_t;
            type gpsd_port_t;
            class tcp_socket name_connect;
            class file { getattr ioctl open read };
    }

    #============= snmpd_t ==============

    allow snmpd_t gpsd_port_t:tcp_socket name_connect;
    allow snmpd_t var_t:file { getattr ioctl open read };

    EOF
    checkmodule -M -m -o snmpd_gpsd.mod snmpd_gpsd.te
    semodule_package -o snmpd_gpsd.pp -m snmpd_gpsd.mod
    semodule -i snmpd_gpsd.pp
    ```
    7. Restart snmpd on your host.

=== "Agent"

    If this device has no agent, [install the agent](../Agent-Setup.md)
    and copy the `gpsd` script to `/usr/lib/check_mk_agent/local/`

    You can need a change to `$server` or to `$port`.

    Verify it is working by running `/usr/lib/check_mk_agent/local/gpsd`
