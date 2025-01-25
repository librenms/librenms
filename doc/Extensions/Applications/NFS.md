# NFS

Provides both NFS client and server support.

Currently supported OSes are as below.

- FreeBSD
- Linux

### Install prereqs

=== "Debian/Ubuntu"

    ```bash
    apt-get install libjson-perl libfile-slurp-perl libmime-base64-perl
    ```

=== "CentOS/RedHat"

    ```bash
    yum install perl-JSON perl-File-Slurp perl-MIME-Base64
    ```

=== "FreeBSD"

    ```bash
    pkg install p5-JSON p5-File-Slurp p5-MIME-Base64
    ```

=== "Generic"

    ```bash
    cpanm JSON File::Slurp MIME::Base64
    ```

### SNMPd extend

1. Download the extend.

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/nfs -O /etc/snmp/nfs
    ```

2. Make it executable.

    ```bash
    chmod +x /etc/snmp/nfs
    ```

3. Add it to `/etc/snmp/snmpd.conf`:

    ```bash
    extend nfs /usr/bin/env PATH=/sbin:/bin:/usr/sbin:/usr/bin:/usr/local/sbin:/usr/local/bin /etc/snmp/nfs
    ```

5. Restart `snmpd` on your host

6. Either wait for it to be rediscovered, rediscover it, or enable it.

### SELinux
If using SELinux, the following is needed.

1. `setsebool -P nis_enabled 1`

2. Make a file (`snmp_nfs.te`) with the following contents and install
   the policy with the command `semodule -i snmp_nfs.te`.

```bash
module local_snmp 1.0;

require {
    type snmpd_t;
    type portmap_port_t;
    type sysctl_rpc_t;
    type device_t;
    type mountd_port_t;
    type hi_reserved_port_t;
    class tcp_socket { name_bind name_connect };
    class udp_socket name_bind;
    class dir search;
    class file { read getattr open };
    class chr_file { open ioctl read write };
}

# Allow snmpd_t to connect to tcp_socket of type portmap_port_t
allow snmpd_t portmap_port_t:tcp_socket name_connect;
allow snmpd_t hi_reserved_port_t:tcp_socket name_bind;
allow snmpd_t hi_reserved_port_t:udp_socket name_bind;
allow snmpd_t mountd_port_t:tcp_socket name_connect;

# Allow snmpd_t to search directories and access files of type sysctl_rpc_t
allow snmpd_t sysctl_rpc_t:dir search;
allow snmpd_t sysctl_rpc_t:file { read getattr open };

# Allow snmpd_t to perform open, ioctl, read, and write operations on chr_file of type device_t
allow snmpd_t device_t:chr_file { open ioctl read write };

# this policy allows :
# zfs extension (fixes root needs to run this)
# nfs extension (fixes file not found error)
```