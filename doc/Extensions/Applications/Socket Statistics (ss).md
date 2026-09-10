## Socket Statistics (ss)

The Socket Statistics application polls `ss` and reads the socket
statuses. The optional JSON configuration file of the script can filter
out single sockets and address families.

1. LibreNMS polls these socket types directly. A filter on a socket type
disables the direct polling. It also disables the indirect polling in
each address family with that socket type as a child:
```
dccp (also exists within address-families "inet" and "inet6")
mptcp (also exists within address-families "inet" and "inet6")
raw (also exists within address-families "inet" and "inet6")
sctp (also exists within address-families "inet" and "inet6")
tcp (also exists within address-families "inet" and "inet6")
udp (also exists within address-families "inet" and "inet6")
xdp
```

2. The following socket types are polled within an address-family only:
```
inet6 (within address-family "inet6")
p_dgr (within address-family "link")
p_raw (within address-family "link")
ti_dg (within address-family "tipc")
ti_rd (within address-family "tipc")
ti_sq (within address-family "tipc")
ti_st (within address-family "tipc")
v_dgr (within address-family "vsock")
v_str (within address-family "vsock")
unknown (within address-families "inet", "inet6", "link", "tipc", and "vsock")
```

3. LibreNMS polls these address families directly. Their child socket
types are indented below them. A filter on a socket type, as in item 1
above, removes it from the address family. A filter on an address
family removes all its child socket types. LibreNMS still monitors
those socket types directly or in another address family, unless item 1
above filters them DIRECTLY:
```
inet
    dccp
    mptcp
    raw
    sctp
    tcp
    udp
    unknown
inet6
    dccp
    icmp6
    mptcp
    raw
    sctp
    tcp
    udp
    unknown
link
    p_dgr
    p_raw
    unknown
netlink
tipc
    ti_dg
    ti_rd
    ti_sq
    ti_st
    unknown
unix
    u_dgr
    u_seq
    u_str
vsock
    v_dgr
    v_str
    unknown
```

### SNMP Extend

1. Copy the python script, ss.py, to the desired host

    ```
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/ss.py -O /etc/snmp/ss.py
    ```

2. Make the script executable.

    ```
    chmod +x /etc/snmp/ss.py
    ```

3. Edit your `snmpd.conf` file and add:

    ```
    extend ss /etc/snmp/ss.py
    ```

4. (Optional) Create a /etc/snmp/ss.json file and specify:

    1. "ss_cmd" - String path to the ss binary: ["/sbin/ss"]

    2. "socket_types" - a comma separated list of the socket types to include. These socket types are valid: dccp, icmp6, mptcp, p_dgr, p_raw, raw, sctp, tcp, ti_dg, ti_rd, ti_sq, ti_st, u_dgr, u_seq, u_str, udp, unknown, v_dgr, v_dgr, xdp. Note: the `/sbin/ss` output shows the "unknown" socket type with the netid "???". The socket types p_dgr and p_raw belong to the "link" address family. The socket types ti_dg, ti_rd, ti_sq, and ti_st belong to the "tipc" address family. The socket types u_dgr, u_seq, and u_str belong to the "unix" address family. The socket types v_dgr and v_str belong to the "vsock" address family. A filter on a parent address family also filters out its specific socket types. The value "all" includes all the socket types. For example, "tcp,udp,icmp6" includes only the tcp, udp, and icmp6 sockets: ["all"]

    3. "addr_families" - a comma separated list of the address families to include. These families are valid: inet, inet6, link, netlink, tipc, unix, vsock. As in item b above, a filter on the link, tipc, unix, or vsock address family also filters out its socket types. The value "all" includes all the families. For example, "inet,inet6" includes only the inet and inet6 families: ["all"]

```
{
    "ss_cmd": "/sbin/ss",
    "socket_types": "all"
    "addr_families": "all"
}
```
We recommend this JSON configuration. It filters out the uncommon and
unused socket types:
```
{
    "ss_cmd": "/sbin/ss",
    "socket_types": "icmp6,p_dgr,p_raw,raw,tcp,u_dgr,u_seq,u_str,udp",
    "addr_families": "inet,inet6,link,netlink,unix"
}
```

5. (Optional) If SELinux is in Enforcing mode, you must add a module so the script can poll sockets:
```
cat << EOF > snmpd_ss.te
module snmp_ss 1.0;

require {
    type snmpd_t;
    class netlink_tcpdiag_socket { bind create getattr nlmsg_read read setopt write };
}

#============= snmpd_t ==============

allow snmpd_t self:netlink_tcpdiag_socket { bind create getattr nlmsg_read read setopt write };
EOF
checkmodule -M -m -o snmpd_ss.mod snmpd_ss.te
semodule_package -o snmpd_ss.pp -m snmpd_ss.mod
semodule -i snmpd_ss.pp
```

6. Restart snmpd.
